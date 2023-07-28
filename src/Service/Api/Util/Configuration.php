<?php
namespace Boxalino\RealTimeUserExperience\Service\Api\Util;

use Boxalino\RealTimeUserExperienceApi\Service\Api\Util\ConfigurationInterface;
use Shopware\Core\System\SystemConfig\SystemConfigService;

/**
 * Class Configuration
 * Configurations defined for the REST API requests
 *
 * @package Boxalino\RealTimeUserExperience\Service\Api\Util
 */
class Configuration extends \Boxalino\RealTimeUserExperience\Service\Util\Configuration
    implements ConfigurationInterface
{

    /**
     * @var null | string
     */
    protected $channelId = null;

    /**
     * @param string $channelId
     * @return $this
     */
    public function setContextId(string $channelId) : self
    {
        $this->channelId = $channelId;
        $this->getPluginConfigByChannelId($channelId);

        return $this;
    }

    /**
     * @param string $method
     * @param array $params
     * @return mixed
     */
    public function __call(string $method, array $params = [])
    {
        preg_match('/^(get)(.*?)$/i', $method, $matches);
        $prefix = $matches[1] ?? '';
        if ($prefix == 'get')
        {
            if(isset($params[0]) && !isset($this->config[$params[0]]))
            {
                $this->getPluginConfigByChannelId($params[0]);
            }

            return $this->$method();
        }
    }

    /**
     * @return bool
     */
    public function isApiEnabled() : bool
    {
        try{
            return (bool)$this->config[$this->channelId]['status'];
        } catch (\Exception $exception)
        {
            return false;
        }
    }

    /**
     * The API endpoint depends on the testing conditionals and on the data index
     * @return string
     */
    public function getRestApiEndpoint() : string
    {
        try{
            $endpoint = $this->config[$this->channelId]['apiUrl'];
            if(empty($endpoint))
            {
                return str_replace("%%account%%", $this->getUsername(), $this->getEndpointByDomain());
            }

            return $endpoint;
        } catch (\Throwable $exception)
        {
            return str_replace("%%account%%", $this->getUsername(), $this->getEndpointByDomain());
        }
    }

    /**
     * @return string
     */
    public function getUsername() : string
    {
        try{
            return $this->config[$this->channelId]['account'];
        } catch (\Throwable $exception)
        {
            return "";
        }
    }

    /**
     * @return string
     */
    public function getApiKey() : string
    {
        try{
            return $this->config[$this->channelId]['apiKey'];
        } catch (\Throwable $exception)
        {
            return "";
        }
    }

    /**
     * @return string
     */
    public function getApiSecret() : string
    {
        try{
            return $this->config[$this->channelId]['apiSecret'];
        } catch (\Throwable $exception)
        {
            return "";
        }
    }

    /**
     * @return bool
     */
    public function getIsDev() : bool
    {
        try{
            return (bool)$this->config[$this->channelId]['devIndex'];
        } catch (\Throwable $exception)
        {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function getIsTest() : bool
    {
        try{
            return (bool)$this->config[$this->channelId]['test'];
        } catch (\Throwable $exception)
        {
            return false;
        }
    }

    /**
     * On server-side requests, only the bx-cloud is used
     * On frontend-side requests, only the alternative domain is used
     * @param string|null $domain
     * @return string
     */
    public function getEndpointByDomain(?string $domain = ConfigurationInterface::RTUX_API_DOMAIN_MAIN) : string
    {
        if($this->getIsDev() || $this->getIsTest())
        {
            return str_replace("%%domain%%", $domain,ConfigurationInterface::RTUX_API_ENDPOINT_STAGE);
        }

        return str_replace("%%domain%%", $domain, ConfigurationInterface::RTUX_API_ENDPOINT_PRODUCTION);
    }


}
