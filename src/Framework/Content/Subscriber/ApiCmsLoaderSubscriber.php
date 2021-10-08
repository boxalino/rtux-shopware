<?php
namespace Boxalino\RealTimeUserExperience\Framework\Content\Subscriber;

use Boxalino\RealTimeUserExperience\Core\Content\Cms\Event\ApiCmsEvent;
use Boxalino\RealTimeUserExperience\Framework\Content\CreateFromTrait;
use Boxalino\RealTimeUserExperience\Framework\Content\Page\ApiCmsLoader;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\RequestDefinitionInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\RequestInterface;
use Psr\Log\LoggerInterface;
use Shopware\Core\Content\Cms\Aggregate\CmsBlock\CmsBlockEntity;
use Shopware\Core\Content\Cms\Aggregate\CmsSection\CmsSectionEntity;
use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotCollection;
use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Shopware\Core\Content\Cms\CmsPageEntity;
use Shopware\Core\Content\Cms\Events\CmsPageLoadedEvent;
use Shopware\Core\Framework\Routing\KernelListenerPriorities;
use Shopware\Core\Framework\Struct\Struct;
use Shopware\Core\PlatformRequest;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Class ApiCmsLoaderSubscriber
 * Adds CMS content to the Shopware Experience pages
 * Can be extended in order to alter the provided logic/behavior
 *
 * If the Boxalino Narrative CMS block is being configured for a "sidebar layout",
 * the root block with section=sidebar will be appended to the sidebar section of the page
 *
 * The class can be used as a base to be extended & customized
 * (ex: adding segments of the narrative response to other sections of the Shopware Experience - top, bottom)
 *
 * @package Boxalino\RealTimeUserExperience\Framework\Content\Subscriber
 */
class ApiCmsLoaderSubscriber implements EventSubscriberInterface
{
    use CreateFromTrait;

    /**
     * @var ApiCmsLoader
     */
    private $apiCmsLoader;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var RequestInterface
     */
    private $requestWrapper;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;


