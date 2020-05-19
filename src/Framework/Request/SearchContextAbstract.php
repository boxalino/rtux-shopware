<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Framework\Request;

use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\Context\ListingContextInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\Context\SearchContextInterface;
use PhpParser\Error;

/**
 * Boxalino Search Request handler
 * Allows to set the nr of subphrases and products returned on each subphrase hit
 *
 * @package Boxalino\RealTimeUserExperience\Framework\Request
 */
abstract class SearchContextAbstract
    extends \Boxalino\RealTimeUserExperienceApi\Framework\Request\SearchContextAbstract
    implements SearchContextInterface, ShopwareApiContextInterface, ListingContextInterface
{
    use ContextTrait;
    use RequestParametersTrait;

}
