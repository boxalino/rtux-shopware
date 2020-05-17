<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Framework\Request;

use Boxalino\RealTimeUserExperience\Framework\Content\Listing\ApiSortingModel;
use Boxalino\RealTimeUserExperience\Framework\SalesChannelContextTrait;
use Boxalino\RealTimeUserExperience\Service\Api\ApiCookieSubscriber;
use Boxalino\RealTimeUserExperience\Service\Api\Request\ParameterFactory;
use Boxalino\RealTimeUserExperience\Service\Api\Request\RequestDefinitionInterface;
use Boxalino\RealTimeUserExperience\Service\Api\Request\RequestTransformerInterface;
use Boxalino\RealTimeUserExperience\Service\Api\Util\Configuration;
use Boxalino\RealTimeUserExperience\Service\ErrorHandler\MissingDependencyException;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\PlatformRequest;
use Shopware\Core\SalesChannelRequest;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class RequestTransformer
 *
 * Adds system-specific (Shopware6) request parameters toa boxalino request
 * Sets request variables dependent on the channel
 * (account, credentials, environment details -- language, dev, test, session, header parameters, etc)
 *
 * @package Boxalino\RealTimeUserExperience\Service\Api
 */
class RequestTransformer implements RequestTransformerInterface
{
    use SalesChannelContextTrait;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @var RequestDefinitionInterface
     */
    protected $requestDefinition;

    /**
     * @var ParameterFactory
     */
    protected $parameterFactory;

    /**
     * @var ApiSortingModel
     */
    protected $productListingSortingRegistry;

    /**
     * @var int
     */
    protected $limit = 0;

    /**
     * RequestTransformer constructor.
     * @param Connection $connection
     * @param ParameterFactory $parameterFactory
     * @param Configuration $configuration
     * @param LoggerInterface $logger
     */
    public function __construct(
        Connection $connection,
        ParameterFactory $parameterFactory,
        Configuration $configuration,
        ApiSortingModel $productListingSortingRegistry,
        LoggerInterface $logger
    ) {
        $this->productListingSortingRegistry = $productListingSortingRegistry;
        $this->connection = $connection;
        $this->configuration = $configuration;
        $this->parameterFactory = $parameterFactory;
        $this->logger = $logger;
    }

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

        if(!$this->requestDefinition)
        {
            throw new MissingDependencyException(
                "BoxalinoAPI: the RequestDefinitionInterface has not been set on the RequestTransformer"
            );
        }

        $salesChannelId = $this->getSalesChannelId();
        $this->configuration->setChannelId($salesChannelId);
        $this->requestDefinition
            ->setUsername($this->configuration->getUsername($salesChannelId))
            ->setApiKey($this->configuration->getApiKey($salesChannelId))
            ->setApiSecret($this->configuration->getApiSecret($salesChannelId))
            ->setDev($this->configuration->getIsDev($salesChannelId))
            ->setTest($this->configuration->getIsTest($salesChannelId))
            ->setSessionId($this->getSessionId($request))
            ->setProfileId($this->getProfileId($request))
            ->setCustomerId($this->getCustomerId($request))
            ->setLanguage(substr($request->getLocale(), 0, 2));

        $this->addHitCount($request);
        $this->addOffset($request);
        $this->addParameters($request);

        return $this->requestDefinition;
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
    public function getSalesChannelId() : string
    {
        return $this->getSalesChannelContext()->getSalesChannel()->getId();
    }

    /**
     * The value stored in the CEMS cookie
     */
    public function getSessionId(Request $request)
    {
        if($request->cookies->has(ApiCookieSubscriber::BOXALINO_API_COOKIE_SESSION))
        {
            return $request->cookies->get(ApiCookieSubscriber::BOXALINO_API_COOKIE_SESSION);
        }

        $cookieValue = Uuid::uuid4()->toString();
        $request->cookies->set(ApiCookieSubscriber::BOXALINO_API_INIT_SESSION, $cookieValue);

        return $cookieValue;
    }

    /**
     * The value stored in the CEMV cookie
     */
    public function getProfileId(Request $request)
    {
        if($request->cookies->has(ApiCookieSubscriber::BOXALINO_API_COOKIE_VISITOR))
        {
            return $request->cookies->get(ApiCookieSubscriber::BOXALINO_API_COOKIE_VISITOR);
        }

        $cookieValue = Uuid::uuid4()->toString();
        $request->cookies->set(ApiCookieSubscriber::BOXALINO_API_INIT_VISITOR, $cookieValue);

        return $cookieValue;
    }

