<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Framework\Request;

use Boxalino\RealTimeUserExperience\Service\Api\Request\Definition\ItemRequestDefinitionInterface;
use Boxalino\RealTimeUserExperience\Service\Api\Request\Context\ItemContextInterface;
use Boxalino\RealTimeUserExperience\Service\Api\Request\ParameterFactory;
use Boxalino\RealTimeUserExperience\Service\Api\Request\RequestDefinitionInterface;
use Boxalino\RealTimeUserExperience\Service\ErrorHandler\MissingDependencyException;
use Boxalino\RealTimeUserExperience\Service\ErrorHandler\WrongDependencyTypeException;
use PhpParser\Error;
use Symfony\Component\HttpFoundation\Request;

/**
 * Item context request
 * Can be used for CrossSelling, basket, blog recommendations
 * and other contexts where the response is item context-based
 *
 * Generally the item context requires a product/blog id as an item context
 * set on the API request
 *
 * @package Boxalino\RealTimeUserExperience\Framework\Request
 */
abstract class ItemContextAbstract
    extends ContextAbstract
    implements ItemContextInterface, ShopwareApiContextInterface
{

    /**
     * @var string
     */
    protected $productId;

    /**
     * @var array
     */
    protected $contextItemIds = [];

    /**
     * @var bool
     */
    protected $useConfiguredProductsAsContextParameters = false;

    /**
     * @var array
     */
    protected $subProductIds = [];

    /**
     * @param Request $request
     * @return RequestDefinitionInterface
     */
    public function get(Request $request) : RequestDefinitionInterface
    {
        if(!$this->productId)
        {
            throw new MissingDependencyException(
                "BoxalinoAPI: the product ID is required on a ProductRecommendation context"
            );
        }
        parent::get($request);
        $this->getApiRequest()
            ->addItems(
                $this->parameterFactory->get(ParameterFactory::BOXALINO_API_REQUEST_PARAMETER_TYPE_ITEM)
                    ->add($this->getGroupBy(), $this->getProductId())
            );

        /**
         * setting subProduct elements (ex: for basket requests)
         */
        foreach($this->subProductIds as $subProductId)
        {
            $this->getApiRequest()
                ->addItems(
                    $this->parameterFactory->get(ParameterFactory::BOXALINO_API_REQUEST_PARAMETER_TYPE_ITEM)
                        ->add($this->getGroupBy(), $subProductId, "subProduct")
                );
        }

        if($this->useConfiguredProductsAsContextParameters())
        {
            foreach($this->contextItemIds as $type => $ids)
            {
                $this->getApiRequest()
                    ->addParameters(
                        $this->parameterFactory->get(ParameterFactory::BOXALINO_API_REQUEST_PARAMETER_TYPE_USER)
                            ->add($type, $ids)
                    );
            }
        }

        return $this->getApiRequest();
    }

    /**
     * @param string $id
     * @return $this
     */
    public function setProductId(string $id) : self
    {
        $this->productId = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getProductId() : string
    {
        return $this->productId;
    }

    /**
     * Setting the existing context items (cross-sellings) existing on the product
     * in order to include or exclude them for request
     *
     * @param string $type
     * @param array $values
     * @return $this
     */
    public function addContextParametersByType(string $type, array $values) : self
    {
        $this->contextItemIds["bx_" . $type] = $values;
        return $this;
    }

    /**
     * @param bool $value
     * @return $this
     */
    public function setConfiguredProductsAsContextParameters(bool $value) : self
    {
        $this->useConfiguredProductsAsContextParameters = false;
        return $this;
    }

    /**
     * @return bool
     */
    public function useConfiguredProductsAsContextParameters() : bool
    {
        return $this->useConfiguredProductsAsContextParameters;
    }

    /**
     * Adding subproduct IDs for basket requests
     *
     * @param string $id
     * @return $this|mixed
     */
    public function addSubProduct(string $id)
    {
        $this->subProductIds[] = $id;
        return $this;
    }

    /**
     * Enforce a dependency type for the ItemContext requests
     *
     * @param RequestDefinitionInterface $requestDefinition
     * @return self | Error
     */
    public function setRequestDefinition(RequestDefinitionInterface $requestDefinition)
    {
        if($requestDefinition instanceof ItemRequestDefinitionInterface)
        {
            return parent::setRequestDefinition($requestDefinition);
        }

        throw new WrongDependencyTypeException("BoxalinoAPIContext: " . get_called_class() .
            " request definition must be an instance of ItemRequestDefinitionInterface");
    }

}
