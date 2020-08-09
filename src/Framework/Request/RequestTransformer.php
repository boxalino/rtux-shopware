<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Framework\Request;

use Boxalino\RealTimeUserExperience\Framework\SalesChannelContextTrait;
use Boxalino\RealTimeUserExperienceApi\Service\Api\ApiCookieSubscriber;
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

        return parent::transform($request);
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

}
