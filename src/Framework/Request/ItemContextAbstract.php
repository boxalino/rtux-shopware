<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Framework\Request;

use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\Context\ItemContextInterface;

/**
 * Item context request
 * Can be used for CrossSelling, basket, blog recommendations
 * and other contexts where the response is item context-based
 *
 * Generally the item context requires a product/blog id as an item context
 * set on the API request
 *
 * @package Boxalino\RealTimeUserExperience\Framework\Request
 */
abstract class ItemContextAbstract
    extends \Boxalino\RealTimeUserExperienceApi\Framework\Request\ItemContextAbstract
    implements ItemContextInterface, ShopwareApiContextInterface
{
    use ContextTrait;

    /**
     * @var bool
     */
    protected $isAjax = false;

    /**
     * @return bool
     */
    public function isAjax(): bool
    {
        return $this->isAjax;
    }

    /**
     * @param bool $isAjax
     * @return ItemContextInterface
     */
    public function setIsAjax(bool $isAjax): ItemContextInterface
    {
        $this->isAjax = $isAjax;
        return $this;
    }


}
