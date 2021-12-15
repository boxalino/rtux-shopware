<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Framework\Content\Page;

use Boxalino\RealTimeUserExperience\Framework\Request\RequestParametersTrait;
use Boxalino\RealTimeUserExperienceApi\Framework\Content\Page\ApiPageLoaderAbstract;
use Boxalino\RealTimeUserExperienceApi\Framework\Content\Page\ApiResponsePageInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\ApiCallServiceInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\RequestInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Response\ApiResponseViewInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Util\ConfigurationInterface;
use Shopware\Core\Content\Category\Exception\CategoryNotFoundException;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\Routing\Exception\MissingRequestParameterException;
use Shopware\Storefront\Page\GenericPageLoader;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Shopware\Core\System\SystemConfig\SystemConfigService;

/**
 * Class AutocompletePageLoader
 * Sample based on a familiar ShopwarePageLoader component
 *
 * @package Boxalino\RealTimeUserExperience\Framework\Content\Page
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

    /**
     * @var SystemConfigService
     */
    protected $systemConfigService;

    public function __construct(
        ApiCallServiceInterface $apiCallService,
        ConfigurationInterface $configuration,
        EventDispatcherInterface $eventDispatcher,
        GenericPageLoader $genericLoader,
        SystemConfigService $systemConfigService
    ) {
        parent::__construct($apiCallService, $configuration);
        $this->eventDispatcher = $eventDispatcher;
        $this->genericLoader = $genericLoader;
        $this->systemConfigService = $systemConfigService;
    }

    /**
     * @return ApiResponsePageInterface
     * @throws CategoryNotFoundException
     * @throws InconsistentCriteriaIdsException
     * @throws MissingRequestParameterException
     */
    public function getApiResponsePage(): ?ApiResponseViewInterface
    {
        if(!$this->apiResponsePage)
        {
            $page = $this->genericLoader->load($this->getRequest()->getRequest(), $this->getSalesChannelContext());
            $this->apiResponsePage = ApiResponsePage::createFrom($page);
            $this->apiResponsePage->setCurrency($this->getSalesChannelContext()->getCurrency()->getIsoCode());
        }

        return $this->apiResponsePage;
    }

    /**
     * @param RequestInterface $request
     * @param ApiResponsePageInterface $page
     */
    protected function dispatchEvent(RequestInterface $request, ApiResponsePageInterface $page)
    {
        $this->eventDispatcher->dispatch(
            new ApiPageLoadedEvent($page, $this->getSalesChannelContext(), $request->getRequest())
        );
    }


}
