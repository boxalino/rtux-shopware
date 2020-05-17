<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Service\Api\Request;

/**
 * Interface ParameterInterface
 *
 * @package Boxalino\RealTimeUserExperience\Service\Api\Request
 */
interface ParameterInterface
{
    /**
     * @return array
     */
    public function toArray() : array;
}
