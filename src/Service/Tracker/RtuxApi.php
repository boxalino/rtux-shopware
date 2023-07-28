<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Service\Tracker;

use Boxalino\RealTimeUserExperienceApi\Service\Api\Util\ConfigurationInterface;
use Shopware\Core\Framework\Struct\Struct;

/**
 * @package Boxalino\RealTimeUserExperience\Service\Tracker
 */
class RtuxApi extends Struct
{

    /**
     * @var string || null
     */
    protected $account;

    /**
     * @var string | null
     */
    protected $apiKey;

    /**
     * @var string | null
     */
    protected $apiServerKey;

    /**
     * @var string | null
     */
    protected $apiSecret;

    /**
     * @var bool
     */
    protected $isActive = false;

    /**
     * @var bool
     */
    protected $isRti = false;

    /**
     * @var bool
     */
    protected $isGdpr = false;

    /**
     * @var string | null
     */
    protected $gdprCemv;

    /**
     * @var string | null
     */
    protected $gdprCems;

    /**
     * @var bool
     */
    protected $isTest = false;

    /**
     * @var bool
     */
    protected $isDev = false;

    /**
     * @var string
     */
    protected $trackerUrl;

    /**
     * @var string
     */
    protected $rtiUrl;

    /**
     * @var string
     */
    protected $serverUrl;

    /**
     * @var string | null
     */
    protected $customerContext;
    
    /** @var string | null */
    protected $masterNavigationId = null;

    /**
     * Tracker constructor.
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        foreach($data as $key=>$value)
        {
            $this->$key = $value;
        }
    }

    /**
     * @return string
     */
    public function getAccount(): ?string
    {
        return $this->account;
    }

    /**
     * @param string $account
     * @return self
     */
    public function setAccount(string $account): self
    {
        $this->account = $account;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    /**
     * @param string|null $apiKey
     * @return self
     */
    public function setApiKey(?string $apiKey): self
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getApiServerKey(): ?string
    {
        return $this->apiServerKey;
    }

    /**
     * @param string|null $serverKey
     * @return self
     */
    public function setApiServerKey(?string $serverKey): self
    {
        $this->apiServerKey = $serverKey;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getApiSecret(): ?string
    {
        return $this->apiSecret;
    }

    /**
     * @param string|null $apiSecret
     * @return self
     */
    public function setApiSecret(?string $apiSecret): self
    {
        $this->apiSecret = $apiSecret;
        return $this;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @param bool $isActive
     * @return self
     */
    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }

    /**
     * @return bool
     */
    public function isRti(): bool
    {
        return $this->isRti;
    }

    /**
     * @param bool $isRti
     * @return self
     */
    public function setIsRti(bool $isRti): self
    {
        $this->isRti = $isRti;
        return $this;
    }

    /**
     * @return bool
     */
    public function isGdpr(): bool
    {
        return $this->isGdpr;
    }

    /**
     * @param bool $isGdpr
     * @return self
     */
    public function setIsGdpr(bool $isGdpr): self
    {
        $this->isGdpr = $isGdpr;
        return $this;
    }

    /**
     * @return bool
     */
    public function isTest(): bool
    {
        return $this->isTest;
    }

    /**
     * @param bool $isTest
     * @return self
     */
    public function setIsTest(bool $isTest): self
    {
        $this->isTest = $isTest;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDev(): bool
    {
        return $this->isDev;
    }

    /**
     * @param bool $isDev
     * @return self
     */
    public function setIsDev(bool $isDev): self
    {
        $this->isDev = $isDev;
        return $this;
    }

    /**
     * @return string
     */
    public function getTrackerUrl(): string
    {
        return $this->trackerUrl;
    }

    /**
     * @param string | null $url
     * @return self
     */
    public function setTrackerUrl(?string $url): self
    {
        $this->trackerUrl = $url;
        return $this;
    }

    /**
     * @return string
     */
    public function getRtiUrl(): string
    {
        return $this->rtiUrl;
    }

    /**
     * @param string | null $url
     * @return self
     */
    public function setRtiUrl(?string $url): self
    {
        $this->rtiUrl = $url;
        return $this;
    }

    /**
     * @return string
     */
    public function getServerUrl(): string
    {
        if(is_null($this->serverUrl))
        {
            $this->serverUrl = str_replace("%%account%%", $this->getAccount(), $this->_getEndpoint());
        }

        return $this->serverUrl;
    }

    /**
     * On FE requests, only the alternative domain is used
     * @return string
     */
    protected function _getEndpoint() : string
    {
        if($this->isDev() || $this->isTest())
        {
            return str_replace("%%domain%%", ConfigurationInterface::RTUX_API_DOMAIN_ALTERNATIVE, ConfigurationInterface::RTUX_API_ENDPOINT_STAGE);
        }

        return str_replace("%%domain%%", ConfigurationInterface::RTUX_API_DOMAIN_ALTERNATIVE, ConfigurationInterface::RTUX_API_ENDPOINT_PRODUCTION);
    }

    /**
     * @param string | null $serverUrl
     * @return self
     */
    public function setServerUrl(?string $serverUrl): self
    {
        $this->serverUrl = $serverUrl;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getGdprCemv(): ?string
    {
        return $this->gdprCemv;
    }

    /**
     * @param string|null $gdprCemv
     */
    public function setGdprCemv(?string $gdprCemv): void
    {
        $this->gdprCemv = $gdprCemv;
    }

    /**
     * @return string|null
     */
    public function getGdprCems(): ?string
    {
        return $this->gdprCems;
    }

    /**
     * @param string|null $gdprCems
     */
    public function setGdprCems(?string $gdprCems): void
    {
        $this->gdprCems = $gdprCems;
    }

    /**
     * @return string|null
     */
    public function getCustomerContext(): ?string
    {
        return $this->customerContext;
    }

    /**
     * @param string|null $customerContext
     * @return self
     */
    public function setCustomerContext(?string $customerContext): self
    {
        $this->customerContext = $customerContext;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMasterNavigationId(): ?string
    {
        return $this->masterNavigationId;
    }

    /**
     * @param string|null $masterNavigationId
     */
    public function setMasterNavigationId(?string $masterNavigationId): void
    {
        $this->masterNavigationId = $masterNavigationId;
    }


}
