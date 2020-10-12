<?php
namespace Boxalino\RealTimeUserExperience\Framework\Tracker;

use Boxalino\RealTimeUserExperience\Service\Tracker\RtuxApiHandler;
use Shopware\Core\Checkout\Cart\Event\CheckoutOrderPlacedEvent;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextServiceInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


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

    public function __construct(
        SalesChannelContextServiceInterface $salesChannelContextService,
        RtuxApiHandler $rtuxApiHandler
    ){
        $this->salesChannelContextService = $salesChannelContextService;
        $this->rtuxApiHandler = $rtuxApiHandler;
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
            //do nothing
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

        return $eventParameters;
    }

    /**
     * @param string $channelId
     * @param string $languageId
     * @return SalesChannelContext
     */
    protected function getSalesChannelContext(string $channelId, string $languageId) : SalesChannelContext
    {
        return $this->salesChannelContextService->get(
            $channelId,
            "boxalino-rtux-api-tracker",
            $languageId
        );
    }


}
