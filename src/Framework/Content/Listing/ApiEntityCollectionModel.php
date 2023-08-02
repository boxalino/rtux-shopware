<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Framework\Content\Listing;

use Boxalino\RealTimeUserExperience\Framework\SalesChannelContextTrait;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Response\Accessor\AccessorInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Response\Accessor\AccessorModelInterface;
use Boxalino\RealTimeUserExperienceApi\Framework\Content\Listing\ApiEntityCollectionModel as RtuxApiEntityCollection;
use Psr\Log\LoggerInterface;
use Shopware\Core\Content\Cms\DataResolver\CriteriaCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

/**
 * Class ApiEntityCollectionModel
 *
 * Item refers to any data model/logic that is desired to be rendered/displayed
 * The integrator can decide to either use all data as provided by the Narrative API,
 * or to design custom data layers to represent the fetched content
 *
 * @package Boxalino\RealTimeUserExperience\Framework\Content\Listing
 */
class ApiEntityCollectionModel extends RtuxApiEntityCollection
    implements AccessorModelInterface
{
    use SalesChannelContextTrait;

    /**
     * @var SalesChannelRepository
     */
    private $productRepository;

    /**
     * @var null | EntitySearchResult
     */
    protected $collection = null;

    /**
     * @var null | EntitySearchResult
     */
    protected $collectionByIds = null;


    public function __construct(
        SalesChannelRepository $productRepository
    ){
        $this->apiCollection = new \ArrayIterator();
        $this->productRepository = $productRepository;
    }

    /**
     * Accessing collection of products based on the hits
     *
     * @return EntitySearchResult
     * @throws \Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException
     */
    public function getCollection() : EntitySearchResult
    {
        if(is_null($this->collection))
        {
            $criteria = new Criteria($this->getHitIds());
            $this->collection = $this->productRepository->search(
                $criteria,
                $this->getSalesChannelContext()
            );
        }

        return $this->collection;
    }

    /**
     * Retrieve the product returned by the API response based on the hit field
     * If the product is not available in the collection - load the collection for the individual items (grouped by ID)
     *
     * @param string $id
     */
    public function getItem(string $id)
    {
        if(is_null($this->collection))
        {
            $this->getCollection();
        }

        if(in_array($id, $this->collection->getIds()))
        {
            return $this->collection->get($id);
        }

        return false;
    }

    /**
     * @param string $id
     * @return mixed|null
     */
    public function getItemById(string $id)
    {
        if(is_null($this->collectionByIds))
        {
            $this->getCollectionByIds();
        }

        if(in_array($id, $this->collectionByIds->getIds()))
        {
            return $this->collectionByIds->get($id);
        }

        return false;
    }

    /**
     * Accessing collection of products based on the hits
     *
     * @return EntitySearchResult
     * @throws \Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException
     */
    public function getCollectionByIds() : EntitySearchResult
    {
        if(is_null($this->collectionByIds))
        {
            $criteria = new Criteria($this->getIds());
            $this->collectionByIds = $this->productRepository->search(
                $criteria,
                $this->getSalesChannelContext()
            );
        }

        return $this->collectionByIds;
    }


    /**
     * @return EntitySearchResult
     */
    public function getLoadedCollection() : EntitySearchResult
    {
        return $this->getCollection();
    }

    /**
     * Preparing element for API preview (ex: pwa context)
     * The protected properties are not public
     */
    public function load(): void
    {
        $this->loadPropertiesToObject(
            $this,
            ["salesChannelContext", "contextId", "defaultSalesChannelLanguageId"],
            ["getItemById", "getItem", "getCollectionById", "getCollection", "getApiCollection"],
            true
        );
    }

    /**
     * @param null | AccessorInterface $context
     * @return AccessorModelInterface
     */
    public function addAccessorContext(?AccessorInterface $context = null): AccessorModelInterface
    {
        parent::addAccessorContext($context);
        $this->setSalesChannelContext($context->getAccessorHandler()->getSalesChannelContext());

        return $this;
    }


}
