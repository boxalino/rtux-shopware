<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Framework\Request;

use Boxalino\RealTimeUserExperience\Framework\SalesChannelContextTrait;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\Context\AutocompleteContextInterface;
use PhpParser\Error;
use Boxalino\RealTimeUserExperienceApi\Framework\Request\AutocompleteContextAbstract as ApiAutocompleteContextAbstract;
/**
 * Autocomplete context request
 * Sets additional properties on the request definition
 * Adds a validation for the request definition instace
 *
 * @package Boxalino\RealTimeUserExperience\Framework\Request
 */
abstract class AutocompleteContextAbstract
    extends ApiAutocompleteContextAbstract
    implements AutocompleteContextInterface, ShopwareApiContextInterface
{
    use ContextTrait;
}
