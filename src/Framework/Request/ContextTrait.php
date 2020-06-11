<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Framework\Request;

use Boxalino\RealTimeUserExperience\Framework\SalesChannelContextTrait;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\ParameterFactory;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\RequestDefinitionInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\RequestTransformerInterface;
use Boxalino\RealTimeUserExperienceApi\Service\ErrorHandler\MissingDependencyException;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\ParameterInterface;
use GuzzleHttp\Client;
use JsonSerializable;
use Symfony\Component\HttpFoundation\Request;

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
     * @var ParameterFactory
     */
    protected $parameterFactory;

    /**
     * @param Request $request
     */
    public function validateRequest(Request $request) : void
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
    public function getVisibilityFilter(Request $request) : ParameterInterface
    {
        return $this->getParameterFactory()->get(ParameterFactory::BOXALINO_API_REQUEST_PARAMETER_TYPE_FILTER)
            ->addRange("products_visibility", $this->getContextVisibility()[0],1000);
    }

    /**
     * @return ParameterInterface
     */
    public function getCategoryFilter(Request $request) : ParameterInterface
    {
        return $this->getParameterFactory()->get(ParameterFactory::BOXALINO_API_REQUEST_PARAMETER_TYPE_FILTER)
            ->add("category_id", $this->getContextNavigationId($request));
    }

    /**
     * @return ParameterInterface
     */
    public function getActiveFilter(Request $request) : ParameterInterface
    {
        return $this->getParameterFactory()->get(ParameterFactory::BOXALINO_API_REQUEST_PARAMETER_TYPE_FILTER)
            ->add("products_active", [1]);
    }

    /**
     * @return RequestTransformerInterface
     */
    public function getRequestTransformer()  : RequestTransformerInterface
    {
        return $this->requestTransformer;
    }

    /**
     * @return ParameterFactory
     */
    public function getParameterFactory() : ParameterFactory
    {
        return $this->parameterFactory;
    }

}
