<?php
namespace Boxalino\RealTimeUserExperience\Service\Api\Util;

use Boxalino\RealTimeUserExperienceApi\Service\Api\Util\ConfigurationInterface;
use Psr\Log\LoggerInterface;
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
     * The API endpoint depends on the testing conditionals and on the data index
     * @param string $channelId
     * @return string
     */
    public function getRestApiEndpoint(string $channelId) : string
    {
        try{
            return $this->config[$channelId]['apiUrl'];
        } catch (\Exception $exception)
        {
            return "";
        }
    }

    /**
     * @param string $channelId
     * @return string
     */
    public function getUsername(string $channelId) : string
    {
        try{
            return $this->config[$channelId]['account'];
        } catch (\Exception $exception)
        {
            return "";
        }
    }

    /**
     * @param string $channelId
     * @return string
     */
    public function getApiKey(string $channelId) : string
    {
        try{
            return $this->config[$channelId]['apiKey'];
        } catch (\Exception $exception)
        {
            return "";
        }
    }

    /**
     * @param string $channelId
     * @return string
     */
    public function getApiSecret(string $channelId) : string
    {
        try{
            return $this->config[$channelId]['apiSecret'];
        } catch (\Exception $exception)
        {
            return "";
        }
    }

    /**
     * @param string $channelId
     * @return bool
     */
    public function getIsDev(string $channelId) : bool
    {
        try{
            return (bool)$this->config[$channelId]['devIndex'];
        } catch (\Exception $exception)
        {
            return false;
        }
    }

    /**
     * @param string $channelId
     * @return bool
     */
    public function getIsTest(string $channelId) : bool
    {
        try{
            return (bool)$this->config[$channelId]['test'];
        } catch (\Exception $exception)
        {
            return false;
        }
    }

}
