<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Framework\Content;

use Shopware\Core\Framework\Struct\Struct;

/**
 * @package Boxalino\RealTimeUserExperience\Framework\Content
 */
class BxAttributeElement extends Struct
{

    /**
     * @var \ArrayIterator
     */
    protected $bxAttributes;

    public function __construct($bxAttributes = null)
    {
        $this->bxAttributes = $bxAttributes ?? new \ArrayIterator();
    }

    /**
     * @return \ArrayIterator
     */
    public function get() : \ArrayIterator
    {
        return $this->bxAttributes;
    }

    /**
     * @param \ArrayIterator $attributes
     * @return $this
     */
    public function set(\ArrayIterator $attributes) : self
    {
        $this->bxAttributes = $attributes;
        return $this;
    }

}
