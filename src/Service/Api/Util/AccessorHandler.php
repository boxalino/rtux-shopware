<?php
namespace Boxalino\RealTimeUserExperience\Service\Api\Util;

use Boxalino\RealTimeUserExperience\Framework\SalesChannelContextTrait;
use Boxalino\RealTimeUserExperience\Service\Api\Response\Accessor\AccessorInterface;
use Boxalino\RealTimeUserExperience\Service\Api\Response\Accessor\AccessorModelInterface;
use Boxalino\RealTimeUserExperience\Service\ErrorHandler\MissingDependencyException;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class ResponseAccessor
 *
 * Boxalino system accessors (base)
 * It is updated on further API extension & use-cases availability
 * Can be extended via custom API version provision
 *
 * @package Boxalino\RealTimeUserExperience\Service\Api\Util
 */
class AccessorHandler implements AccessorHandlerInterface
{
    use SalesChannelContextTrait;

    /**
     * @var \ArrayObject
     */
    protected $accessorDefinitions;

    /**
     * @var \ArrayObject
     */
    protected $accessorSetter;

    /**
     * @var \ArrayObject
     */
    protected $hitIdFieldName;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct()
    {
        $this->accessorDefinitions = new \ArrayObject();
        $this->accessorSetter = new \ArrayObject();
        $this->hitIdFieldName = new \ArrayObject();
    }

    /**
     * @param string $type
     * @param string $setter
     * @param string $modelName
     */
    public function addAccessor(string $type, string $setter="", string $modelName="")
    {
        if(!empty($setter))
        {
            $this->accessorSetter->offsetSet($type, $setter);
        }

        if(!empty($modelName))
        {
            $this->accessorDefinitions->offsetSet($type, $modelName);
        }

        return $this;
    }

    /**
     * @param string $type
     * @return mixed
     */
    public function getAccessor(string $type) : ?AccessorInterface
    {
        if($this->accessorDefinitions->offsetExists($type))
        {
            return $this->getModel($this->accessorDefinitions->offsetGet($type));
        }

        throw new MissingDependencyException(
            "BoxalinoApiAccessor: the accessor does not have a model defined for $type . Please contact Boxalino"
        );
    }

    /**
     * @param string $type
     * @return bool
     */
    public function hasAccessor(string $type) : bool
    {
        return $this->accessorDefinitions->offsetExists($type);
    }

    /**
     * @param string $type
     * @return string
     */
    public function getAccessorSetter(string $type) : ?string
    {
        if($this->accessorSetter->offsetExists($type))
        {
            return $this->accessorSetter->offsetGet($type);
        }

        throw new MissingDependencyException(
            "BoxalinoApiAccessor: the accessor does not have a setter defined for $type . Please contact Boxalino."
        );
    }

    /**
     * @param string $type
     * @return bool
     */
    public function hasSetter(string $type) : bool
    {
        return $this->accessorSetter->offsetExists($type);
    }

    /**
     * @param string $type
     * @param string $field
     * @return $this|mixed
     */
    public function addHitIdFieldName(string $type, string $field)
    {
        $this->hitIdFieldName->offsetSet($type, $field);
        return $this;
    }

    /**
     * @param string $type
     * @return string|null
     */
    public function getHitIdFieldName(string $type) : ?string
    {
        if($this->hitIdFieldName->offsetExists($type))
        {
            return $this->hitIdFieldName->offsetGet($type);
        }

        throw new MissingDependencyException(
            "BoxalinoApiResponse: the accessor does not have a hit ID field name defined for $type . Please contact Boxalino."
        );
    }

    /**
     * @internal
     * @required
     */
    public function setContainer(ContainerInterface $container): ?ContainerInterface
    {
        $previous = $this->container;
        $this->container = $container;

        return $previous;
    }

    /**
     * @param string $type
     * @param mixed|null $context
     * @return mixed
     */
    public function getModel(string $type, $context = null)
    {
        try {
            if($this->container->has($type))
            {
                $service = $this->container->get($type);
                if($service instanceof AccessorModelInterface)
                {
                    $service->addAccessorContext($context);
                }

                return $service;
            }

            $model = new $type($this);
            if($model instanceof AccessorModelInterface)
            {
                $model->addAccessorContext($context);
            }

            return $model;
        } catch (\Exception $exception)
        {
            throw new MissingDependencyException(
                "BoxalinoApiAccessor: there was an issue accessing the service/model requested for $type. Original error: " . $exception->getMessage()
            );
        }
    }

    /**
     * @param LoggerInterface $logger
     * @return $this
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

}
