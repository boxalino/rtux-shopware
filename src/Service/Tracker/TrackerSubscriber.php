<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Service\Tracker;

use Boxalino\RealTimeUserExperience\Service\Tracker\Util\Configuration;
use Psr\Log\LoggerInterface;
use Shopware\Storefront\Event\StorefrontRenderEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class TrackerSubscriber
 * Sets the API tracker details on the page
 *
 * @package Boxalino\RealTimeUserExperience\Service\Tracker
 */
class TrackerSubscriber implements EventSubscriberInterface
{

    /**
     * @var TrackerHandler
     */
    protected $trackerHandler;

    public function __construct(TrackerHandler $trackerHandler)
    {
        $this->trackerHandler = $trackerHandler;
    }

    public static function getSubscribedEvents()
    {
        return [
            StorefrontRenderEvent::class => [
                ['addRtuxApiTracker']
            ],
        ];
    }

    /**
     * @param StorefrontRenderEvent $event
     */
    public function addRtuxApiTracker(StorefrontRenderEvent $event): void
    {
        $context = $event->getSalesChannelContext();
        try{
            if($context->hasExtension("rtuxApiTracker"))
            {
                $this->logger->info("has extension");
                return;
            }
            $context->addExtension("rtuxApiTracker", $this->trackerHandler->getTracker($context));
        } catch (\Throwable $exception)
        {
            $this->logger->warning($exception->getMessage());
        }
    }

}
