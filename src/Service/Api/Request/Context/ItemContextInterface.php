<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Service\Api\Request\Context;

use Boxalino\RealTimeUserExperience\Service\Api\Request\ContextInterface;

/**
 * Interface ItemContextInterface
 * @package Boxalino\RealTimeUserExperience\Service\Api\Request
 */
interface ItemContextInterface extends ContextInterface
{
    /**
     * @param string $productId
     * @return self
     */
    public function setProductId(string $productId);

    /**
     * @param bool $value
     * @return self
     */
    public function setConfiguredProductsAsContextParameters(bool $value);

    /**
     * @return bool
     */
    public function useConfiguredProductsAsContextParameters() : bool;

    /**
     * @param string $type
     * @param array $values
     * @return self
     */
    public function addContextParametersByType(string $type, array $values);

    /**
     * @param string $id
     * @return mixed
     */
    public function addSubProduct(string $id);

}
