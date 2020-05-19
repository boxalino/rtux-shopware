<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Framework\Request;

use Boxalino\RealTimeUserExperience\Framework\Content\Listing\ApiSortingModel;
use Boxalino\RealTimeUserExperience\Framework\SalesChannelContextTrait;
use Boxalino\RealTimeUserExperienceApi\Service\Api\ApiCookieSubscriber;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\ParameterFactory;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\RequestDefinitionInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\RequestTransformerInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Util\ConfigurationInterface;
use Boxalino\RealTimeUserExperienceApi\Service\ErrorHandler\MissingDependencyException;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\PlatformRequest;
use Shopware\Core\SalesChannelRequest;
use Symfony\Component\HttpFoundation\Request;
use Boxalino\RealTimeUserExperienceApi\Framework\Request\RequestTransformerAbstract as ApiRequestTransformer;

/**
 * Class RequestTransformer
 *
 * Adds system-specific (Shopware6) request parameters toa boxalino request
 * Sets request variables dependent on the channel
 * (account, credentials, environment details -- language, dev, test, session, header parameters, etc)
 *
 * @package Boxalino\RealTimeUserExperience\Service\Api
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
     * @param Request $request
     * @return RequestDefinitionInterface
     */
    public function transform(Request $request): RequestDefinitionInterface
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
     * @param Request $request
     * @return string
     */
    public function getCustomerId(Request $request) : string
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
    public function getContextId() : string
    {
        return $this->getSalesChannelContext()->getSalesChannel()->getId();
    }

}