    /**
     * @param Request $request
     */
    public function addParameters(Request $request) : void
    {
        /** header parameters accept a string as value */
        $this->requestDefinition->addHeaderParameters(
            $this->parameterFactory->get(ParameterFactory::BOXALINO_API_REQUEST_PARAMETER_TYPE_HEADER)
                ->add("User-Host", $request->getClientIp()),
            $this->parameterFactory->get(ParameterFactory::BOXALINO_API_REQUEST_PARAMETER_TYPE_HEADER)
                ->add("User-Agent", $request->headers->get('user-agent')),
            $this->parameterFactory->get(ParameterFactory::BOXALINO_API_REQUEST_PARAMETER_TYPE_HEADER)
                ->add("User-Referer", $request->headers->get('referer')),
            $this->parameterFactory->get(ParameterFactory::BOXALINO_API_REQUEST_PARAMETER_TYPE_HEADER)
                ->add("User-Url", $request->getUri()),
            $this->parameterFactory->get(ParameterFactory::BOXALINO_API_REQUEST_PARAMETER_TYPE_HEADER)
                ->add(SalesChannelRequest::ATTRIBUTE_DOMAIN_ID,
                $request->attributes->get(SalesChannelRequest::ATTRIBUTE_DOMAIN_ID)),
            $this->parameterFactory->get(ParameterFactory::BOXALINO_API_REQUEST_PARAMETER_TYPE_HEADER)
                ->add(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_ID,
                $request->attributes->get(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_ID))
        );

        $queryString = $request->getQueryString();
        if(is_null($queryString))
        {
            return;
        }
        parse_str($queryString, $params);
        foreach($params as $param => $value)
        {
            if(in_array($param, ['p', 'limit']))
            {
                continue;
            }

            if($param == "sort")
            {
                $this->addSorting($request);
                continue;
            }

            if($param == 'search')
            {
                $this->requestDefinition->setQuery($value);
                continue;
            }

            $value = is_array($value) ? $value : [$value];
            $this->requestDefinition->addParameters(
                $this->parameterFactory->get(ParameterFactory::BOXALINO_API_REQUEST_PARAMETER_TYPE_USER)
                    ->add($param, $value)
            );
        }
    }

    /**
     * @param int $page
     * @return int
     */
    public function addOffset(Request $request) : RequestTransformer
    {
        $page = $this->getPage($request);
        $this->requestDefinition->setOffset(($page-1) * $this->getLimit($request));
        return $this;
    }

    /**
     * Hitcount is a concept similar to limit
     *
     * @param int $hits
     * @param Request $request
     * @return $this
     */
    public function addHitCount(Request $request) : RequestTransformer
    {
        $this->requestDefinition->setHitCount($this->getLimit($request));
        return $this;
    }

    /**
     * @param RequestDefinitionInterface $requestDefinition
     * @return $this
     */
    public function setRequestDefinition(RequestDefinitionInterface $requestDefinition)
    {
        $this->requestDefinition = $requestDefinition;
        return $this;
    }

    /**
     * @return RequestDefinitionInterface
     */
    public function getRequestDefinition() : RequestDefinitionInterface
    {
        return $this->requestDefinition;
    }

    /**
     * As set in platform/src/Core/Content/Product/SalesChannel/Listing/ProductListingFeaturesSubscriber.php
     * @return string | null
     */
    protected function addSorting(Request $request)
    {
        $key = $request->get('sort', ApiSortingModel::BOXALINO_DEFAULT_SORT_FIELD);
        if (!$key || $key === ApiSortingModel::BOXALINO_DEFAULT_SORT_FIELD) {
            return;
        }

        $sorting = $this->productListingSortingRegistry->requestTransform($key);
        foreach($sorting as $sort)
        {
            $this->requestDefinition->addSort(
                $this->parameterFactory->get(ParameterFactory::BOXALINO_API_REQUEST_PARAMETER_TYPE_SORT)
                    ->add($sort["field"], $sort["reverse"])
            );
        }
    }

    /**
     * As set in platform/src/Core/Content/Product/SalesChannel/Listing/ProductListingFeaturesSubscriber.php
     * @return int
     */
    protected function getLimit(Request $request): int
    {
        $limit = $request->query->getInt('limit', 24);

        if ($request->isMethod(Request::METHOD_POST)) {
            $limit = $request->request->getInt('limit', $limit);
        }

        return $limit <= 0 ? 24 : $limit;
    }

    /**
     * As set in platform/src/Core/Content/Product/SalesChannel/Listing/ProductListingFeaturesSubscriber.php
     * @return int
     */
    protected function getPage(Request $request): int
    {
        $page = $request->query->getInt('p', 1);

        if ($request->isMethod(Request::METHOD_POST)) {
            $page = $request->request->getInt('p', $page);
        }

        return $page <= 0 ? 1 : $page;
    }

}
