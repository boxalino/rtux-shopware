<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Framework\Content\Page;

use Boxalino\RealTimeUserExperience\Framework\SalesChannelContextTrait;
use Boxalino\RealTimeUserExperience\Service\Util\Configuration;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\ContextInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\ApiCallServiceInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Util\ConfigurationInterface;

/**
 * Trait ApiLoaderTrait
 * Definitions for the abstract functions required in api response content loaders
 *
 * @package Boxalino\RealTimeUserExperience\Framework\Content\Page
 */
trait ApiLoaderTrait
{
    use SalesChannelContextTrait;

    /**
     * @return string
     */
    public function getContextId(): string
    {
        return $this->getSalesChannelContext()->getSalesChannel()->getId();
    }

    /**
     * Setting the active sales channel context on the handler of the response
     * @return mixed
     */
    protected function _afterApiCallService() : void
    {
        parent::_afterApiCallService();

        /** this is a required step */
        $this->apiCallService->getApiResponse()->getAccessorHandler()->setSalesChannelContext($this->getSalesChannelContext());
    }

    /**
     * Prepare the context : adding the sales channel context
     */
    protected function _beforeApiCallService() : void
    {
        $this->getConfiguration()->setContextId($this->getContextId());
        $this->getApiContext()->setSalesChannelContext($this->getSalesChannelContext());
    }

    /**
     * @return ConfigurationInterface
     */
    public function getConfiguration() : ConfigurationInterface
    {
        return $this->configuration;
    }
}
