<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Framework\Content\Page;

use Boxalino\RealTimeUserExperience\Framework\Request\RequestParametersTrait;
use Boxalino\RealTimeUserExperienceApi\Framework\Content\Page\ApiPageLoaderAbstract;
use Boxalino\RealTimeUserExperienceApi\Framework\Content\Page\ApiResponsePageInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\ApiCallServiceInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Util\ConfigurationInterface;
use Shopware\Core\Content\Category\Exception\CategoryNotFoundException;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\Routing\Exception\MissingRequestParameterException;
use Shopware\Storefront\Page\GenericPageLoader;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AutocompletePageLoader
 * Sample based on a familiar ShopwarePageLoader component
 *
 * @package Boxalino\RealTimeUserExperience\Service\Api\Content\Page
 */
class ApiPageLoader extends ApiPageLoaderAbstract
{
    use ApiLoaderTrait;
    use RequestParametersTrait;

    /**
     * @var GenericPageLoader
     */
    protected $genericLoader;

    /**
     * @var EventDispatcherInterface 
     */
    protected $eventDispatcher;

    public function __construct(
        ApiCallServiceInterface $apiCallService,
        ConfigurationInterface $configuration,
        EventDispatcherInterface $eventDispatcher,
        GenericPageLoader $genericLoader
    ) {
        parent::__construct($apiCallService, $configuration);
        $this->eventDispatcher = $eventDispatcher;
        $this->genericLoader = $genericLoader;
    }

    /**
     * @return ApiResponsePageInterface
     * @throws CategoryNotFoundException
     * @throws InconsistentCriteriaIdsException
     * @throws MissingRequestParameterException
     */
    public function getApiResponsePage(Request $request): ApiResponsePageInterface
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

}
