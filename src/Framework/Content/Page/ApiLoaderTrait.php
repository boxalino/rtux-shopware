<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Framework\Content\Page;

use Boxalino\RealTimeUserExperience\Framework\SalesChannelContextTrait;
use Boxalino\RealTimeUserExperience\Service\Api\Request\ContextInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\ApiCallServiceInterface;

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
    protected function validateCall(ApiCallServiceInterface $apiCallService) : void
    {
        if($apiCallService->isFallback())
        {
            throw new \Exception($apiCallService->getFallbackMessage());
        }

        /** this is a required step */
        $apiCallService->getApiResponse()->getAccessorHandler()->setSalesChannelContext($this->getSalesChannelContext());
    }

    /**
     * Prepare the context : adding the sales channel context
     */
    protected function prepareContext(ContextInterface $context) : void
    {
        $context->setSalesChannelContext($this->getSalesChannelContext());
    }
}
