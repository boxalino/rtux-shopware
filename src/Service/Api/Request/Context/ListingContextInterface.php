<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Service\Api\Request\Context;

use Boxalino\RealTimeUserExperience\Framework\Request\ListingContextAbstract;
use Boxalino\RealTimeUserExperience\Service\Api\Request\ContextInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Interface SearchContextInterface
 * @package Boxalino\RealTimeUserExperience\Service\Api\Request
 */
interface ListingContextInterface extends ContextInterface
{

    /**
     * Adds facets to the request (field-values)
     *
     * @param Request $request
     * @return ListingContextAbstract
     */
    public function addFacets(Request $request) : ListingContextAbstract;

    /**
     * Adds range facets to the request
     *
     * @param Request $request
     * @return ListingContextAbstract
     */
    public function addRangeFacets(Request $request) : ListingContextAbstract;

    /**
     * Returns an array with the following structure:
     * [propertyName => ["to"=>requestParameterForToValue, "from"=>requestParameterForFromValue]]
     *
     * This list is being used in the addRangeFacets() function
     *
     * @return mixed
     */
    public function getRangeProperties() : array;

}
