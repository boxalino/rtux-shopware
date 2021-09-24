<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Framework;

use Shopware\Core\System\SalesChannel\SalesChannelContext;

/**
 * Trait SalesChannelContextTrait
 * Used in contexts where the sales channel is relevant and required
 *
 * @package Boxalino\RealTimeUserExperience\Framework
 */
trait SalesChannelContextTrait
{
    /**
     * @var SalesChannelContext
     */
    protected $salesChannelContext;

    /**
     * @return SalesChannelContext
     */
    public function getSalesChannelContext(): SalesChannelContext
    {
        return $this->salesChannelContext;
    }

    /**
     * @param SalesChannelContext $salesChannelContext
     * @return self
     */
    public function setSalesChannelContext(SalesChannelContext $salesChannelContext): self
    {
        $this->salesChannelContext = $salesChannelContext;
        return $this;
    }

    /**
     * @return string
     */
    public function getContextId() : string
    {
        return $this->getSalesChannelContext()->getSalesChannel()->getId();
    }



}
