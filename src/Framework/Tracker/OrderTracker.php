<?php
namespace Boxalino\RealTimeUserExperience\Framework\Tracker;

use Boxalino\RealTimeUserExperience\Service\Tracker\RtuxApiHandler;
use Boxalino\RealTimeUserExperienceApi\Service\Api\ApiCookieSubscriber;
use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Cart\Event\CheckoutOrderPlacedEvent;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextServiceInterface;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextServiceParameters;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;


/**
 * Class OrderTracker
 * Tracking the order events server-side
 *
 * @package Boxalino\RealTimeUserExperience\Framework\Tracker
 */
class OrderTracker implements EventSubscriberInterface
{

    public const RTUX_API_TRACKER_PURCHASE_EVENT = "purchase";

    /**
     * @var RtuxApiHandler
     */
    protected $rtuxApiHandler;

    /**
     * @var SalesChannelContextServiceInterface
     */
    protected $salesChannelContextService;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(
        SalesChannelContextServiceInterface $salesChannelContextService,
        RtuxApiHandler $rtuxApiHandler,
        RequestStack $requestStack,
        LoggerInterface $logger
    ){
        $this->salesChannelContextService = $salesChannelContextService;
        $this->rtuxApiHandler = $rtuxApiHandler;
        $this->requestStack = $requestStack;
        $this->logger = $logger;
    }

    /**
     * @return array|string[]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            CheckoutOrderPlacedEvent::class => 'addApiTracker'
        ];
    }

    /**
     * Tracks the order event with order details
     *
     * @param CheckoutOrderPlacedEvent $event
     */
    public function addApiTracker(CheckoutOrderPlacedEvent $event)
    {
        try{
            $this->rtuxApiHandler->track(
                self::RTUX_API_TRACKER_PURCHASE_EVENT,
                $this->getEventParameters($event->getOrder()),
                $this->getSalesChannelContext($event->getSalesChannelId(), $event->getOrder()->getLanguageId())
            );
        } catch (\Throwable $exception)
        {
            $this->logger->error("Boxalino API PURCHASE TRACKER error: " . $exception->getMessage());
        }
    }

    /**
     * @param OrderEntity $order
     * @return array
     */
    protected function getEventParameters(OrderEntity $order) : array
    {
        $productsCount = 0;
        $eventParameters = [
            't'  => round($order->getAmountTotal(), 2),
            'c'  => $order->getCurrency()->getIsoCode(),
            'orderId' => $order->getId()
        ];
        /**
         * @var OrderLineItemEntity $item
         */
        foreach($order->getLineItems() as $item)
        {
            /** only set the products instead  */
            if($item->getProductId())
            {
                $eventParameters['id' . $productsCount] = $item->getProductId();
                $eventParameters['q' . $productsCount] = $item->getQuantity();
                $eventParameters['p' . $productsCount] = $item->getTotalPrice();
                $productsCount++;
            }
        }
        /** the count is added later because the vouchers are set as order line items as well */
        $eventParameters['n'] = $productsCount;

        list($cemv, $cems) = $this->getCemvCemsCookies();
        if(is_null($cemv) || is_null($cems))
        {
            $this->logger->notice("Boxalino API TRACKER PURCHASE: CEMS:$cems or CEMV:$cemv cookies are not available for tracking on order ID {$order->getId()}");
            return $eventParameters;
        }
        $eventParameters['_bxs'] = $cems;
        $eventParameters['_bxv'] = $cemv;

        return $eventParameters;
    }

    /**
     * @return array|null[]
     */
    protected function getCemvCemsCookies() : array
    {
        $request = $this->requestStack->getMainRequest();
        if ($request === null) {
            return [null, null];
        }

        return [
            $request->cookies->get(ApiCookieSubscriber::BOXALINO_API_COOKIE_VISITOR, null),
            $request->cookies->get(ApiCookieSubscriber::BOXALINO_API_COOKIE_SESSION, null)
        ];
    }

    /**
     * @param string $channelId
     * @param string $languageId
     * @return SalesChannelContext
     */
    protected function getSalesChannelContext(string $channelId, string $languageId) : SalesChannelContext
    {
        return $this->salesChannelContextService->get(
            new SalesChannelContextServiceParameters(
                $channelId,
                "boxalino-rtux-api-tracker",
                $languageId
            )
        );
    }


}
