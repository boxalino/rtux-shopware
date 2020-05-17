<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Framework\Content\Page;

use Boxalino\RealTimeUserExperience\Framework\Content\CreateFromTrait;
use Boxalino\RealTimeUserExperience\Framework\Content\Listing\ApiCmsModel;
use Boxalino\RealTimeUserExperience\Service\Api\ApiCallServiceInterface;
use Boxalino\RealTimeUserExperience\Service\Api\Response\Accessor\Block;
use Boxalino\RealTimeUserExperience\Service\ErrorHandler\UndefinedPropertyError;
use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Shopware\Core\Framework\Struct\Struct;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ApiCmsLoader
 * Sample based on a familiar ShopwarePageLoader component
 *
 * @package Boxalino\RealTimeUserExperience\Service\Api\Content\Page
 */
class ApiCmsLoader extends ApiLoader
{
    use CreateFromTrait;

    /**
     * Loads the content of an API Response page
     */
    public function load(Request $request, SalesChannelContext $salesChannelContext, CmsSlotEntity $slot): Struct
    {
        $this->addProperties($slot);
        $this->call($request, $salesChannelContext);

        if($this->apiCallService->isFallback())
        {
            throw new \Exception($this->apiCallService->getFallbackMessage());
        }

        $content = new ApiCmsModel();
        $content->setBlocks($this->apiCallService->getApiResponse()->getBlocks())
            ->setLeft($this->apiCallService->getApiResponse()->getLeft())
            ->setTop($this->apiCallService->getApiResponse()->getTop())
            ->setBottom($this->apiCallService->getApiResponse()->getBottom())
            ->setRight($this->apiCallService->getApiResponse()->getRight())
            ->setRequestId($this->apiCallService->getApiResponse()->getRequestId())
            ->setGroupBy($this->getGroupBy())
            ->setVariantUuid($this->getVariantUuid())
            ->setNavigationId($request->get("navigationId", $salesChannelContext->getSalesChannel()->getNavigationCategoryId()))
            ->setTotalHitCount($this->apiCallService->getApiResponse()->getHitCount());

        return $content;
    }

    /**
     * Adds properties to the CmsContextAbstract
     * @param CmsSlotEntity $slot
     */
    protected function addProperties(CmsSlotEntity $slot)
    {
        foreach($slot->getConfig() as $key => $configuration)
        {
            $value = $configuration['value'];
            if($key == 'widget')
            {
                $this->apiContextInterface->setWidget($value);
                continue;
            }
            if($key == 'hitCount')
            {
                $this->apiContextInterface->setHitCount((int) $value);
                continue;
            }
            if($key == 'groupBy')
            {
                $this->apiContextInterface->setGroupBy($value);
                continue;
            }

            if(!is_null($value) && !empty($value))
            {
                $this->apiContextInterface->set($key, $configuration['value']);
            }
        }
    }

    /**
     * Replicates the narrative content in order to generate the top/bottom/right/left slots
     *
     * @param Struct $apiCmsModel
     * @return Struct
     */
    public function createSectionFrom(Struct $apiCmsModel, string $position) : Struct
    {
        if(in_array($position, $this->apiCallService->getApiResponse()->getResponseSegments()) && $apiCmsModel instanceof ApiCmsModel)
        {
            /** @var ApiCmsModel $segmentNarrativeBlock */
            $segmentNarrativeBlock = $this->createFromObject($apiCmsModel, ['blocks', $position]);
            $getterFunction = "get".ucfirst($position);
            $setterFunction = "set".ucfirst($position);
            $segmentNarrativeBlock->setBlocks($apiCmsModel->$getterFunction());
            $segmentNarrativeBlock->$setterFunction(new \ArrayIterator());

            return $segmentNarrativeBlock;
        }

        return new ApiCmsModel();
    }

    /**
     * This function can be used to access parts of the response\
     * and isolate them in different sections
     * ex: a single narrative request on a page with 3 sections
     * @param string $property
     * @param string $value
     * @param string $segment
     * @return \ArrayIterator
     */
    public function getBlocksByPropertyValue(string $property, string $value, string $segment = 'blocks') : \ArrayIterator
    {
        $newSectionBlocks = new \ArrayIterator();
        $responseSegmentGetter = "get" . ucfirst($segment);
        $blocks = $this->apiCallService->getApiResponse()->$responseSegmentGetter();
        /** @var Block $block */
        foreach($blocks as $index => $block)
        {
            try{
                $functionName = "get".ucfirst($property);
                $propertyValue = $block->$functionName();
                if($propertyValue[0] == $value)
                {
                    $newSectionBlocks->append($block);
                    $blocks->offsetUnset($index);
                }
            } catch (UndefinedPropertyError $exception)
            {
                continue;
            }
        }

        return $newSectionBlocks;
    }

}
