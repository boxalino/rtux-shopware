<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Framework\Content\Listing;

use Boxalino\RealTimeUserExperienceApi\Framework\Content\Listing\ApiSortingModelInterface;
use Boxalino\RealTimeUserExperienceApi\Framework\Content\Listing\ApiSortingModelAbstract;

/**
 * Class ApiSortingModel
 *
 * The sort is described by 2 parameters: field (ex:price) and direction (ex:desc)
 *
 * The ApiSortingModelInterface dependency is added with the integration repository
 * The ApiSortingModel is used as "model" for the Layout Block
 *
 * @package Boxalino\RealTimeUserExperience\Framework\Content\Listing
 */
class ApiSortingModel extends ApiSortingModelAbstract
    implements ApiSortingModelInterface
{

    /**
     * The default sort field recommended with the Boxalino API is the "score" (label: "Relevance"/"Recommended")
     * because the product order is the recommended one
     *
     * @return string
     */
    public function getDefaultSortField(): string
    {
        return "score";
    }

    /**
     * @return string
     */
    public function getDefaultSortDirection() : string
    {
        return ApiSortingModelInterface::SORT_ASCENDING;
    }


}
