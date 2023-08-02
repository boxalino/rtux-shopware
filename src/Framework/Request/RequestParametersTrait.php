<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Framework\Request;

use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\RequestDefinitionInterface;
use Shopware\Core\System\SystemConfig\SystemConfigService;

/**
 * Trait RequestParametersTrait
 *
 * Describes the Shopware6 request parameters
 *
 * @package Boxalino\RealTimeUserExperience\Framework\Request
 */
trait RequestParametersTrait
{
    /**
     * As set in platform/src/Core/Content/Product/SalesChannel/Listing/ProductListingFeaturesSubscriber.php
     * function getCurrentSorting()
     * @return string
     */
    public function getSortParameter() : string
    {
        return "order";
    }

    /**
     * @return string
     */
    public function getSearchParameter() : string
    {
        return 'search';
    }

    /**
     * As set in platform/src/Core/Content/Product/SalesChannel/Listing/ProductListingFeaturesSubscriber.php
     * function getPage()
     * @return string
     */
    public function getPageNumberParameter() : string
    {
        return "p";
    }

    /**
     * As set in platform/src/Core/Content/Product/SalesChannel/Listing/ProductListingFeaturesSubscriber.php,
     * function getLimit()
     * @return string
     */
    public function getPageLimitParameter() : string
    {
        return "limit";
    }

    /**
     * As set in platform/src/Core/Content/Product/SalesChannel/Listing/ProductListingFeaturesSubscriber.php
     * @return string
     */
    public function getDefaultLimitValue() : int
    {
        try{
            $limit = 24;
            $context = $this->getSalesChannelContext();
            if($this->systemConfigService instanceof SystemConfigService)
            {
                $limit = $this->systemConfigService->getInt('core.listing.productsPerPage', $context->getSalesChannelId());
            }

            return $limit <= 0 ? 24 : $limit;
        } catch (\Throwable $exception)
        {
            return 24;
        }
    }

    /**
     * @return string
     */
    public function getBlockViewModeParameter() : string
    {
        return "style";
    }

    /**
     * @return array
     */
    public function getSystemParameters() : array
    {
        return [
            $this->getPageLimitParameter(),
            $this->getPageNumberParameter(),
            $this->getBlockViewModeParameter(),
            $this->getSearchParameter(),
            $this->getSortParameter(),
            RequestDefinitionInterface::BOXALINO_API_REQUEST_INSPECT_FLAG,
            RequestDefinitionInterface::BOXALINO_API_WIDGET_INSPECT_FLAG
        ];
    }


}
