<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Framework\Content\Listing;

use Boxalino\RealTimeUserExperience\Framework\SalesChannelContextTrait;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Response\Accessor\AccessorInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Response\Accessor\AccessorModelInterface;
use Boxalino\RealTimeUserExperienceApi\Framework\Content\Listing\ApiEntityCollectionModelAbstract;
use Psr\Log\LoggerInterface;
use Shopware\Core\Content\Cms\DataResolver\CriteriaCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepositoryInterface;
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
class ApiEntityCollectionModel extends ApiEntityCollectionModelAbstract
    implements AccessorModelInterface
{
    use SalesChannelContextTrait;

    /**
     * @var SalesChannelRepositoryInterface
     */
    private $productRepository;

    /**
     * @var null | EntitySearchResult
     */
    protected $collection = null;

    public function __construct(
        SalesChannelRepositoryInterface $productRepository
    ){
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
     * @param \ArrayIterator $blocks
     * @param string $hitAccessor
     * @param string $idField
     */
    public function setHitIds(\ArrayIterator $blocks, string $hitAccessor, string $idField = "id")
    {
        $ids = array_map(function(AccessorInterface $block) use ($hitAccessor, $idField) {
            if(property_exists($block, $hitAccessor))
            {
                $value = $block->get($hitAccessor)->get($idField);
                if(is_array($value))
                {
                    return $value[0];
                }

                return $value;
            }
        }, $blocks->getArrayCopy());

        $this->hitIds = $ids;
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
