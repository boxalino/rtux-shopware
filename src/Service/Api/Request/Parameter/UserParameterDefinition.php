<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Service\Api\Request\Parameter;

use Boxalino\RealTimeUserExperience\Service\Api\Request\ParameterDefinition;

/**
 * Class UserParameterDefinition
 *
 * Required parameters for every request:
 * User-Host, User-Referer, User-Url, User-Agent
 *
 * Any additional parameters can be added
 * @package Boxalino\RealTimeUserExperience\Service\Api\Request
 */
class UserParameterDefinition extends ParameterDefinition
{

    /**
     * @param string $property
     * @param array $values
     * @return $this
     */
    public function add(string $property, ?array $values)
    {
        $this->{$property} = $values;
        return $this;
    }
}
