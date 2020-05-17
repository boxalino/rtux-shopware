<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Service\Api\Response;

use Boxalino\RealTimeUserExperience\Service\Api\Response\Accessor\AccessorInterface;
use Boxalino\RealTimeUserExperience\Service\Api\Response\Accessor\Block;
use Boxalino\RealTimeUserExperience\Service\Api\Util\AccessorHandlerInterface;
use Boxalino\RealTimeUserExperience\Service\ErrorHandler\UndefinedPropertyError;

/**
 * Class ResponseHydratorTrait
 *
 * Hydrates the response to the objects mapping as defined in the AccessorHandler
 * (see Resources/config/services/api/accessor.xml and Boxalino\RealTimeUserExperience\Service\Api\Util\AccessorHandlerInterface)
 */
trait ResponseHydratorTrait
{

    /**
     * Transform an element to an object
     * (ex: a response element to the desired type)
     *
     * @param \StdClass $data
     * @param AccessorInterface $object
     * @return mixed
     */
    public function toObject(\StdClass $data, AccessorInterface $object) : AccessorInterface
    {
        $dataAsObject = new \ReflectionObject($data);
        $properties = $dataAsObject->getProperties();
        $class = get_class($object);
        $methods = get_class_methods($class);

        foreach($properties as $property)
        {
            $propertyName = $property->getName();
            $value = $data->$propertyName;
            $setter = "set" . strtoupper(substr($propertyName, 0, 1)) . substr($propertyName, 1);
            /**
             * accessor are informative Boxalino system variables which have no value to the integration system
             */
            if($value === ['accessor'] || $value === "accessor")
            {
                continue;
            }

            if(in_array($setter, $methods))
            {
                $object->$setter($value);
                continue;
            }

            if($this->getAccessorHandler()->hasAccessor($propertyName))
            {
                $handler = $this->getAccessorHandler()->getAccessor($propertyName);
                $objectProperty = $this->getAccessorHandler()->getAccessorSetter($propertyName);
                /** the facets is returned as a list/[] instead of a model itself */
                if($propertyName === 'bx-facets')
                {
                    $object->set($objectProperty, $value);
                    continue;
                }

                if(empty($value))
                {
                    $object->set($objectProperty, $handler);
                    continue;
                }

                if(is_array($value))
                {
                    $value = array_pop($value);
                }
                $valueObject = $this->toObject($value, $handler);
                $object->set($objectProperty, $valueObject);

                continue;
            }

            $object->set($propertyName, $value);
        }

        return $object;
    }


    /**
     * Find the block that has a property set
     *
     * @param $block
     * @param string $property
     * @return AccessorInterface|null
     */
    public function findObjectWithProperty($block, string $property) : ?AccessorInterface
    {
        $response = null;
        if($block instanceof Block)
        {
            try{
                if($block->get($property))
                {
                    $response = $block;
                }
            } catch(\Throwable $exception) {
                foreach($block->getBlocks() as $searchBlock)
                {
                    try{
                        if($searchBlock->get($property))
                        {
                            $response = $searchBlock;
                        }
                    } catch (\Throwable $exception)
                    {
                        try {
                            foreach ($searchBlock->getBlocks() as $childBlock) {
                                try {
                                    $response = $childBlock->findObjectWithProperty($childBlock, $property);
                                } catch (\Throwable $exception) {
                                    continue;
                                }
                            }

                            if(is_null($response))
                            {
                                continue;
                            }

                            return $response;
                        } catch (\Throwable $exception) {
                            continue;
                        }
                    }

                    if(is_null($response))
                    {
                        continue;
                    }
                }
            }
        }

        return $response;
    }

    /**
     * @return AccessorHandlerInterface
     */
    abstract function getAccessorHandler() : AccessorHandlerInterface;

    /**
     * Helper function to debug accessor element
     *
     * @param $content
     */
    public function log($content)
    {
        if(property_exists($this, "logger"))
        {
            $this->logger->info($content);
            return;
        }

        if(property_exists($this->getAccessorHandler(), "logger"))
        {
            $this->getAccessorHandler()->getLogger()->info($content);
        }
    }

}
