<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Framework\Request;

use Boxalino\RealTimeUserExperience\Framework\Content\Listing\ApiFacetModel;
use Boxalino\RealTimeUserExperience\Service\Api\Request\Context\ListingContextInterface;
use Boxalino\RealTimeUserExperience\Service\Api\Request\Context\SearchContextInterface;
use Boxalino\RealTimeUserExperience\Service\Api\Request\Definition\SearchRequestDefinitionInterface;
use Boxalino\RealTimeUserExperience\Service\Api\Request\ParameterFactory;
use Boxalino\RealTimeUserExperience\Service\Api\Request\RequestDefinitionInterface;
use Boxalino\RealTimeUserExperience\Service\ErrorHandler\WrongDependencyTypeException;
use PhpParser\Error;
use Symfony\Component\HttpFoundation\Request;

/**
 * Boxalino Search Request handler
 * Allows to set the nr of subphrases and products returned on each subphrase hit
 *
 * @package Boxalino\RealTimeUserExperience\Framework\Request
 */
abstract class SearchContextAbstract
    extends ListingContextAbstract
    implements SearchContextInterface, ShopwareApiContextInterface, ListingContextInterface
{

    /**
     * @var null | int
     */
    protected $subPhrasesCount;

    /**
     * @var null | int
     */
    protected $subPhrasesProductsCount;

    /**
     * Adding additional subphrases details for the search request
     *
     * @param Request $request
     * @return RequestDefinitionInterface
     */
    public function get(Request $request) : RequestDefinitionInterface
    {
        parent::get($request);

        if(!is_null($this->subPhrasesCount))
        {
            $this->getApiRequest()->setMaxSubPhrases($this->getSubPhrasesCount());
        }

        if(!is_null($this->subPhrasesProductsCount))
        {
            $this->getApiRequest()->setMaxSubPhrasesHits($this->getSubPhrasesProductsCount());
        }

        return $this->getApiRequest();
    }

    /**
     * @param Request $request
     * @return void
     */
    protected function addContextParameters(Request $request) : void
    {
        parent::addContextParameters($request);
        $this->getApiRequest()->addHeaderParameters(
            $this->parameterFactory->get(ParameterFactory::BOXALINO_API_REQUEST_PARAMETER_TYPE_HEADER)
                ->add("search", $request->get("search", ""))
        );
    }

    /**
     * @return int|null
     */
    public function getSubPhrasesCount(): ?int
    {
        return $this->subPhrasesCount;
    }

    /**
     * @param int $subPhrasesCount
     * @return SearchContextAbstract
     */
    public function setSubPhrasesCount(int $subPhrasesCount): SearchContextAbstract
    {
        $this->subPhrasesCount = $subPhrasesCount;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getSubPhrasesProductsCount(): ?int
    {
        return $this->subPhrasesProductsCount;
    }

    /**
     * @param int $subPhrasesProductsCount
     * @return SearchContextAbstract
     */
    public function setSubPhrasesProductsCount(int $subPhrasesProductsCount): SearchContextAbstract
    {
        $this->subPhrasesProductsCount = $subPhrasesProductsCount;
        return $this;
    }

    /**
     * Enforce a dependency type for the SearchContext requests
     *
     * @param RequestDefinitionInterface $requestDefinition
     * @return self | Error
     */
    public function setRequestDefinition(RequestDefinitionInterface $requestDefinition)
    {
        if($requestDefinition instanceof SearchRequestDefinitionInterface)
        {
            return parent::setRequestDefinition($requestDefinition);
        }

        throw new WrongDependencyTypeException("BoxalinoAPIContext: " . get_called_class() .
            " request definition must be an instance of SearchRequestDefinitionInterface");
    }

}
