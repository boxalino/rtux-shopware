<?php
namespace Boxalino\RealTimeUserExperience\Service\Api\Response\Accessor;

use ArrayIterator;

/**
 * @package Boxalino\RealTimeUserExperience\Service\Api\Response\Accessor
 */
interface AccessorFacetModelInterface extends AccessorModelInterface
{

    /**
     * @return ArrayIterator
     */
    public function getFacets() : \ArrayIterator;

}
