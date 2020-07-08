<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Framework\Content\Listing;

use Boxalino\RealTimeUserExperienceApi\Framework\Content\Listing\ApiSortingModelInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Response\Accessor\AccessorInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Response\Accessor\AccessorModelInterface;
use Boxalino\RealTimeUserExperienceApi\Service\ErrorHandler\MissingDependencyException;
use Boxalino\RealTimeUserExperienceApi\Framework\Content\Listing\ApiSortingModelAbstract;
use Shopware\Core\Content\Product\SalesChannel\Listing\ProductListingFeaturesSubscriber;
use Shopware\Core\Content\Product\SalesChannel\Listing\ProductListingSorting;
use Shopware\Core\Content\Product\SalesChannel\Listing\ProductListingSortingRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;

/**
 * Class ApiSortingModel
 * @package Boxalino\RealTimeUserExperience\Framework\Content\Listing
 */
class ApiSortingModel extends ApiSortingModelAbstract
    implements ApiSortingModelInterface
{

    /**
     * @var ProductListingSortingRegistry
     */
    protected $productListingSortingRegistry;

    public function __construct(ProductListingSortingRegistry $productListingSortingRegistry)
    {
        parent::__construct();
        $this->productListingSortingRegistry = $productListingSortingRegistry;
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
     * @return string
     */
    public function getDefaultSortField(): string
    {
        return ProductListingFeaturesSubscriber::DEFAULT_SEARCH_SORT;
    }

    /**
     * Default sorting order (asc,desc)
     *
     * @return string
     */
    public function getDefaultSortDirection() : string
    {
        return ApiSortingModelInterface::SORT_ASCENDING;
    }

    /**
     * Transform a request key to a valid API sort
     * @param string $key
     * @return array
     */
    public function getRequestSorting(string $key) : array
    {
        if($this->has($key))
        {
            $sorting = $this->get($key);
            $mapping = [];
            foreach($sorting->getFields() as $field => $direction)
            {
                $reverse = mb_strtolower($direction) === ApiSortingModelInterface::SORT_DESCENDING ?? false;
                $mapping[] = ["field" => $this->getRequestField($field), "reverse" => $reverse];
            }

            return $mapping;
        }

        return [];
    }

    /**
     * Based on the response, transform the response field+direction into a e-shop valid sorting
     *
     * @return string
     */
    public function getCurrent() : string
    {
        $responseField = $this->getCurrentApiSortField();
        if(!empty($responseField))
        {
            $direction = $this->getCurrentSortDirection();
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

        return $this->get(ProductListingFeaturesSubscriber::DEFAULT_SEARCH_SORT)->getKey();
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
