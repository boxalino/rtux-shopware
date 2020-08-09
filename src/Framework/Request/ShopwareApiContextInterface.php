<?php
namespace Boxalino\RealTimeUserExperience\Framework\Request;

use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\ContextInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

/**
 * Interface ShopwareContextInterface
 * @package Boxalino\RealTimeUserExperience\Framework\Request
 */
interface ShopwareApiContextInterface extends ContextInterface
{
    /**
     * @param SalesChannelContext $salesChannelContext
     * @return mixed
     */
    public function setSalesChannelContext(SalesChannelContext $salesChannelContext);

    /**
     * @return string
     */
    public function getContextId() : string;

}
