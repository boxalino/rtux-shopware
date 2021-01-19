<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Framework\Content;

use Boxalino\RealTimeUserExperienceApi\Framework\Content\Page\ApiLoaderInterface;
use Shopware\Core\Framework\Struct\Struct;

/**
 * Trait CreateFromTrait
 * Generation of a class as the trait CreateFromTrait::createFrom($object) is meant to do
 * (ex: helps duplicate elements)
 *
 * @package Boxalino\RealTimeUserExperience\Framework\Content
 */
trait CreateFromTrait
{
    /**
     * @param Struct $object
     * @param array $excludeProperties
     * @return Struct
     */
    public function createFromObject(Struct $object, array $excludeProperties) : Struct
    {
        try {
            $new = (new \ReflectionClass(get_class($object)))
                ->newInstanceWithoutConstructor();
        } catch (\ReflectionException $exception) {
            throw new \InvalidArgumentException($exception->getMessage());
        }

        foreach ($object->getVars() as $property => $value)
        {
            if(is_null($value) || in_array($property, $excludeProperties))
            {
                continue;
            }
            $functionName = "set".ucfirst($property);
            $new->$functionName($value);
        }

        return $new;
    }

    /**
     * @param ApiLoaderInterface $object
     * @return ApiLoaderInterface
     */
    public function createFromApiLoaderObject(ApiLoaderInterface $object, array $excludeProperties = []) : ApiLoaderInterface
    {
        try {
            /** @var ApiLoaderInterface $loader */
            $loader = (new \ReflectionClass(get_class($object)))
                ->newInstanceWithoutConstructor();
        } catch (\ReflectionException $exception) {
            throw new \InvalidArgumentException($exception->getMessage());
        }

        $functions = get_class_methods($object);
        foreach ($functions as $function)
        {
            $method = substr($function, 0, 3);
            $property = substr($function, 3);
            $setter = "set" . $property;
            if($method == "get" && in_array($setter, $functions) && !in_array($property, $excludeProperties))
            {
                $loader->$setter($object->$function());
            }
        }

        return $loader;
    }

    /**
     * @param $object
     * @return object
     */
    public function createEmptyFromObject($object)
    {
        try {
            $new = (new \ReflectionClass(get_class($object)))
                ->newInstanceWithoutConstructor();
        } catch (\ReflectionException $exception) {
            throw new \InvalidArgumentException($exception->getMessage());
        }

        return $new;
    }


}
