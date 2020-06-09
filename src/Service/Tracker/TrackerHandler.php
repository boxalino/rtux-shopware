<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Service\Tracker;

use Boxalino\RealTimeUserExperience\Service\Tracker\ApiTracker;
use GuzzleHttp\Psr7\Request;
use Psr\Log\LoggerInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Boxalino\RealTimeUserExperience\Service\Util\Configuration;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use GuzzleHttp\Client;

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
    public const BOXALINO_API_SERVER_PRODUCTION="//track.bx-cloud.com/track/v2";
    public const BOXALINO_API_SERVER_STAGE="//r-st.bx-cloud.com/track/v2";
    public const RTUX_API_TRACKER_CONFIGURATION_CACHE_KEY = 'rtux_api_tracker_configuration';
    public const RTUX_API_TRACKER_LANGUAGE_CACHE_KEY = 'rtux_api_tracker_language';

    /**
     * @var Configuration
     */
    private $rtuxConfiguration;

    /**
     * @var TagAwareAdapterInterface
     */
    private $cache;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Client
     */
    private $trackClient;

    public function __construct(
        Configuration $rtuxConfiguration,
        TagAwareAdapterInterface $cache,
        LoggerInterface $logger
    ) {
        $this->rtuxConfiguration = $rtuxConfiguration;
        $this->trackClient = new Client();
        $this->cache = $cache;
        $this->logger = $logger;
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
     * @param string $event
     * @param array $params
     * @param SalesChannelContext $salesChannelContext
     * @return mixed
     */
    public function track(string $event, array $params, SalesChannelContext $salesChannelContext)
    {
        $tracker = $this->getTracker($salesChannelContext);

        $params['_a'] = $tracker->getAccount();
        $params['_ev'] = $event;
        $params['_t'] = round(microtime(true) * 1000);
        $params['_ln'] = $this->getLanguageCodeFromCache($salesChannelContext->getSalesChannel()->getLanguageId());
        $params['_bxs'] = $_COOKIE["cems"];
        $params['_bxv'] = $_COOKIE["cemv"];

        try {
            $this->trackClient->send(
                new Request(
                    'POST',
                    $this->getServerUrl($tracker->isDev(), $params["_bxv"]),
                    [
                        'Content-Type' => 'text/plain'
                    ],
                    json_encode(['events'=>[$params]])
                )
            );
        } catch (\Throwable $exception)
        {
            $this->logger->warning("Boxalino API Tracker $event not delivered: " . $exception->getMessage());
        }
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
     * @param string $languageId
     * @return string
     * @throws \Psr\Cache\CacheException
     * @throws \Psr\Cache\InvalidArgumentException
     */
    protected function getLanguageCodeFromCache(string $languageId) : string
    {
        $item = $this->cache->getItem(self::RTUX_API_TRACKER_LANGUAGE_CACHE_KEY . "_" . $languageId);
        if ($item->isHit() && $item->get()) {
            return $item->get();
        }

        $languageCode = $this->rtuxConfiguration->getLanguageCode($languageId);
        $item->set($languageCode);
        if ($item instanceof ItemInterface) {
            $item->tag(["rtux","language","code"]);
        }
        $this->cache->save($item);

        return $languageCode;
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
     * @param bool $isDev
     * @param string $session
     * @return string
     */
    public function getServerUrl(bool $isDev=false, string $session) : string
    {
        if($isDev)
        {
            return self::BOXALINO_API_SERVER_STAGE . "?_bxv=" . $session;
        }

        return self::BOXALINO_API_SERVER_PRODUCTION . "?_bxv=" . $session;
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
