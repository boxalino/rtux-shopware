<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Service\Tracker;

use Boxalino\RealTimeUserExperienceApi\Service\Api\ApiCookieSubscriber;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Util\ConfigurationInterface;
use GuzzleHttp\Psr7\Request;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Boxalino\RealTimeUserExperience\Service\Util\Configuration;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use GuzzleHttp\Client;

/**
 * Class RtuxApiHandler
 * Prepares the tracker information used on page
 * Caches the tracker configurations
 *
 * @package Boxalino\RealTimeUserExperience\Service\Tracker
 */
class RtuxApiHandler
{

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
     * @return RtuxApi
     */
    public function getRtuxApi(SalesChannelContext $salesChannelContext) : RtuxApi
    {
        $trackerConfiguration = $this->getConfigurationFromCache($salesChannelContext);
        $tracker = new RtuxApi($trackerConfiguration);
        $tracker->setTrackerUrl($this->getTrackerUrl($tracker->isDev(), $tracker->isTest()))
            ->setRtiUrl($this->getRtiUrl($tracker->isDev(), $tracker->isTest()))
            ->setCustomerContext($this->getEncodedCustomer($salesChannelContext));

        return $tracker;
    }

    /**
     * @param string $event
     * @param array $params
     * @param SalesChannelContext $salesChannelContext
     * @return void
     */
    public function track(string $event, array $params, SalesChannelContext $salesChannelContext) : void
    {
        $tracker = $this->getRtuxApi($salesChannelContext);
        if(!$tracker->isActive())
        {
            return;
        }

        $params['_a'] = $tracker->getAccount();
        $params['_ev'] = $event;
        $params['_t'] = round(microtime(true) * 1000);
        $params['_ln'] = $this->getLanguageCodeFromCache($salesChannelContext->getSalesChannel()->getLanguageId());
        if(!isset($params["_bxs"]))
        {
            $params['_bxs'] = $_COOKIE[ApiCookieSubscriber::BOXALINO_API_COOKIE_SESSION];
        }
        if(!isset($params["_bxv"]))
        {
            $params['_bxv'] = $_COOKIE[ApiCookieSubscriber::BOXALINO_API_COOKIE_VISITOR];
        }

        try {
            $profileId = $params['_bxv'];
            if(is_null($profileId))
            {
                $profileId = Uuid::uuid4()->toString();
            }
            $this->trackClient->send(
                new Request(
                    'POST',
                    $this->getServerUrl($profileId, $tracker->isDev()),
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
            "serverUrl" =>  $configurations['apiUrl'] ?? null,
            "apiServerKey" => $configurations['apiServerKey'] ?? null,
            "apiSecret" => $configurations['apiSecret'] ?? null,
            "gdprCemv" => $configurations['gdprCemv'] ?? null,
            "gdprCems" => $configurations['gdprCems'] ?? null,
            "isActive" => (bool) $configurations['trackerActive'] ?? false,
            "isRti" => (bool) $configurations['rtiActive'] ?? false,
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
     * @param bool $isTest
     * @return string
     */
    public function getTrackerUrl(bool $isDev=false, bool $isTest=false) : string
    {
        if($isDev || $isTest)
        {
            return ConfigurationInterface::BOXALINO_API_TRACKING_STAGE;
        }

        return ConfigurationInterface::BOXALINO_API_TRACKING_PRODUCTION;
    }

    /**
     * @param bool $isDev
     * @param bool $isTest
     * @return string
     */
    public function getRtiUrl(bool $isDev=false, bool $isTest=false) : string
    {
        if($isDev || $isTest)
        {
            return ConfigurationInterface::BOXALINO_API_RTI_STAGE;
        }

        return ConfigurationInterface::BOXALINO_API_RTI_PRODUCTION;
    }

    /**
     * @param bool $isDev
     * @param string $session
     * @return string
     */
    public function getServerUrl(string $session, bool $isDev=false) : string
    {
        if($isDev)
        {
            return ConfigurationInterface::BOXALINO_API_SERVER_STAGE . "?_bxv=" . $session;
        }

        return ConfigurationInterface::BOXALINO_API_SERVER_PRODUCTION . "?_bxv=" . $session;
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
