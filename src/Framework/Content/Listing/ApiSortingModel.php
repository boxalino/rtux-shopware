<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Framework\Content\Listing;

use Boxalino\RealTimeUserExperience\Service\Api\Response\Accessor\AccessorInterface;
use Boxalino\RealTimeUserExperience\Service\Api\Response\Accessor\AccessorModelInterface;
use Boxalino\RealTimeUserExperience\Service\ErrorHandler\MissingDependencyException;
use Shopware\Core\Content\Product\SalesChannel\Listing\ProductListingSorting;
use Shopware\Core\Content\Product\SalesChannel\Listing\ProductListingSortingRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;

/**
 * Class ApiSortingModel
 * @package Boxalino\RealTimeUserExperience\Framework\Content\Listing
 */
class ApiSortingModel implements AccessorModelInterface
{

    const BOXALINO_DEFAULT_SORT_FIELD = "score";

    /**
     * @var ProductListingSorting[]
     */
    protected $sortings = [];

    /**
     * @var ProductListingSortingRegistry
     */
    protected $productListingSortingRegistry;

    /**
     * @var \ArrayObject
     */
    protected $sortingMapRequest;

    /**
     * @var \ArrayObject
     */
    protected $sortingMapResponse;

    /**
     * @var AccessorInterface
     */
    protected $activeSorting;

    public function __construct(ProductListingSortingRegistry $productListingSortingRegistry)
    {
        $this->sortingMapRequest = new \ArrayObject();
        $this->sortingMapResponse = new \ArrayObject();
        $this->productListingSortingRegistry = $productListingSortingRegistry;
    }

    /**
     * Retrieving the declared Boxalino field linked to Shopware6 sorting declaration
     *
     * @param string $field
     * @return string
     */
    public function getRequestField(string $field) : string
    {
        if($this->sortingMapRequest->offsetExists($field))
        {
            return $this->sortingMapRequest->offsetGet($field);
        }

        throw new MissingDependencyException("BoxalinoApiSorting: The required request field does not have a sorting mapping.");
    }

    /**
     * Retrieving the declared Shopware field linked to Boxalino fields
     *
     * @param string $field
     * @return string
     */
    public function getResponseField(string $field) : string
    {
        if($this->sortingMapResponse->offsetExists($field))
        {
            return $this->sortingMapResponse->offsetGet($field);
        }

        throw new MissingDependencyException("BoxalinoApiSorting: The required response field does not have a sorting mapping.");
    }

    /**
     * Accessing the sorting declared for a key on ProductListingSortingRegistry
     * (Shopware6 standard)
     *
     * @param string $key
     * @return ProductListingSorting|null
     */
    public function get(string $key): ?ProductListingSorting
    {
        return $this->productListingSortingRegistry->get($key);
    }

    /**
     * Check if a sorting rule key has been declared for ProductListingSortingRegistry
     * (Shopware6 standard)
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return $this->productListingSortingRegistry->has($key);
    }

    /**
     * Transform a request key to a valid API sort
     * @param string $key
     * @return array
     */
    public function requestTransform(string $key) : array
    {
        if($this->has($key))
        {
            $sorting = $this->get($key);
            $mapping = [];
            foreach($sorting->getFields() as $field => $direction)
            {
                $reverse = mb_strtoupper($direction) === FieldSorting::DESCENDING ?? false;
                $mapping[] = ["field" => $this->getRequestField($field), "reverse" => $reverse];
            }

            return $mapping;
        }

        return [];
    }

    /**
     * Accessing the sortings available from ProductListingSortingRegistry
     * (Shopware6 standard)
     *
     * @return array
     */
    public function getSortings(): array
    {
        return $this->productListingSortingRegistry->getSortings();
    }

    /**
     * Adds mapping between a Shopware6 field definition (as inserted via ProductListingSortingRegistry tagging)
     * and a valid Boxalino field
     *
     * @param array $mappings
     * @return $this
     */
    public function add(array $mappings)
    {
        foreach($mappings as $shopwareField => $boxalinoField)
        {
            $this->sortingMapRequest->offsetSet($shopwareField, $boxalinoField);
            $this->sortingMapResponse->offsetSet($boxalinoField, $shopwareField);
        }

        return $this;
    }

    /**
     * Based on the response, transform the response field+direction into a Shopware6 valid sorting
     */
    public function getCurrent() : string
    {
        $responseField = $this->activeSorting->getField();
        if(!empty($responseField))
        {
            $direction = $this->activeSorting->getReverse() === true ? mb_strtolower(FieldSorting::DESCENDING)
                : mb_strtolower(FieldSorting::ASCENDING);
            $field = $this->getResponseField($responseField);
            foreach($this->getSortings() as $sorting)
            {
                foreach($sorting->getFields() as $sortingField=>$sortingDirection)
                {
                    if($sortingField == $field && $sortingDirection == $direction)
                    {
                        return $sorting->getKey();
                    }
                }
            }
        }

        return $this->get(self::BOXALINO_DEFAULT_SORT_FIELD)->getKey();
    }

    /**
     * Setting the active sorting
     *
     * @param AccessorInterface $responseSorting
     * @return $this
     */
    public function setActiveSorting(AccessorInterface $responseSorting)
    {
        $this->activeSorting = $responseSorting;
        return $this;
    }

    /**
     * @param null | AccessorInterface $context
     * @return AccessorModelInterface
     */
    public function addAccessorContext(?AccessorInterface $context = null): AccessorModelInterface
    {
        $this->setActiveSorting($context->getSorting());
        return $this;
    }

}
