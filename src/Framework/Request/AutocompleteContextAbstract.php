<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Framework\Request;

use Boxalino\RealTimeUserExperience\Service\Api\Request\Context\AutocompleteContextInterface;
use Boxalino\RealTimeUserExperience\Service\Api\Request\Definition\AutocompleteRequestDefinitionInterface;
use Boxalino\RealTimeUserExperience\Service\Api\Request\RequestDefinitionInterface;
use Boxalino\RealTimeUserExperience\Service\ErrorHandler\WrongDependencyTypeException;
use PhpParser\Error;
use Symfony\Component\HttpFoundation\Request;

/**
 * Autocomplete context request
 * Sets additional properties on the request definition
 * Adds a validation for the request definition instace
 *
 * @package Boxalino\RealTimeUserExperience\Framework\Request
 */
abstract class AutocompleteContextAbstract
    extends ContextAbstract
    implements AutocompleteContextInterface, ShopwareApiContextInterface
{

    /**
     * @var int
     */
    protected $suggestionCount = 0;

    /**
     * @var bool
     */
    protected $highlight = null;

    /**
     * @var string
     */
    protected $highlightPrefix = null;

    /**
     * @var string
     */
    protected $highlightPostfix = null;

    /**
     * Adding autocomplete specific request parameters
     *
     * @param Request $request
     * @return RequestDefinitionInterface
     */
    public function get(Request $request) : RequestDefinitionInterface
    {
        parent::get($request);
        $this->getApiRequest()
            ->setAcQueriesHitCount($this->getSuggestionsCount())
            ->setHitCount($this->getHitCount());

        if(!is_null($this->highlightPostfix))
        {
            $this->getApiRequest()->setAcHighlightPost($this->getHighlightPostfix());
        }

        if(!is_null($this->highlightPrefix))
        {
            $this->getApiRequest()->setAcHighlightPre($this->getHighlightPrefix());
        }

        if(!is_null($this->highlight))
        {
            $this->getApiRequest()->setAcHighlight($this->isAcHighlight());
        }

        return $this->getApiRequest();
    }

    /**
     * Set the number of textual suggestions returned as part of the autocomplete response
     *
     * @param int $count
     * @return $this|ContextAbstract
     */
    public function setSuggestionCount(int $count) : ContextAbstract
    {
        $this->suggestionCount = $count;
        return $this;
    }

    /**
     * @return int
     */
    public function getSuggestionsCount() : int
    {
        return $this->suggestionCount;
    }

    /**
     * @return bool
     */
    public function isAcHighlight(): bool
    {
        return $this->highlight;
    }

    /**
     * @param bool $acHighlight
     * @return AutocompleteContextAbstract
     */
    public function setHighlight(bool $acHighlight): AutocompleteContextAbstract
    {
        $this->highlight = $acHighlight;
        return $this;
    }

    /**
     * @return string
     */
    public function getHighlightPrefix(): string
    {
        return $this->highlightPrefix;
    }

    /**
     * @param string $acHighlightPre
     * @return AutocompleteContextAbstract
     */
    public function setHighlightPrefix(string $acHighlightPre): AutocompleteContextAbstract
    {
        $this->highlightPrefix = $acHighlightPre;
        return $this;
    }

    /**
     * @return string
     */
    public function getHighlightPostfix(): string
    {
        return $this->highlightPostfix;
    }

    /**
     * @param string $acHighlightPost
     * @return AutocompleteContextAbstract
     */
    public function setHighlightPostfix(string $acHighlightPost): AutocompleteContextAbstract
    {
        $this->highlightPostfix = $acHighlightPost;
        return $this;
    }

    /**
     * Enforce a dependency type for the AutocompleteContext requests
     *
     * @param RequestDefinitionInterface $requestDefinition
     * @return self | Error
     */
    public function setRequestDefinition(RequestDefinitionInterface $requestDefinition)
    {
        if($requestDefinition instanceof AutocompleteRequestDefinitionInterface)
        {
            return parent::setRequestDefinition($requestDefinition);
        }

        throw new WrongDependencyTypeException("BoxalinoAPIContext: " . get_called_class() .
            " request definition must be an instance of AutocompleteRequestDefinitionInterface, "
            . get_class($requestDefinition) . " provided");
    }

}
