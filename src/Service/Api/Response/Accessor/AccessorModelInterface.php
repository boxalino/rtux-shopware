<?php
namespace Boxalino\RealTimeUserExperience\Service\Api\Response\Accessor;

/**
 * @package Boxalino\RealTimeUserExperience\Service\Api\Response\Accessor
 */
interface AccessorModelInterface
{

    /**
     * @param AccessorInterface | null $context
     * @return AccessorModelInterface
     */
    public function addAccessorContext(?AccessorInterface $context = null) : AccessorModelInterface;

}