    public function __construct(
        ApiCmsLoader $apiCmsLoader,
        RequestInterface $requestWrapper,
        EventDispatcherInterface $dispatcher,
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->apiCmsLoader = $apiCmsLoader;
        $this->requestWrapper = $requestWrapper;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @return array|string[]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            CmsPageLoadedEvent::class => 'addApiCmsContent'
        ];
    }


    /**
     * Adds API CMS content as configured
     * Mirrors the Shopware6 structure of a Shopping Experience component
     *
     * @param CmsPageLoadedEvent $event
     */
    public function addApiCmsContent(CmsPageLoadedEvent $event): void
    {
        $this->requestWrapper->setRequest($event->getRequest());
        $this->apiCmsLoader->setRequest($this->requestWrapper);
        $this->apiCmsLoader->setSalesChannelContext($event->getSalesChannelContext());

        /** @var CmsPageEntity $element */
        foreach ($event->getResult() as $element)
        {
            /** @var CmsSectionEntity $section */
            foreach ($element->getSections() as $sectionNr => $section)
            {
                /** @var CmsBlockEntity $block */
                foreach ($section->getBlocks() as $block)
                {
                    try {
                        if ($block->getType() == 'narrative')
                        {
                            //avoid making duplicate requests from dynamically created blocks
                            if(strpos($block->getId(), "boxalino_block_") === false)
                            {
                                $slot = $block->getSlots()->first();
                                $configurations = $slot->getConfig();
                                $apiCmsLoader = $this->getLoader($configurations['widget']['value']);
                                $apiCmsLoader->setCmsConfig($configurations);
                                $apiCmsLoader->load();
                                $this->addApiResponseToSections($apiCmsLoader, $element, $section, $block, $configurations);
                                $block->getSlots()->first()->setData($apiCmsLoader->getApiResponsePage());

                                $this->dispatchEvent();
                            }
                        }
                    } catch (\Throwable $exception) {
                        $this->logger->debug("Boxalino ApiCmsLoaderSubscriber: " . $exception->getMessage() .
                            "\n" . $exception->getTraceAsString()
                        );
                        continue;
                    }
                }
            }
        }
    }

    /**
     * Dispatches an event in order to trigger the CMS cache invalidation for the API content
     */
    protected function dispatchEvent() : void
    {
        $event = new ApiCmsEvent(
            $this->requestWrapper->getParam("navigationId"),
            $this->apiCmsLoader->getSalesChannelContext()->getContext()
        );

        $this->dispatcher->dispatch($event);
    }

    /**
     * Reset the ApiCms Loader to allow multiple API requests on a CMS page
     *
     * @return ApiCmsLoader
     */
    protected function getLoader($widget) : ApiCmsLoader
    {
        $apiContext = $this->apiCmsLoader->getApiContext();

        /** @var  RequestDefinitionInterface $requestDefinition */
        $requestDefinition = $this->createEmptyFromObject($apiContext->getApiRequest());
        $apiContext = $apiContext->setRequestDefinition($requestDefinition);

        $loader = $this->createFromApiLoaderObject($this->apiCmsLoader, ["ApiResponsePage"]);
        $loader->setApiContext($apiContext);

        return $loader;
    }

    /**
     * @param $narrativeResponse
     */
    protected function addApiResponseToSections(
        ApiCmsLoader $apiCmsLoader,
        CmsPageEntity $element,
        CmsSectionEntity $section,
        CmsBlockEntity $narrativeBlock,
        array $configurations
    ){
        if ($configurations['sidebar']['value'])
        {
            if($section->getType() == 'sidebar' && $narrativeBlock->getSectionPosition() == 'main')
            {
                $this->addSlotToSectionByPosition($apiCmsLoader, $narrativeBlock, $section, "left", "sidebar");
            }
        }

        $sectionPosition = $section->getPosition(); $previousSection=null; $nextSection=null;
        if($sectionPosition)
        {
            foreach($element->getSections() as $sequenceSection)
            {
                if($sequenceSection->getPosition() == $sectionPosition-1)
                {
                    /** @var CmsSectionEntity $previousSection */
                    $previousSection = $sequenceSection;
                    continue;
                }

                if($sequenceSection->getPosition() == $sectionPosition+1)
                {
                    /** @var CmsSectionEntity $nextSection */
                    $nextSection = $sequenceSection;
                    continue;
                }
            }
        }

        if($previousSection)
        {
            $this->addSlotToSectionByPosition($apiCmsLoader, $narrativeBlock, $previousSection, "top", "default");
        }

        if($nextSection)
        {
            $this->addSlotToSectionByPosition($apiCmsLoader, $narrativeBlock, $nextSection, "bottom", "default");
        }
    }

    /**
     * @param ApiCmsLoader $apiCmsLoader
     * @param CmsBlockEntity $narrativeBlock
     * @param CmsSectionEntity $section
     * @param string $position
     * @param string $sectionPosition
     */
    protected function addSlotToSectionByPosition(ApiCmsLoader $apiCmsLoader, CmsBlockEntity $narrativeBlock, CmsSectionEntity $section, string $position, string $sectionPosition = "default")
    {
        $slot = $this->createCmsSlotEntity($apiCmsLoader, $narrativeBlock, $position);
        $slots = $this->createCmsSlotCollection($slot);
        $newBlock = $this->createCmsBlockEntity($narrativeBlock, $slots, $sectionPosition, count($section->getBlocks()));

        $section->getBlocks()->add($newBlock);

        return;
    }

    /**
     * @param CmsBlockEntity $blockEntity
     * @param Struct $slotData
     * @return CmsSlotEntity
     */
    protected function createCmsSlotEntity(ApiCmsLoader $loader, CmsBlockEntity $blockEntity, string $position) : CmsSlotEntity
    {
        /** @var CmsSlotEntity $slot */
        $slot = $this->createFromStructObject($blockEntity->getSlots()->first(), ['data', '_uniqueIdentifier', '_entityName']);
        $slot->setUniqueIdentifier(uniqid("boxalino_narrative_"));
        $slotData = $loader->getApiResponsePage();
        $slot->setData($loader->createSectionFrom($slotData, $position));

        try{
            $setterFunction = "set".ucfirst($position);
            $loader->getApiResponsePage()->$setterFunction(new \ArrayIterator());
        } catch (\Throwable $exception)
        {
            $this->logger->debug("Boxalino ApiCmsLoaderSubscriber content removed from $position: " . $exception->getMessage() .
                "\n" . $exception->getTraceAsString()
            );
        }

        return $slot;
    }

    /**
     * @param CmsSlotEntity $slot
     * @return CmsSlotCollection
     */
    protected function createCmsSlotCollection(CmsSlotEntity $slot) : CmsSlotCollection
    {
        $slotsCollection = new CmsSlotCollection();
        $slotsCollection->add($slot);

        return $slotsCollection;
    }

    /**
     * @param CmsBlockEntity $originalBlock
     * @param CmsSlotCollection $slots
     * @param string $sectionPosition
     * @param int $position
     * @return CmsBlockEntity
     */
    protected function createCmsBlockEntity(CmsBlockEntity $originalBlock, CmsSlotCollection $slots, string $sectionPosition, int $position = 0) : CmsBlockEntity
    {
        /** @var CmsBlockEntity $block */
        $block = $this->createFromStructObject($originalBlock, ['data', '_uniqueIdentifier', 'sectionId', 'id', '_entityName']);
        $block->setSectionPosition($sectionPosition);
        $block->setUniqueIdentifier(uniqid("boxalino_{$sectionPosition}_"));
        $block->setSectionId(uniqid());
        $block->setId(uniqid("boxalino_block_"));
        $block->setPosition($position);
        $block->setSlots($slots);

        return $block;
    }


}
