<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Service\Api\Request\Definition;

use Boxalino\RealTimeUserExperience\Service\Api\Request\RequestDefinitionInterface;

/**
 * Boxalino API Request definition interface
 *
 * @package Boxalino\RealTimeUserExperience\Service\Api\Request
 */
interface AutocompleteRequestDefinitionInterface extends RequestDefinitionInterface
{
    /**
     * @param bool $highlight
     * @return mixed
     */
    public function setAcHighlight(bool $highlight) : AutocompleteRequestDefinition;

    /**
     * @param string $pre
     * @return mixed
     */
    public function setAcHighlightPre(string $pre) : AutocompleteRequestDefinition;

    /**
     * @param string $post
     * @return mixed
     */
    public function setAcHighlightPost(string $post) : AutocompleteRequestDefinition;

    /**
     * @param int $hitCount
     * @return mixed
     */
    public function setAcQueriesHitCount(int $hitCount) : AutocompleteRequestDefinition;

}
