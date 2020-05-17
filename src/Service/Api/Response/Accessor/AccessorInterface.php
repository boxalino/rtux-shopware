<?php
namespace Boxalino\RealTimeUserExperience\Service\Api\Response\Accessor;

/**
 * @package Boxalino\RealTimeUserExperience\Service\Api\Response\Accessor
 */
interface AccessorInterface
{
    /**
     * @param \StdClass $data
     * @param AccessorInterface $object
     * @return AccessorInterface
     */
    public function toObject(\StdClass $data, AccessorInterface $object) : AccessorInterface;

    /**
     * @param string $parameterName
     * @param $content
     * @return mixed
     */
    public function set(string $parameterName, $content);

    /**
     * @param string $parameterName
     * @return mixed
     */
    public function get(string $parameterName);

}
