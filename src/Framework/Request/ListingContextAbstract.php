<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Framework\Request;

use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\Context\ListingContextInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\Definition\ListingRequestDefinitionInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\ParameterFactory;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\RequestDefinitionInterface;
use Boxalino\RealTimeUserExperienceApi\Service\ErrorHandler\WrongDependencyTypeException;
use PhpParser\Error;
use Symfony\Component\HttpFoundation\Request;

/**
 * Boxalino Listing Request handler
 * Allows to set the nr of subphrases and products returned on each subphrase hit
 *
 * @package Boxalino\RealTimeUserExperience\Framework\Request
 */
abstract class ListingContextAbstract
    extends \Boxalino\RealTimeUserExperienceApi\Framework\Request\ListingContextAbstract
    implements ShopwareApiContextInterface, ListingContextInterface
{
    use ContextTrait;

}
