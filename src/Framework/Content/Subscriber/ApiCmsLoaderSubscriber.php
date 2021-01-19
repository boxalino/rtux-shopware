<?php
namespace Boxalino\RealTimeUserExperience\Framework\Content\Subscriber;

use Boxalino\RealTimeUserExperience\Framework\Content\CreateFromTrait;
use Boxalino\RealTimeUserExperience\Framework\Content\Page\ApiCmsLoader;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\RequestInterface;
use Psr\Log\LoggerInterface;
use Shopware\Core\Content\Cms\Aggregate\CmsBlock\CmsBlockEntity;
use Shopware\Core\Content\Cms\Aggregate\CmsSection\CmsSectionEntity;
use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotCollection;
use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Shopware\Core\Content\Cms\CmsPageEntity;
use Shopware\Core\Content\Cms\Events\CmsPageLoadedEvent;
use Shopware\Core\Framework\Struct\Struct;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

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


    public function __construct(
        ApiCmsLoader $apiCmsLoader,
        RequestInterface $requestWrapper,
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->apiCmsLoader = $apiCmsLoader;
        $this->requestWrapper = $requestWrapper;
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
        foreach ($event->getResult() as $element) {
            /** @var CmsSectionEntity $section */
            foreach ($element->getSections() as $sectionNr => $section) {
                /** @var CmsBlockEntity $block */
                foreach ($section->getBlocks() as $block) {
                    try {
                        if ($block->getType() == 'narrative') {
                            $slot = $block->getSlots()->first();
                            $configurations = $slot->getConfig();
                            $apiCmsLoader = $this->getLoader($configurations['widget']['value']);
                            $apiCmsLoader->setCmsConfig($configurations);
                            $narrativeResponse = $apiCmsLoader->load()->getApiResponsePage();
                            $block->getSlots()->first()->setData($narrativeResponse);
                            if ($slot->getConfig()['sidebar']['value']) {
                                $this->updateSidebar($apiCmsLoader, $narrativeResponse, $section, $block);
                            }
                        }
                    } catch (\Throwable $exception) {
                        $this->logger->warning("Boxalino ApiCmsLoaderSubscriber: " . $exception->getMessage() .
                            "\n" . $exception->getTraceAsString()
                        );
                        continue;
                    }
                }
            }
        }
    }

    /**
     * Reset the ApiCms Loader to allow multiple API requests on a CMS page
     *
     * @return ApiCmsLoader
     */
    protected function getLoader($widget) : ApiCmsLoader
    {
        $apiContext = $this->apiCmsLoader->getApiContext();
        $apiContext = $apiContext->setRequestDefinition($this->createEmptyFromObject($apiContext->getApiRequest()));

        $loader = $this->createFromApiLoaderObject($this->apiCmsLoader, ["ApiResponsePage"]);
        $loader->setApiContext($apiContext);

        return $loader;
    }

    /**
     * @param Struct $data
     * @param CmsSectionEntity $section
     * @param CmsBlockEntity $narrativeBlock
     */
    protected function updateSidebar(ApiCmsLoader $loader, Struct $data, CmsSectionEntity $section, CmsBlockEntity $narrativeBlock) : void
    {
        if($section->getType() == 'sidebar' && $narrativeBlock->getSectionPosition() == 'main')
        {
            $slot = $this->createCmsSlotEntity($loader, $narrativeBlock, $data);
            $slots = $this->createCmsSlotCollection($slot);
            $sidebarBlock = $this->createCmsBlockEntity($narrativeBlock, $slots, "sidebar", count($section->getBlocks()));

            $section->getBlocks()->add($sidebarBlock);
        }
    }

    /**
     * @param CmsBlockEntity $blockEntity
     * @param Struct $slotData
     * @return CmsSlotEntity
     */
    protected function createCmsSlotEntity(ApiCmsLoader $loader, CmsBlockEntity $blockEntity, Struct $slotData) : CmsSlotEntity
    {
        /** @var CmsSlotEntity $slot */
        $slot = $this->createFromObject($blockEntity->getSlots()->first(), ['data', '_uniqueIdentifier', '_entityName']);
        $slot->setUniqueIdentifier(uniqid("boxalino_narrative_"));
        $slot->setData($loader->createSectionFrom($slotData, 'left'));

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
        $block = $this->createFromObject($originalBlock, ['data', '_uniqueIdentifier', 'sectionId', 'id', '_entityName']);
        $block->setSectionPosition($sectionPosition);
        $block->setUniqueIdentifier(uniqid("boxalino_{$sectionPosition}_"));
        $block->setSectionId(uniqid());
        $block->setId(uniqid("boxalino_block_"));
        $block->setPosition($position);
        $block->setSlots($slots);

        return $block;
    }

}
