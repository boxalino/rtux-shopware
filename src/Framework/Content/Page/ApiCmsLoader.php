<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Framework\Content\Page;

use Boxalino\RealTimeUserExperience\Framework\Content\Listing\ApiCmsModel;
use Boxalino\RealTimeUserExperienceApi\Framework\Content\Listing\ApiCmsModelInterface;
use Boxalino\RealTimeUserExperienceApi\Framework\Content\Page\ApiCmsLoaderAbstract;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\RequestInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Response\ApiResponseViewInterface;
use Shopware\Core\Framework\Struct\Struct;

/**
 * Class ApiCmsLoader
 * Sample based on a familiar ShopwarePageLoader component
 *
 * @package Boxalino\RealTimeUserExperience\Framework\Content\Page
 */
class ApiCmsLoader extends ApiCmsLoaderAbstract
{
    use ApiLoaderTrait;

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
