<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Framework\Request;

use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\Context\ItemContextInterface;
use PhpParser\Error;

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

}
