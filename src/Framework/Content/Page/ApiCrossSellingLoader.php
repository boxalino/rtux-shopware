<?php
namespace Boxalino\RealTimeUserExperience\Framework\Content\Page;

use Boxalino\RealTimeUserExperience\Framework\Content\BxAttributeElement;
use Boxalino\RealTimeUserExperience\Framework\Content\Listing\ApiEntityCollectionModel;
use Boxalino\RealTimeUserExperienceApi\Framework\Content\Page\ApiLoaderInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Response\Accessor\Block;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Response\ApiResponseViewInterface;
use Shopware\Core\Content\Product\Aggregate\ProductCrossSelling\ProductCrossSellingEntity;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Content\Product\SalesChannel\CrossSelling\CrossSellingElementCollection;
use Shopware\Core\Content\Product\SalesChannel\CrossSelling\CrossSellingElement;

/**
 * Class ApiCrossSellingLoader
 *
 * The default ApiLoader is extended in order to allow further development&transformation to process a cross-selling integration
 * to be used as base for the subscriber
 *
 * @package Boxalino\RealTimeUserExperience\Framework\Content\Page
 */
class ApiCrossSellingLoader extends ApiGenericCrossSellingLoader
    implements ApiLoaderInterface
{

    use StoreApiTrait;

    /**
     * @var null | CrossSellingElementCollection
     */
    protected $crossellingResultCollection = null;

    /**
     * Used for the non-ajax PDP content load
     *
     * @return CrossSellingElementCollection
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getResult(CrossSellingElementCollection $crossSellingLoaderResult): CrossSellingElementCollection
    {
        /** if the PDP recommendations are to be loaded via AJAX - empty the product crossellings collection */
        if ($this->getApiContext()->isAjax())
        {
            if(!$this->isStoreApiRequest())
            {
                return new CrossSellingElementCollection();
            }
        }

        $this->addItemContextOnApiContext();
        $this->updateApiContextByCrosssellingCollection($crossSellingLoaderResult);
        try {
            $this->call();
        } catch (\Throwable $exception) {
            return $crossSellingLoaderResult;
        }

        $this->prepareCrossSellingResponseCollection();
        $result = $this->getCrossellingResultByApiResponse();
        if ($result->count() > 0) {
            return $result;
        }

        return $crossSellingLoaderResult;
    }

    /**
     * Create the SW6.* Crossselling collections (version-dependent)
     * (The products have all been priorly loaded in a generic collection to optimize the process)
     *
     * @return CrossSellingElementCollection
     */
    protected function getCrossellingResultByApiResponse(): CrossSellingElementCollection
    {
        if(is_null($this->crossellingResultCollection))
        {
            $this->prepareCrossellingResultByApiResponse();
        }

        return $this->crossellingResultCollection;
    }

    /**
     * Initializing the crosselling result collection
     */
    protected function prepareCrossellingResultByApiResponse(): void
    {
        $this->crossellingResultCollection = new CrossSellingElementCollection();
        $index = 0;
        foreach ($this->apiCallService->getApiResponse()->getBlocks() as $block) {
            /**
             * the logic on validating the child block
             * if the cross-selling narrative is properly structured - the ProductsCollection and Model with Entities
             * will be valid content
             */
            /** @var Block $block */
            if (property_exists($block, "model")
                && $block->getModel() instanceof ApiEntityCollectionModel
                && property_exists($block, "bxHits")
            ) {
                $index++;
                $name = $block->getName();
                if (is_array($name)) {
                    $name = $name[0];
                }
                $type = $block->getType();
                if (is_array($type)) {
                    $type = $type[0];
                }
                $crossSelling = $this->createCrossSellingEntity(
                    $index, $name, $type, (bool)$index == 1
                );
                $productCollection = $this->getCrossSellCollectionByType($type);
                if (is_null($productCollection)) {
                    continue;
                }

                if ($productCollection->count() > 0) {
                    /** @var CrossSellingElement $element */
                    $element = $this->loadCrossSellingElement($crossSelling, $productCollection);
                    try {
                        $element->addExtension("bxAttributes", new BxAttributeElement($block->getBxAttributes()));
                    } catch (\Throwable $exception) {
                        $element->addExtension("bxAttributes", new BxAttributeElement());
                    }

                    $this->crossellingResultCollection->add($element);
                }
            }
        }
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
     * @return ApiLoaderInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function load(): ApiLoaderInterface
    {
        return $this;
    }

    /**
     * @return ApiResponseViewInterface|null
     */
    public function getApiResponsePage(): ?ApiResponseViewInterface
    {
        return null;
    }
    
    
}
