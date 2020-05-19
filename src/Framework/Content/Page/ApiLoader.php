<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Framework\Content\Page;

use Boxalino\RealTimeUserExperience\Framework\SalesChannelContextTrait;
use Boxalino\RealTimeUserExperienceApi\Service\Api\ApiCallServiceInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\ContextInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Util\ConfigurationInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use \Boxalino\RealTimeUserExperienceApi\Framework\Content\Page\ApiLoader as ApiLoaderBase;

/**
 * Class ApiLoader
 *
 * @package Boxalino\RealTimeUserExperience\Service\Api\Content\Page
 */
class ApiLoader extends ApiLoaderBase
{
    use SalesChannelContextTrait;

    /**
     * Makes a call to the Boxalino API
     * Sets the sales channel context
     *
     * @param Request $request
     * @param SalesChannelContext $salesChannelContext
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function call(Request $request) : void
    {
        $this->apiContextInterface->setSalesChannelContext($this->getSalesChannelContext());
        parent::call($request);

        if(!$this->apiCallService->isFallback())
        {
            /** this is a required step */
            $this->apiCallService->getApiResponse()->getAccessorHandler()->setSalesChannelContext($this->getSalesChannelContext());
        }

        return;
    }

    /**
     * @return string
     */
    public function getContextId(): string
    {
        return $this->getSalesChannelContext()->getSalesChannel()->getId();
    }

}
