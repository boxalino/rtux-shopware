<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Framework\Content\Page;

use Boxalino\RealTimeUserExperience\Service\Api\ApiCallServiceInterface;
use Boxalino\RealTimeUserExperience\Service\Api\Request\ContextInterface;
use Boxalino\RealTimeUserExperience\Service\Api\Request\RequestDefinitionInterface;
use Boxalino\RealTimeUserExperience\Service\Api\Util\Configuration;
use Shopware\Core\Content\Category\Exception\CategoryNotFoundException;
use Shopware\Core\Content\Product\SalesChannel\Search\ProductSearchGatewayInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\Routing\Exception\MissingRequestParameterException;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Framework\Page\StorefrontSearchResult;
use Shopware\Storefront\Page\GenericPageLoader;
use Shopware\Storefront\Page\Page;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AutocompletePageLoader
 * Sample based on a familiar ShopwarePageLoader component
 *
 * @package Boxalino\RealTimeUserExperience\Service\Api\Content\Page
 */
class ApiPageLoader extends ApiLoader
{

    /**
     * @var GenericPageLoader
     */
    protected $genericLoader;

    public function __construct(
        ApiCallServiceInterface $apiCallService,
        Configuration $configuration,
        EventDispatcherInterface $eventDispatcher,
        GenericPageLoader $genericLoader

    ) {
        parent::__construct($apiCallService, $configuration, $eventDispatcher);
        $this->genericLoader = $genericLoader;
    }

    /**
     * Loads the content of an API Response page
     *
     * @param Request $request
     * @param SalesChannelContext $salesChannelContext
     * @return ApiResponsePage
     * @throws CategoryNotFoundException
     * @throws InconsistentCriteriaIdsException
     * @throws MissingRequestParameterException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function load(Request $request, SalesChannelContext $salesChannelContext): ApiResponsePage
    {
        $page = $this->genericLoader->load($request, $salesChannelContext);
        $page = ApiResponsePage::createFrom($page);

        $this->call($request, $salesChannelContext);

        if($this->apiCallService->isFallback())
        {
            throw new \Exception($this->apiCallService->getFallbackMessage());
        }

        /** set page properties */
        $page->setBlocks($this->apiCallService->getApiResponse()->getBlocks());
        $page->setRequestId($this->apiCallService->getApiResponse()->getRequestId());
        $page->setGroupBy($this->getGroupBy());
        $page->setVariantUuid($this->getVariantUuid());
        $page->setHasSearchSubPhrases($this->apiCallService->getApiResponse()->hasSearchSubPhrases());
        $page->setRedirectUrl($this->apiCallService->getApiResponse()->getRedirectUrl());
        $page->setTotalHitCount($this->apiCallService->getApiResponse()->getHitCount());
        $page->setSearchTerm(
            (string) $request->query->get('search', "")
        );
        if($this->apiCallService->getApiResponse()->isCorrectedSearchQuery())
        {
            $page->setSearchTerm((string) $this->apiCallService->getApiResponse()->getCorrectedSearchQuery());
        }

        $this->eventDispatcher->dispatch(
            new ApiPageLoadedEvent($page, $salesChannelContext, $request)
        );

        return $page;
    }

}
