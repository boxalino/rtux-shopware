<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Framework\Content\Page;

use Boxalino\RealTimeUserExperience\Framework\Content\CreateFromTrait;
use Boxalino\RealTimeUserExperience\Framework\Content\Listing\ApiCmsModel;
use Boxalino\RealTimeUserExperienceApi\Framework\Content\Listing\ApiCmsModelInterface;
use Boxalino\RealTimeUserExperienceApi\Framework\Content\Page\ApiBaseLoaderAbstract;
use Boxalino\RealTimeUserExperienceApi\Framework\Content\Page\ApiLoaderInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\ApiCallServiceInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\RequestInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Response\ApiResponseViewInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Util\ConfigurationInterface;
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Struct\Struct;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepositoryInterface;

/**
 * Class ApiCmsLoader
 * Sample based on a familiar ShopwarePageLoader component
 *
 * @package Boxalino\RealTimeUserExperience\Framework\Content\Page
 */
class ApiCmsLoader extends ApiBaseLoaderAbstract
    implements ApiLoaderInterface
{
    use CreateFromTrait;
    use ApiLoaderTrait;

    /**
     * @var array
     */
    protected $cmsConfig = [];

    /**
     * @var SalesChannelRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var \ArrayIterator
     */
    protected $apiResponsePageList = null;

    /**
     * @var string
     */
    protected $storeApiEndpoint = "store-api";

    public function __construct(
        ApiCallServiceInterface $apiCallService,
        ConfigurationInterface $configuration,
        SalesChannelRepositoryInterface $repository
    ){
        parent::__construct($apiCallService, $configuration);
        $this->categoryRepository = $repository;
        $this->apiResponsePageList = new \ArrayIterator();
    }

    /**
     * Loads the content of an API Response page
     */
    public function load()
    {
        $this->addProperties();
        parent::load();
        
        $this->getApiResponsePage()->setNavigationId($this->getNavigationId($this->getRequest()));
        $this->getApiResponsePage()->setCategory($this->loadCategory());
        $this->getApiResponsePage()->setCurrency($this->getSalesChannelContext()->getSalesChannel()->getCurrency()->getIsoCode());

        /** if it`s a store-api request - load the content of the blocks */
        if($this->isStoreApiRequest())
        {
            $this->getApiResponsePage()->load();
        }

        return $this;
    }

    /**
     * Sets category content on the CMS page
     *
     * @return \Shopware\Core\Content\Category\CategoryEntity
     */
    protected function loadCategory() : ?CategoryEntity
    {
        $criteria = new Criteria();
        $criteria->setIds([$this->getNavigationId($this->getRequest())]);
        $criteria->addAssociation('media');
        $criteria->setTotalCountMode(Criteria::TOTAL_COUNT_MODE_NONE);

        /** @var CategoryEntity $category */
        $category = $this->categoryRepository->search($criteria, $this->getSalesChannelContext())->getEntities()->first();

        return $category;
    }

    /**
     * @return ApiResponseViewInterface | Struct
     */
    public function getApiResponsePage(): ?ApiResponseViewInterface
    {
        if(!$this->apiResponsePage)
        {
            $this->apiResponsePage = new ApiCmsModel();
        }

        return $this->apiResponsePage;
    }

    /**
     * @return \ArrayIterator
     */
    public function getApiResponsePageList() : \ArrayIterator
    {
        if(is_null($this->apiResponsePageList))
        {
            $this->apiResponsePageList = new \ArrayIterator();
        }

        return $this->apiResponsePageList;
    }

    /**
     * @param RequestInterface $request
     * @return string
     */
    protected function getNavigationId(RequestInterface $request): string
    {
        return $request->getParam("navigationId", $this->getSalesChannelContext()->getSalesChannel()->getNavigationCategoryId());
    }

    /**
     * The CMS configuration in Shopware are not as key=>value
     * @param array $config
     */
    public function setCmsConfig(array $config)
    {
        $this->cmsConfig = [];
        foreach($config as $key => $configuration)
        {
            $this->cmsConfig[$key] = $configuration['value'];
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getCmsConfig() : array
    {
        return $this->cmsConfig;
    }

    /**
     * Adds properties to the CmsContextAbstract
     */
    protected function addProperties()
    {
        foreach($this->getCmsConfig() as $key => $value)
        {
            if($key == 'widget')
            {
                $this->getApiContext()->setWidget($value);
                continue;
            }
            if($key == 'hitCount')
            {
                $this->getApiContext()->setHitCount((int) $value);
                continue;
            }
            if($key == 'groupBy')
            {
                $this->getApiContext()->setGroupBy($value);
                continue;
            }

            if(!is_null($value) && !empty($value))
            {
                $this->getApiContext()->set($key, $value);
            }
        }
    }

    /**
     * Replicates the narrative content in order to generate the top/bottom/right/left slots
     *
     * @param Struct $apiCmsModel
     * @param string $position
     * @return Struct | null
     */
    public function createSectionFrom(Struct $apiCmsModel, string $position) : ?Struct
    {
        $layoutSegments = $this->apiCallService->getApiResponse()->getResponseSegments();
        if(in_array($position, $layoutSegments) && $apiCmsModel instanceof ApiCmsModelInterface)
        {
            /** @var ApiCmsModelInterface | Struct $segmentNarrativeBlock */
            $segmentNarrativeBlock = $this->createFromStructObject($apiCmsModel, array_merge(['blocks'], $layoutSegments));
            $getterFunction = "get".ucfirst($position);
            $setterFunction = "set".ucfirst($position);
            $segmentNarrativeBlock->setBlocks($apiCmsModel->$getterFunction());
            $segmentNarrativeBlock->$setterFunction(new \ArrayIterator());

            return $segmentNarrativeBlock;
        }

        return null;
    }

    /**
     * @return SalesChannelRepositoryInterface
     */
    public function getCategoryRepository(): SalesChannelRepositoryInterface
    {
        return $this->categoryRepository;
    }

    /**
     * @param SalesChannelRepositoryInterface $categoryRepository
     * @return ApiCmsLoader
     */
    public function setCategoryRepository(SalesChannelRepositoryInterface $categoryRepository): ApiCmsLoader
    {
        $this->categoryRepository = $categoryRepository;
        return $this;
    }

    /**
     * @return bool
     */
    public function isStoreApiRequest() : bool
    {
        try{
            $userUrl = $this->getRequest()->getUserUrl();
            if(strpos($userUrl, $this->getStoreApiEndpoint()) > -1)
            {
                return true;
            }
        } catch (\Throwable $exception)
        {
            return false;
        }

        return false;
    }

    /**
     * @return string
     */
    public function getStoreApiEndpoint(): string
    {
        return $this->storeApiEndpoint;
    }

    /**
     * @param string $storeApiEndpoint
     * @return ApiCmsLoader
     */
    public function setStoreApiEndpoint(string $storeApiEndpoint): ApiCmsLoader
    {
        $this->storeApiEndpoint = $storeApiEndpoint;
        return $this;
    }


}
