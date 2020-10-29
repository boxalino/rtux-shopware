<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Framework\Content\Page;

use Boxalino\RealTimeUserExperience\Framework\Content\CreateFromTrait;
use Boxalino\RealTimeUserExperience\Framework\Content\Listing\ApiCmsModel;
use Boxalino\RealTimeUserExperienceApi\Framework\Content\Listing\ApiCmsModelInterface;
use Boxalino\RealTimeUserExperienceApi\Framework\Content\Page\ApiBaseLoaderAbstract;
use Boxalino\RealTimeUserExperienceApi\Framework\Content\Page\ApiLoaderInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\RequestInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Response\ApiResponseViewInterface;
use Shopware\Core\Framework\Struct\Struct;

/**
 * Class ApiCmsLoader
 * Sample based on a familiar ShopwarePageLoader component
 *
 * @package Boxalino\RealTimeUserExperience\Framework\Content\Page
 */
class ApiCmsLoader extends ApiBaseLoaderAbstract
{
    use CreateFromTrait;
    use ApiLoaderTrait;

    /**
     * @var array
     */
    protected $cmsConfig = [];

    /**
     * Loads the content of an API Response page
     */
    public function load() : ApiLoaderInterface
    {
        $this->addProperties();
        parent::load();
        $this->getApiResponsePage()->setNavigationId($this->getNavigationId($this->getRequest()));
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
        foreach($config as $key=>$configuration)
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
     * @return Struct
     */
    public function createSectionFrom(Struct $apiCmsModel, string $position) : ?Struct
    {
        if(in_array($position, $this->apiCallService->getApiResponse()->getResponseSegments()) && $apiCmsModel instanceof ApiCmsModelInterface)
        {
            /** @var ApiCmsModelInterface | Struct $segmentNarrativeBlock */
            $segmentNarrativeBlock = $this->createFromObject($apiCmsModel, ['blocks', $position]);
            $getterFunction = "get".ucfirst($position);
            $setterFunction = "set".ucfirst($position);
            $segmentNarrativeBlock->setBlocks($apiCmsModel->$getterFunction());
            $segmentNarrativeBlock->$setterFunction(new \ArrayIterator());

            return $segmentNarrativeBlock;
        }

        return $this->getApiResponsePage();
    }

}
