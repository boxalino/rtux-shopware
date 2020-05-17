<?php
namespace Boxalino\RealTimeUserExperience\Framework\Content\Subscriber;

use Boxalino\RealTimeUserExperience\Framework\Content\Page\ApiCrossSellingLoader;
use Shopware\Storefront\Page\Product\ProductPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ApiCrossSellingLoaderSubscriber
 *
 * In order to update the CrossSelling content, the event ProductPageLoadedEvent had to be used
 * because that event has access to both Request and SalesChannelContext
 * elements required for the API Request build
 *
 * @package Boxalino\RealTimeUserExperience\Framework\Content\Subscriber
 */
class ApiCrossSellingLoaderSubscriber implements EventSubscriberInterface
{
    /**
     * @var ApiCrossSellingLoader
     */
    private $apiCrossSellingLoader;


    public function __construct(
        ApiCrossSellingLoader $apiCrossSellingLoader
    ){
        $this->apiCrossSellingLoader = $apiCrossSellingLoader;
    }

    /**
     * The event type hooked to is ProductPageLoadedEvent because in order to make API calls
     * it is also required the request
     * which is not available in the case of the CrossSellingLoadedEvent
     *
     * @return array|string[]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ProductPageLoadedEvent::class => 'addApiCrossSellings'
        ];
    }

    /**
     * Adds API recommendation to the crosselling loader event
     * If the fallback of the request is triggered - the existing cross-sellings are being set
     *
     * @param ProductPageLoadedEvent $event
     */
    public function addApiCrossSellings(ProductPageLoadedEvent $event) : void
    {
        $page = $event->getPage();
        $request = $event->getRequest();
        $request->attributes->set("mainProductId", $page->getProduct()->getId());

        $result = $this->apiCrossSellingLoader->load($request, $event->getSalesChannelContext(), $page->getCrossSellings());
        $page->setCrossSellings($result);
    }

}
