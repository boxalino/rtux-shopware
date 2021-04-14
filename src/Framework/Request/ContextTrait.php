<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Framework\Request;

use Boxalino\RealTimeUserExperience\Framework\SalesChannelContextTrait;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\ParameterFactoryInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\RequestDefinitionInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\RequestInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\RequestTransformerInterface;
use Boxalino\RealTimeUserExperienceApi\Service\ErrorHandler\MissingDependencyException;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\ParameterInterface;
use GuzzleHttp\Client;
use JsonSerializable;

/**
 * Trait ContextTrait
 * sets all the functions required for the Boxalino\RealTimeUserExperienceApi\Framework\Request\ContextAbstract
 * to be used for all other implicit contexts
 *
 * @package Boxalino\RealTimeUserExperience\Framework\Request
 */
trait ContextTrait
{
    use SalesChannelContextTrait;

    /**
     * @var string
     */
    protected $groupBy = "products_group_id";

    /**
     * @var RequestTransformerInterface
     */
    protected $requestTransformer;

    /**
     * @var ParameterFactoryInterface
     */
    protected $parameterFactory;

    /**
     * @param RequestInterface $request
     */
    public function validateRequest(RequestInterface $request) : void
    {
        if(!$this->salesChannelContext)
        {
            throw new MissingDependencyException(
                "BoxalinoAPI: the SalesChannelContext has not been set on the ContextDefinition"
            );
        }
        $this->getRequestTransformer()->setSalesChannelContext($this->getSalesChannelContext());
    }

    /**
     * @return ParameterInterface
     */
    public function getVisibilityFilter(RequestInterface $request) : ParameterInterface
    {
        return $this->getParameterFactory()->get(ParameterFactoryInterface::BOXALINO_API_REQUEST_PARAMETER_TYPE_FILTER)
            ->addRange("visibility", $this->getContextVisibility()[0],1000);
    }

    /**
     * @return ParameterInterface
     */
    public function getCategoryFilter(RequestInterface $request) : ParameterInterface
    {
        return $this->getParameterFactory()->get(ParameterFactoryInterface::BOXALINO_API_REQUEST_PARAMETER_TYPE_FILTER)
            ->add("category_id", $this->getContextNavigationId($request));
    }

    /**
     * @return ParameterInterface
     */
    public function getActiveFilter(RequestInterface $request) : ParameterInterface
    {
        return $this->getParameterFactory()->get(ParameterFactoryInterface::BOXALINO_API_REQUEST_PARAMETER_TYPE_FILTER)
            ->add("status", [1]);
    }

    /**
     * @return RequestTransformerInterface
     */
    public function getRequestTransformer()  : RequestTransformerInterface
    {
        return $this->requestTransformer;
    }

    /**
     * @return ParameterFactoryInterface
     */
    public function getParameterFactory() : ParameterFactoryInterface
    {
        return $this->parameterFactory;
    }

}
