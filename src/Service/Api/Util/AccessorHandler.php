<?php
namespace Boxalino\RealTimeUserExperience\Service\Api\Util;

use Boxalino\RealTimeUserExperience\Framework\SalesChannelContextTrait;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Util\AccessorHandler as ApiAccessorHandler;

/**
 * Class AccessorHandler
 *
 * Boxalino system accessors (base)
 * It is updated on further API extension & use-cases availability
 * Can be extended via custom API version provision
 *
 * @package Boxalino\RealTimeUserExperience\Service\Api\Util
 */
class AccessorHandler extends ApiAccessorHandler
{
    use SalesChannelContextTrait;
}
