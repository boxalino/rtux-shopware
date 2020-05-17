<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Service\Api\Request\Parameter;

use Boxalino\RealTimeUserExperience\Service\Api\Request\ParameterDefinition;

/**
 * Class SortingDefinition
 * Setting a sorting option on the request
 *
 * @package Boxalino\RealTimeUserExperience\Service\Api\Request
 */
class SortingDefinition extends ParameterDefinition
{

    /**
     * @param string $field
     * @param bool $reverse
     * @return $this
     */
    public function add(string $field, bool $reverse = false) : self
    {
        $this->field = $field;
        $this->reverse = $reverse;

        return $this;
    }

}
