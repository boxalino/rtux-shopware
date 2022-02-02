<?php
namespace Boxalino\RealTimeUserExperience\Framework\Content\Subscriber;

use Boxalino\RealTimeUserExperience\Framework\Content\Page\ApiCrossSellingLoader;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\RequestInterface;
use Shopware\Core\Content\Product\SalesChannel\CrossSelling\CrossSellingElementCollection;
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

    /**
     * @var RequestInterface
     */
    private $requestWrapper;

    public function __construct(
        ApiCrossSellingLoader $apiCrossSellingLoader,
        RequestInterface $requestWrapper
    ){
        $this->requestWrapper = $requestWrapper;
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
        $this->requestWrapper->setRequest($request);

        try{
            $this->apiCrossSellingLoader->setRequest($this->requestWrapper)
                ->setSalesChannelContext($event->getSalesChannelContext())
                ->load();

            $result = $this->apiCrossSellingLoader->getResult($page->getCrossSellings());
            $page->setCrossSellings($result);
        } catch (\Throwable $exception)
        {
            //if there is an exception due to $page->getCrossSellings() and PDP content is loaded via AJAX
            if($this->apiCrossSellingLoader->getApiContext()->isAjax())
            {
                $page->setCrossSellings(new CrossSellingElementCollection());
            }
        }

    }

}
