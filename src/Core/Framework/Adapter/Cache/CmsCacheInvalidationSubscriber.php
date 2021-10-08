<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Core\Framework\Adapter\Cache;

use Boxalino\RealTimeUserExperience\Core\Content\Cms\Event\ApiCmsEvent;
use Shopware\Core\Content\Category\SalesChannel\CachedCategoryRoute;
use Shopware\Core\Framework\Adapter\Cache\CacheInvalidator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Cache invalidator for Shopware6.4 integration
 * Will invalidate the cache on every CMS route that is loading Boxalino API content
 *
 * To be used as an example by client`s integration teams in order to extend functionalities
 */
class CmsCacheInvalidationSubscriber implements EventSubscriberInterface
{
    private CacheInvalidator $logger;

    public function __construct(
        CacheInvalidator $logger
    ) {
        $this->logger = $logger;
    }

    public static function getSubscribedEvents()
    {
        return [
            ApiCmsEvent::class => 'invalidateCategoryRouteByCategoryId'
        ];
    }

    /**
     * @param ApiCmsEvent $event
     */
    public function invalidateCategoryRouteByCategoryId(ApiCmsEvent $event): void
    {
        // invalidates the category route cache when a category got the content loaded via Boxalino API
        $this->logger->invalidate(
            [CachedCategoryRoute::buildName($event->getId())]
        );
    }


}
