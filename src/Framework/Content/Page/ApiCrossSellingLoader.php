<?php
namespace Boxalino\RealTimeUserExperience\Framework\Content\Page;

use Boxalino\RealTimeUserExperience\Framework\Content\Listing\ApiEntityCollectionModel;
use Boxalino\RealTimeUserExperienceApi\Framework\Content\Page\ApiLoaderAbstract;
use Boxalino\RealTimeUserExperienceApi\Framework\Content\Page\ApiLoaderInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\ApiCallServiceInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\RequestInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Response\ApiResponseViewInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Util\ConfigurationInterface;
use Shopware\Core\Content\Product\Aggregate\ProductCrossSelling\ProductCrossSellingEntity;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepositoryInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Page\Product\CrossSelling\CrossSellingElement;
use Shopware\Storefront\Page\Product\CrossSelling\CrossSellingLoaderResult;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ApiCrossSellingLoader
 *
 * The default ApiLoader is extended in order to allow further development&transformation to process a cross-selling integration
 * to be used as base for the subscriber
 *
 * @package Boxalino\RealTimeUserExperience\Framework\Content\Page
 */
class ApiCrossSellingLoader extends ApiLoaderAbstract
    implements ApiLoaderInterface
{

    use ApiLoaderTrait;

    /**
     * @var SalesChannelRepositoryInterface
     */
    private $productRepository;

    /**
     * Product ids grouped by the widget response they belong to
     *
     * @var \ArrayIterator
     */
    private $productIdsByType;

    /**
     * @var null | EntitySearchResult
     */
    private $crossSellingResponseCollection = null;


    public function __construct(
        ApiCallServiceInterface $apiCallService,
        ConfigurationInterface $configuration,
        SalesChannelRepositoryInterface $productRepository
    ){
        parent::__construct($apiCallService, $configuration);
        $this->productRepository = $productRepository;
        $this->productIdsByType = new \ArrayIterator();
    }

    public function load() : ApiLoaderInterface
    {
        return $this;
    }

    public function getApiResponsePage(): ?ApiResponseViewInterface
    {
        return null;
    }

    /**
     * @return CrossSellingLoaderResult
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getResult(CrossSellingLoaderResult $crossSellingLoaderResult) : CrossSellingLoaderResult
    {
        $this->setRequestInterfaceContext($crossSellingLoaderResult);
        try{
            $this->call();
        } catch (\Throwable $exception)
        {
            return $crossSellingLoaderResult;
        }

        $this->setCrossSellingResponseCollection();
        $result = new CrossSellingLoaderResult(); $index = 0;
        foreach ($this->apiCallService->getApiResponse()->getBlocks() as $block)
        {
            /**
             * the logic on validating the child block
             * if the cross-selling narrative is properly structured - the ProductsCollection and Model with Entities
             * will be valid content
             */
            if(property_exists($block, "model")
                && $block->getModel() instanceof ApiEntityCollectionModel
                && property_exists($block, "productsCollection")
            ){
                $index++;
                $name = $block->getName(); if(is_array($name)) { $name = $name[0];}
                $type = $block->getType(); if(is_array($type)) { $type = $type[0];}
                $crossSelling = $this->createCrossSellingEntity(
                    $index, $name, $type, (bool) $index==1
                );
                $productCollection = $this->getCrossSellCollectionByType($type);
                if($productCollection->count() > 0)
                {
                    $element = $this->loadCrossSellingElement($crossSelling, $productCollection);
                    $result->add($element);
                }
            }
        }

        if($result->count() > 0)
        {
            return $result;
        }

        return $crossSellingLoaderResult;
    }


    /**
     * Creates a cross-selling item to be added to the cross-selling loader result
     *
     * @param ProductCrossSellingEntity $crossSelling
     * @param EntityCollection $collection
     * @return CrossSellingElement
     */
    protected function loadCrossSellingElement(ProductCrossSellingEntity $crossSelling, ProductCollection $collection) : CrossSellingElement
    {
        $element = new CrossSellingElement();
        $element->setCrossSelling($crossSelling);
        $element->setProducts($collection);
        $element->setTotal($collection->count());

        return $element;
    }

    /**
     * Mocks a cross-selling product entity
     * The set properties are those as requested via the template
     *
     * @param int $position
     * @param string $name
     * @param string $type
     * @param bool $active
     * @return ProductCrossSellingEntity
     */
    protected function createCrossSellingEntity(int $position, string $name, string $type, bool $active = false) : ProductCrossSellingEntity
    {
        $crossSelling = new ProductCrossSellingEntity();
        $crossSelling->setActive($active);
        $crossSelling->setId($type);
        $crossSelling->setName($name);
        $crossSelling->setTranslated(['name' => $name]);
        $crossSelling->setPosition($position);

        return $crossSelling;
    }

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
    protected function setCrossSellingResponseCollection()
    {
        if(is_null($this->crossSellingResponseCollection))
        {
            $blocks = $this->apiCallService->getApiResponse()->getBlocks();
            $productIds = [];
            foreach($blocks as $block)
            {
                $hitIds = $block->getModel()->getHitIds();
                $productIds = array_merge($productIds, $hitIds);
                $type = $block->getType(); if(is_array($type)) { $type = $type[0];}
                $this->productIdsByType->offsetSet($type, $hitIds);
            }
            $this->crossSellingResponseCollection = $this->productRepository->search(new Criteria($productIds), $this->getSalesChannelContext());
        }

        return $this->crossSellingResponseCollection;
    }

    /**
     * Filters the main $crossSellingResponseCollection by the product IDs segments of the widget
     *
     * @param string $type
     * @return EntityCollection
     */
    protected function getCrossSellCollectionByType(string $type) : ProductCollection
    {
        $ids = $this->productIdsByType->offsetGet($type);
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
     * @param CrossSellingLoaderResult $crossSellingLoaderResult
     * @return self
     */
    public function setRequestInterfaceContext(CrossSellingLoaderResult $crossSellingLoaderResult) : self
    {
        /** @var Request $request */
        $request = $this->getRequest();
        if($request->getRequest()->attributes->has("mainProductId"))
        {
            $this->getApiContext()->setProductId($request->getRequest()->attributes->get("mainProductId"));
        }
        if($this->getApiContext()->useConfiguredProductsAsContextParameters())
        {
            foreach($crossSellingLoaderResult as $item)
            {
                $name = $item->getCrossSelling()->getTranslated()['name'];
                $type = preg_replace('/[^a-z0-9]+/', '_', strtolower($name));
                $ids = $item->getProducts()->getIds();
                $this->getApiContext()->addContextParametersByType($type, $ids);
            }
        }

        return $this;
    }

}
