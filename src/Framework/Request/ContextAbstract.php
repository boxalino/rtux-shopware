<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Framework\Request;

use Boxalino\RealTimeUserExperience\Framework\SalesChannelContextTrait;
use Boxalino\RealTimeUserExperience\Service\Api\Request\ContextInterface;
use Boxalino\RealTimeUserExperience\Service\Api\Request\ParameterFactory;
use Boxalino\RealTimeUserExperience\Service\Api\Request\RequestDefinitionInterface;
use Boxalino\RealTimeUserExperience\Service\Api\Request\RequestTransformerInterface;
use Boxalino\RealTimeUserExperience\Service\ErrorHandler\MissingDependencyException;
use GuzzleHttp\Client;
use JsonSerializable;
use Psr\Http\Message\RequestInterface;
use Shopware\Core\Content\Product\Aggregate\ProductVisibility\ProductVisibilityDefinition;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextServiceInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ContextAbstract
 * System-specific request context definition
 * Builds the request with the use of the request definitions entities and the use of the request transformer
 *
 * @package Boxalino\RealTimeUserExperience\Framework\Request
 */
abstract class ContextAbstract implements ShopwareApiContextInterface
{

    use SalesChannelContextTrait;

    /**
     * @var RequestDefinitionInterface
     */
    protected $apiRequest;

    /**
     * @var ParameterFactory
     */
    protected $parameterFactory;

    /**
     * @var RequestTransformerInterface
     */
    protected $requestTransformer;

    /**
     * @var string
     */
    protected $widget;

    /**
     * @var bool
     */
    protected $orFilters = false;

    /**
     * @var int
     */
    protected $hitCount = 0;

    /**
     * @var int
     */
    protected $offset = 0;

    /**
     * @var string
     */
    protected $groupBy = "products_group_id";

    /**
     * @var \ArrayObject
     */
    protected $properties;

    /**
     * Listing constructor.
     *
     * @param RequestTransformerInterface $requestTransformer
     * @param ParameterFactory $parameterFactory
     */
    public function __construct(
        RequestTransformerInterface $requestTransformer,
        ParameterFactory $parameterFactory
    ) {
        $this->properties = new \ArrayObject();
        $this->requestTransformer = $requestTransformer;
        $this->parameterFactory = $parameterFactory;
    }

    /**
     * @param Request $request
     * @return RequestDefinitionInterface
     */
    public function get(Request $request) : RequestDefinitionInterface
    {
        if(!$this->salesChannelContext)
        {
            throw new MissingDependencyException(
                "BoxalinoAPI: the SalesChannelContext has not been set on the ContextDefinition"
            );
        }
        $this->requestTransformer->setRequestDefinition($this->getApiRequest())
            ->setSalesChannelContext($this->salesChannelContext)
            ->transform($request);

        $this->setRequestDefinition($this->requestTransformer->getRequestDefinition());
        $this->getApiRequest()
            ->setReturnFields($this->getReturnFields())
            ->setGroupBy($this->getGroupBy())
            ->setWidget($this->getWidget());

        $this->addFilters($request);
        $this->addContextParameters($request);


        return $this->getApiRequest();
    }

    abstract function getContextNavigationId(Request $request, SalesChannelContext $salesChannelContext): array;
    abstract function getContextVisibility() : int;
    abstract function getReturnFields() : array;

    /**
     * Adding context parameters per integration use-case
     * (for custom integrations)
     *
     * @param Request $request
     * @return void
     */
    protected function addContextParameters(Request $request) : void
    {
        if($this->getHitCount())
        {
            $this->getApiRequest()->setHitCount($this->getHitCount());
        }
    }

    /**
     * Adding general filters
     *
     * @param Request $request
     */
    protected function addFilters(Request $request) : void
    {
        $this->getApiRequest()
            ->addFilters(
                $this->parameterFactory->get(ParameterFactory::BOXALINO_API_REQUEST_PARAMETER_TYPE_FILTER)
                    ->add("category_id", $this->getContextNavigationId($request, $this->salesChannelContext)),
                $this->parameterFactory->get(ParameterFactory::BOXALINO_API_REQUEST_PARAMETER_TYPE_FILTER)
                    ->addRange("products_visibility", $this->getContextVisibility(),1000),
                $this->parameterFactory->get(ParameterFactory::BOXALINO_API_REQUEST_PARAMETER_TYPE_FILTER)
                    ->add("products_active", [1])
            );
    }

    /**
     * @param string $widget
     * @return $this
     */
    public function setWidget(string $widget)
    {
        $this->widget = $widget;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getWidget() : string
    {
        return $this->widget;
    }

    /**
     * @return RequestDefinitionInterface
     */
    public function getApiRequest() : RequestDefinitionInterface
    {
        return $this->apiRequest;
    }

    /**
     * @param RequestDefinitionInterface $requestDefinition
     * @return $this
     */
    public function setRequestDefinition(RequestDefinitionInterface $requestDefinition)
    {
        $this->apiRequest = $requestDefinition;
        return $this;
    }

    /**
     * @param bool $orFilters
     * @return ContextAbstract
     */
    public function setOrFilters(bool $orFilters) : self
    {
        $this->orFilters = $orFilters;
        return $this;
    }

    /**
     * @return bool
     */
    public function getOrFilters(): bool
    {
        return $this->orFilters;
    }

    /**
     * @param string $groupBy
     * @return ContextAbstract
     */
    public function setGroupBy(string $groupBy) : self
    {
        $this->groupBy = $groupBy;
        return $this;
    }

    /**
     * @return string
     */
    public function getGroupBy() : string
    {
        return $this->groupBy;
    }

    /**
     * @return int
     */
    public function getHitCount(): int
    {
        return $this->hitCount;
    }

    /**
     * @param int $hitCount
     * @return ContextAbstract
     */
    public function setHitCount(int $hitCount) : self
    {
        $this->hitCount = $hitCount;
        return $this;
    }

    /**
     * @return int
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * @param int $offset
     * @return ContextAbstract
     */
    public function setOffset(int $offset) : self
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * @param string $property
     * @param string | bool $value
     * @return $this
     */
    public function set(string $property, $value)
    {
        $this->properties->offsetSet($property, $value);
        return $this;
    }

    /**
     * @param string $property
     * @return string | bool | void
     */
    public function getProperty(string $property)
    {
        if($this->properties->offsetExists($property))
        {
            return $this->properties->offsetGet($property);
        }
    }

    /**
     * @param string $property
     * @return bool
     */
    public function has(string $property) : bool
    {
        return $this->properties->offsetExists($property);
    }

}
