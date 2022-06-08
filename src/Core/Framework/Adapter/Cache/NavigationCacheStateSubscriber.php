<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Core\Framework\Adapter\Cache;

use Shopware\Core\Framework\Routing\KernelListenerPriorities;
use Shopware\Core\PlatformRequest;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Util\ConfigurationInterface;

abstract class NavigationCacheStateSubscriber implements EventSubscriberInterface
{

    public const STATE_BOXALINO_API = 'cms-block-navigation-boxalino-api';

    /**
     * @var ConfigurationInterface
     */
    protected $apiConfiguration;

    public function __construct(ConfigurationInterface $configuration)
    {
        $this->apiConfiguration = $configuration;
    }

    /**
     * @return array|string[]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => [
                ['addApiStateOnNavigation', KernelListenerPriorities::KERNEL_CONTROLLER_EVENT_SCOPE_VALIDATE_POST],
            ],
        ];
    }

    /**
     * It will apply to all CategoryRoute
     * Add the cache state constant to the list of shopware.cache.invalidation.category_route to avoid caching
     *
     * @param ControllerEvent $event
     */
    public function addApiStateOnNavigation(ControllerEvent $event): void
    {
        if ($this->checkIfAddState($event))
        {
            $request = $event->getRequest();

            /** @var SalesChannelContext $context */
            $context = $request->attributes->get(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_CONTEXT_OBJECT);
            if($this->apiConfiguration->setContextId($context->getSalesChannelId())->isApiEnabled())
            {
                $context->addState($this->getCacheState());
            }
        }
    }


    /**
     * @param ControllerEvent $event
     * @return bool
     */
    abstract function checkIfAddState(ControllerEvent $event) : bool;

    /**
     * @return string
     */
    abstract function getCacheState() : string;


}
