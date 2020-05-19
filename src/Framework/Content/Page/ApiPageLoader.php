<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Framework\Content\Page;

use Boxalino\RealTimeUserExperience\Framework\SalesChannelContextTrait;
use Boxalino\RealTimeUserExperienceApi\Framework\Content\Page\ApiLoaderAbstract;
use Boxalino\RealTimeUserExperienceApi\Framework\Content\Page\ApiResponsePageInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\ApiCallServiceInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\ContextInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\RequestDefinitionInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Util\ConfigurationInterface;
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
class ApiPageLoader extends ApiLoaderAbstract
{
    use SalesChannelContextTrait;

    /**
     * @var GenericPageLoader
     */
    protected $genericLoader;

    public function __construct(
        ApiCallServiceInterface $apiCallService,
        ConfigurationInterface $configuration,
        EventDispatcherInterface $eventDispatcher,
        ApiResponsePageInterface $apiResponsePage,
        GenericPageLoader $genericLoader
    ) {
        parent::__construct($apiCallService, $configuration, $eventDispatcher, $apiResponsePage);
        $this->genericLoader = $genericLoader;
    }

    /**
     * @return ApiResponsePageInterface
     * @throws CategoryNotFoundException
     * @throws InconsistentCriteriaIdsException
     * @throws MissingRequestParameterException
     */
    public function getApiResponsePage(): ApiResponsePageInterface
    {
        $page = $this->genericLoader->load($request, $this->getSalesChannelContext());
        return ApiResponsePage::createFrom($page);
    }

    /**
     * @param Request $request
     * @param ApiResponsePageInterface $page
     */
    protected function dispatchEvent(Request $request, ApiResponsePageInterface $page)
    {
        $this->eventDispatcher->dispatch(
            new ApiPageLoadedEvent($page, $this->getSalesChannelContext(), $request)
        );
    }

    /**
     * @return string
     */
    protected function getQueryParameter() : string
    {
        return "search";
    }

}
