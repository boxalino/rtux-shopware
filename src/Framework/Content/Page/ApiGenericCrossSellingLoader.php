<?php
namespace Boxalino\RealTimeUserExperience\Framework\Content\Page;

use Boxalino\RealTimeUserExperience\Framework\Content\BxAttributeElement;
use Boxalino\RealTimeUserExperienceApi\Framework\Content\Page\ApiLoaderAbstract;
use Boxalino\RealTimeUserExperienceApi\Framework\Content\Page\ApiLoaderInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\ApiCallServiceInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Response\Accessor\Block;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Util\ConfigurationInterface;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepositoryInterface;
use Shopware\Core\Content\Product\SalesChannel\CrossSelling\CrossSellingElementCollection;
use Shopware\Core\Content\Product\SalesChannel\CrossSelling\CrossSellingElement;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ApiGenericCrossSellingLoader
 *
 * The default ApiLoader is extended in order to allow further development&transformation to process a cross-selling integration
 * to be used as base for the subscriber
 *
 * @package Boxalino\RealTimeUserExperience\Framework\Content\Page
 */
abstract class ApiGenericCrossSellingLoader extends ApiLoaderAbstract
    implements ApiLoaderInterface
{

    use ApiLoaderTrait;

    /**
     * @var SalesChannelRepositoryInterface
     */
    protected $productRepository;

    /**
     * Product ids grouped by the widget response they belong to
     *
     * @var \ArrayIterator
     */
    protected $productIdsByType;

    /**
     * @var null | EntitySearchResult
     */
    protected $crossSellingResponseCollection = null;

    public function __construct(
        ApiCallServiceInterface         $apiCallService,
        ConfigurationInterface          $configuration,
        SalesChannelRepositoryInterface $productRepository
    ){
        parent::__construct($apiCallService, $configuration);
        $this->productRepository = $productRepository;
        $this->productIdsByType = new \ArrayIterator();
    }

    abstract protected function prepareCrossellingResultByApiResponse() : void;

    /**
     * Creates a single product collection with all product IDs which are part of the response
     *
     * the reason is because using productRepository to create new collections for each  response cross-selling type
     * was triggering the sales_channel.product.loaded event which was very time-consuming (Shopware)
     *
     * $crossSellingResponseCollection is later filter to create individual widget cross-selling response elements
     *
     * @return EntitySearchResult|null
     * @throws \Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException
     */
    protected function prepareCrossSellingResponseCollection()
    {
        if(is_null($this->crossSellingResponseCollection))
        {
            $productIds = [];
            /** @var Block $block */
            foreach($this->apiCallService->getApiResponse()->getBlocks() as $block)
            {
                $hitIds = $block->getModel()->getHitIds();
                $productIds = array_merge($productIds, $hitIds);
                $type = $block->getType(); if(is_array($type)) { $type = $type[0];}
                $this->productIdsByType->offsetSet($type, $hitIds);
            }
            if(count($productIds))
            {
                $this->crossSellingResponseCollection = $this->productRepository->search(
                    new Criteria($productIds),
                    $this->getSalesChannelContext()
                );
            }
        }

        return $this->crossSellingResponseCollection;
    }

    /**
     * Filters the main $crossSellingResponseCollection by the product IDs segments of the widget
     *
     * @param string $type
     * @return EntityCollection | null
     */
    protected function getCrossSellCollectionByType(string $type) : ?ProductCollection
    {
        $ids = $this->productIdsByType->offsetGet($type);
        if(empty($ids))
        {
            return null;
        }

        return $this->crossSellingResponseCollection->filter(
            function(ProductEntity $element) use ($ids)
            {
                if(in_array($element->getId(), $ids))
                {
                    return $element;
                }
            }
        )->getEntities();
    }

    /**
     * Set required request elements on the $apiContextInterface (instanceof ItemContextAbstract)
     *
     * @param CrossSellingElementCollection $crossSellingLoaderResult
     * @return self
     */
    public function updateApiContextByCrosssellingCollection(CrossSellingElementCollection $crossSellingLoaderResult) : self
    {
        if($this->getApiContext()->useConfiguredProductsAsContextParameters())
        {
            $mainProductId = $this->getApiContext()->getProductId();

            /** @var  CrossSellingElement $item */
            foreach($crossSellingLoaderResult as $item)
            {
                $this->getApiContext()->addContextParametersByType(
                    $this->getCrossSellingTypeParameterName($item->getCrossSelling()->getTranslated()['name'], $mainProductId),
                    array_values($item->getProducts()->getIds())
                );
            }
        }

        return $this;
    }

    /**
     * @param string $name
     * @param string $mainProductId
     * @return string
     */
    protected function getCrossSellingTypeParameterName(string $name, string $mainProductId) : string
    {
        return preg_replace('/[^a-z0-9]+/', '_', strtolower($name)) . "_" . $mainProductId;
    }

    /**
     * Setting the main product view (item context) on the API content
     */
    public function addItemContextOnApiContext() : void
    {
        /** @var Request $request */
        $request = $this->getRequest();
        $mainProductId = 0;
        if($request->getRequest()->attributes->has("mainProductId"))
        {
            $mainProductId = $request->getRequest()->attributes->get("mainProductId");
            $this->getApiContext()->setProductId($mainProductId);
        }
    }


}
