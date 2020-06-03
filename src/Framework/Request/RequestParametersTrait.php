<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Framework\Request;

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
        return 24;
    }

}
