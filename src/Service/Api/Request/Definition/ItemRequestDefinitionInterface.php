<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Service\Api\Request\Definition;

use Boxalino\RealTimeUserExperience\Service\Api\Request\Parameter\ItemDefinition;
use Boxalino\RealTimeUserExperience\Service\Api\Request\RequestDefinitionInterface;
use Boxalino\RealTimeUserExperience\Service\Api\Request\RequestDefinition;

/**
 * Boxalino API Request definition interface for item context requests
 * (ex: recomendations on PDP, basket, blog articles, etc)
 *
 * @package Boxalino\RealTimeUserExperience\Service\Api\Request
 */
interface ItemRequestDefinitionInterface extends RequestDefinitionInterface
{
    /**
     * @param ItemDefinition ...$itemDefinitions
     * @return RequestDefinition
     */
    public function addItems(ItemDefinition ...$itemDefinitions) : ItemRequestDefinition;

}
