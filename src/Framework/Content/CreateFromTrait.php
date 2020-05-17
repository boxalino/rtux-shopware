<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Framework\Content;

use Shopware\Core\Framework\Struct\Struct;

/**
 * Trait CreateFromTrait
 * Generation of a class as the trait CreateFromTrait::createFrom($object) is meant to do
 * (it does not work)
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
}
