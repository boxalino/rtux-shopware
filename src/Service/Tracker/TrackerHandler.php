<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Service\Tracker;

use Boxalino\RealTimeUserExperience\Service\Tracker\ApiTracker;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Boxalino\RealTimeUserExperience\Service\Util\Configuration;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;

/**
 * Class TrackerHandler
 * Prepares the tracker information used on page
 * Caches the tracker configurations
 *
 * @package Boxalino\RealTimeUserExperience\Service\Tracker
 */
class TrackerHandler
{
    public const BOXALINO_API_TRACKING_PRODUCTION="//track.bx-cloud.com/static/bav2.min.js";
    public const BOXALINO_API_TRACKING_STAGE="//r-st.bx-cloud.com/static/bav2.min.js";
    public const RTUX_API_TRACKER_CONFIGURATION_CACHE_KEY = 'rtux_api_tracker_configuration';

    /**
     * @var Configuration
     */
    private $rtuxConfiguration;

    /**
     * @var TagAwareAdapterInterface
     */
    private $cache;


    public function __construct(
        Configuration $rtuxConfiguration,
        TagAwareAdapterInterface $cache
    ) {
        $this->rtuxConfiguration = $rtuxConfiguration;
        $this->cache = $cache;
    }

    /**
     * @param SalesChannelContext $salesChannelContext
     * @return ApiTracker
     */
    public function getTracker(SalesChannelContext $salesChannelContext) : Tracker
    {
        $trackerConfiguration = $this->getConfigurationFromCache($salesChannelContext);
        $tracker = new Tracker($trackerConfiguration);
        $tracker->setUrl($this->getTrackerUrl($tracker->isDev()))
            ->setCustomerContext($this->getEncodedCustomer($salesChannelContext));

        return $tracker;
    }

    /**
     * @param SalesChannelContext $salesChannelContext
     * @return array
     * @throws \Psr\Cache\CacheException
     * @throws \Psr\Cache\InvalidArgumentException
     */
    protected function getConfigurationFromCache(SalesChannelContext $salesChannelContext) : array
    {
        $channelId = $salesChannelContext->getSalesChannel()->getId();
        $item = $this->cache->getItem(self::RTUX_API_TRACKER_CONFIGURATION_CACHE_KEY . "_" . $channelId);
        if ($item->isHit() && $item->get()) {
            return $item->get();
        }

        $configurations = $this->rtuxConfiguration->getPluginConfigByChannelId($channelId);
        $trackerConfigurations = [
            "account" => $configurations['account'] ?? null,
            "apiKey" => $configurations['apiKey'] ?? null,
            "apiSecret" => $configurations['apiSecret'] ?? null,
            "isActive" => (bool) $configurations['trackerActive'] ?? false,
            "isTest" => (bool) $configurations['test'] ?? false,
            "isDev" => (bool)$configurations['devIndex'] ?? false
        ];

        $item->set($trackerConfigurations);
        if ($item instanceof ItemInterface) {
            $item->tag(["rtux","tracker","boxalino"]);
        }
        $this->cache->save($item);

        return $trackerConfigurations;
    }

    /**
     * @param bool $isDev
     * @return string
     */
    public function getTrackerUrl(bool $isDev=false) : string
    {
        if($isDev)
        {
            return self::BOXALINO_API_TRACKING_STAGE;
        }

        return self::BOXALINO_API_TRACKING_PRODUCTION;
    }

    /**
     * @param SalesChannelContext $salesChannelContext
     * @return string|null
     */
    public function getEncodedCustomer(SalesChannelContext $salesChannelContext) : ?string
    {
        if(is_null($salesChannelContext->getCustomer()))
        {
            return null;
        }

        return base64_encode($salesChannelContext->getCustomer()->getId());
    }


}
