<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Service\Tracker;

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
    protected $serverUrl;

    /**
     * @var string | null
     */
    protected $customerContext;

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
        return $this->url;
    }

    /**
     * @param string $url
     * @return self
     */
    public function setTrackerUrl(string $url): self
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return string
     */
    public function getServerUrl(): string
    {
        return $this->serverUrl;
    }

    /**
     * @param string $serverUrl
     * @return self
     */
    public function setServerUrl(string $serverUrl): self
    {
        $this->serverUrl = $serverUrl;
        return $this;
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

}
