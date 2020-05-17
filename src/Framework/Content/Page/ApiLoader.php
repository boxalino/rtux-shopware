<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Framework\Content\Page;

use Boxalino\RealTimeUserExperience\Service\Api\ApiCallServiceInterface;
use Boxalino\RealTimeUserExperience\Service\Api\Request\ContextInterface;
use Boxalino\RealTimeUserExperience\Service\Api\Util\Configuration;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ApiLoader
 *
 * @package Boxalino\RealTimeUserExperience\Service\Api\Content\Page
 */
class ApiLoader
{

    /**
     * @var ContextInterface
     */
    protected $apiContextInterface;

    /**
     * @var ApiCallServiceInterface
     */
    protected $apiCallService;

    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var \ArrayIterator
     */
    protected $apiContextInterfaceList;

    public function __construct(
        ApiCallServiceInterface $apiCallService,
        Configuration $configuration,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->configuration = $configuration;
        $this->apiCallService = $apiCallService;
        $this->eventDispatcher = $eventDispatcher;
        $this->apiContextInterfaceList = new \ArrayIterator();
    }

    /**
     * Makes a call to the Boxalino API
     *
     * @param Request $request
     * @param SalesChannelContext $salesChannelContext
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function call(Request $request, SalesChannelContext $salesChannelContext) : void
    {
        $this->apiContextInterface->setSalesChannelContext($salesChannelContext);
        $this->apiCallService->call(
            $this->apiContextInterface->get($request),
            $this->configuration->getRestApiEndpoint($salesChannelContext->getSalesChannel()->getId())
        );

        if(!$this->apiCallService->isFallback())
        {
            /** this is a required step */
            $this->apiCallService->getApiResponse()->getAccessorHandler()->setSalesChannelContext($salesChannelContext);
        }

        return;
    }

    /**
     * @return string
     */
    protected function getGroupBy() : string
    {
        return $this->apiCallService->getApiResponse()->getGroupBy();
    }

    /**
     * @return string
     */
    protected function getVariantUuid() : string
    {
        return $this->apiCallService->getApiResponse()->getVariantId();
    }

    /**
     * @param ContextInterface $apiContextInterface
     * @return $this
     */
    public function setApiContextInterface(ContextInterface $apiContextInterface)
    {
        $this->apiContextInterface = $apiContextInterface;
        return $this;
    }

    /**
     * Used to create bundle requests
     *
     * @param ContextInterface $apiContextInterface
     * @param string $widget
     * @return $this
     */
    public function addApiContextInterface(ContextInterface $apiContextInterface, string $widget)
    {
        $this->apiContextInterfaceList->offsetSet($widget, $apiContextInterface);
        return $this;
    }

}
