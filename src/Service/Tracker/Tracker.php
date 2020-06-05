<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Service\Tracker;

use Shopware\Core\Framework\Struct\Struct;

/**
 * @package Boxalino\RealTimeUserExperience\Service\Tracker
 */
class Tracker extends Struct
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
    protected $url;

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
     * @return ApiTracker
     */
    public function setAccount(string $account): Tracker
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
     * @return Tracker
     */
    public function setApiKey(?string $apiKey): Tracker
    {
        $this->apiKey = $apiKey;
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
     * @return Tracker
     */
    public function setApiSecret(?string $apiSecret): Tracker
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
     * @return ApiTracker
     */
    public function setIsActive(bool $isActive): Tracker
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
     * @return ApiTracker
     */
    public function setIsTest(bool $isTest): Tracker
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
     * @return Tracker
     */
    public function setIsDev(bool $isDev): Tracker
    {
        $this->isDev = $isDev;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return ApiTracker
     */
    public function setUrl(string $url): Tracker
    {
        $this->url = $url;
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
     * @return ApiTracker
     */
    public function setCustomerContext(?string $customerContext): Tracker
    {
        $this->customerContext = $customerContext;
        return $this;
    }

}
