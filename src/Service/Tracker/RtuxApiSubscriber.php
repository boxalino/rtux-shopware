<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Service\Tracker;

use Psr\Log\LoggerInterface;
use Shopware\Storefront\Event\StorefrontRenderEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class RtuxApiSubscriber
 * Sets the API tracker details on the page
 *
 * @package Boxalino\RealTimeUserExperience\Service\Tracker
 */
class RtuxApiSubscriber implements EventSubscriberInterface
{

    /**
     * @var RtuxApiHandler
     */
    protected $rtuxApiHandler;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(RtuxApiHandler $rtuxApiHandler, LoggerInterface $logger)
    {
        $this->rtuxApiHandler = $rtuxApiHandler;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents()
    {
        return [
            StorefrontRenderEvent::class => [
                ['addRtuxApi']
            ],
        ];
    }

    /**
     * @param StorefrontRenderEvent $event
     */
    public function addRtuxApi(StorefrontRenderEvent $event): void
    {
        $context = $event->getSalesChannelContext();
        try{
            if($context->hasExtension("rtuxApi"))
            {
                return;
            }
            $context->addExtension("rtuxApi", $this->rtuxApiHandler->getRtuxApi($context));
        } catch (\Throwable $exception)
        {
            $this->logger->warning($exception->getMessage());
        }
    }

}
