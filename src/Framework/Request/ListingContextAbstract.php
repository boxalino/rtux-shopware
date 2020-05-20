<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Framework\Request;

use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\Context\ListingContextInterface;
use \Boxalino\RealTimeUserExperienceApi\Framework\Request\ListingContextAbstract as ApiListingContextAbstract;
/**
 * Boxalino Listing Request handler
 * Allows to set the nr of subphrases and products returned on each subphrase hit
 *
 * @package Boxalino\RealTimeUserExperience\Framework\Request
 */
abstract class ListingContextAbstract
    extends ApiListingContextAbstract
    implements ShopwareApiContextInterface, ListingContextInterface
{
    use ContextTrait;

}
