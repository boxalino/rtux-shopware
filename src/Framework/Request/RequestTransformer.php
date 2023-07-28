<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Framework\Request;

use Boxalino\RealTimeUserExperience\Framework\SalesChannelContextTrait;
use Boxalino\RealTimeUserExperienceApi\Framework\Content\Listing\ApiSortingModelInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\ParameterFactoryInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\RequestDefinitionInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\RequestInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\RequestTransformerInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Util\ConfigurationInterface;
use Boxalino\RealTimeUserExperienceApi\Service\ErrorHandler\MissingDependencyException;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\PlatformRequest;
use Shopware\Core\SalesChannelRequest;
use Boxalino\RealTimeUserExperienceApi\Framework\Request\RequestTransformerAbstract as ApiRequestTransformer;
use Shopware\Core\System\SystemConfig\SystemConfigService;

/**
 * Class RequestTransformer
 *
 * Adds system-specific (Shopware6) request parameters toa boxalino request
 * Sets request variables dependent on the channel
 * (account, credentials, environment details -- language, dev, test, session, header parameters, etc)
 *
 * @package Boxalino\RealTimeUserExperience\Framework\Request
 */
class RequestTransformer extends ApiRequestTransformer
    implements RequestTransformerInterface
{
    use SalesChannelContextTrait;
    use RequestParametersTrait;

    /**
     * @var array
     */
    protected $requestParameters = [];

    /**
     * @var SystemConfigService
     */
    protected $systemConfigService;

    public function __construct(
        ParameterFactoryInterface $parameterFactory,
        ConfigurationInterface $configuration,
        ApiSortingModelInterface $sortingModel,
        LoggerInterface $logger,
        SystemConfigService $systemConfigService
    ){
        parent::__construct($parameterFactory, $configuration, $sortingModel, $logger);
        $this->systemConfigService = $systemConfigService;
    }

    /**
     * Sets context parameters (credentials, server, etc)
     * Adds parameters per request query elements
     *
     * @param RequestInterface $request
     * @return RequestDefinitionInterface
     */
    public function transform(RequestInterface $request): RequestDefinitionInterface
    {
        if(!$this->salesChannelContext)
        {
            throw new MissingDependencyException(
                "BoxalinoAPI: the SalesChannelContext has not been set on the RequestTransformer"
            );
        }

        parent::transform($request);
        $this->requestDefinition->setLanguage($this->getLanguage());

        return $this->requestDefinition;
    }

    /**
     * Processing the RequestInterface parameters
     *
     * @param RequestInterface $request
     */
    public function addParameters(RequestInterface $request) : void
    {
        parent::addParameters($request);
        $this->requestDefinition->addHeaderParameters(
            $this->parameterFactory->get(ParameterFactoryInterface::BOXALINO_API_REQUEST_PARAMETER_TYPE_HEADER)
                ->add("contextId", $this->getContextId())
        );

        foreach($this->requestParameters as $parameter)
        {
            $value = $request->getParam($parameter, null);
            if(is_null($value))
            {
                continue;
            }
            if(is_array($value))
            {
                $this->requestDefinition->addParameters(
                    $this->parameterFactory->get(ParameterFactoryInterface::BOXALINO_API_REQUEST_PARAMETER_TYPE_USER)
                        ->add($parameter, $value)
                );
                continue;
            }
            $this->requestDefinition->addHeaderParameters(
                $this->parameterFactory->get(ParameterFactoryInterface::BOXALINO_API_REQUEST_PARAMETER_TYPE_HEADER)
                    ->add($parameter, rawurldecode($value))
            );
        }
    }

    /**
     * @param array $parameters
     * @return $this
     */
    public function addRequestParameters(array $parameters)
    {
        $this->requestParameters = $parameters;
        return $this;
    }

    /**
     * @param RequestInterface $request
     * @return string
     */
    public function getCustomerId(RequestInterface $request) : string
    {
        if(is_null($this->getSalesChannelContext()->getCustomer()))
        {
            return $this->getProfileId($request);
        }

        return $this->getSalesChannelContext()->getCustomer()->getId();
    }

    /**
     * @return string
     */
    public function getLanguage() : string
    {
        return $this->configuration->getLanguageCode(
            $this->getSalesChannelContext()->getContext()->getLanguageId()
        );
    }


}
