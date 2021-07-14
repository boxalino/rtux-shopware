<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Framework\Request;

use Boxalino\RealTimeUserExperience\Framework\FilterablePropertyTrait;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\Context\ListingContextInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\ParameterFactoryInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\RequestDefinitionInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\RequestInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\RequestTransformerInterface;
use Doctrine\DBAL\Connection;

/**
 * Boxalino Cms Request handler
 *
 * Allows to set the configurations from the Boxalino Narrative CMS block
 * (facets, filters, sidebar, etc_
 *
 * @package Boxalino\RealTimeUserExperience\Framework\Request
 */
abstract class CmsContextAbstract
    extends \Boxalino\RealTimeUserExperienceApi\Framework\Request\CmsContextAbstract
    implements ShopwareApiContextInterface, ListingContextInterface
{
    use ContextTrait;
    use FilterablePropertyTrait;

    /**
     * CmsContextAbstract constructor.
     *
     * @param RequestTransformerInterface $requestTransformer
     * @param ParameterFactoryInterface $parameterFactory
     * @param Connection $connection
     */
    public function __construct(
        RequestTransformerInterface $requestTransformer,
        ParameterFactoryInterface $parameterFactory,
        Connection $connection
    ) {
        parent::__construct($requestTransformer, $parameterFactory);
        $this->connection = $connection;
    }

    /**
     * Adding a new step to set all filterable properties to request
     *
     * @param RequestInterface $request
     * @return RequestDefinitionInterface
     */
    public function get(RequestInterface $request) : RequestDefinitionInterface
    {
        parent::get($request);
        $this->addStoreFilterableProperties($request);

        return $this->getApiRequest();
    }

    /**
     * @param $request
     */
    public function addStoreFilterableProperties($request) : void
    {
        if($this->getProperty("addStoreFilterableProperties"))
        {
            $storeFilterableProperties = $this->getStoreFilterablePropertiesByRequest($request);
            foreach ($storeFilterableProperties as $propertyName) {
                $this->getApiRequest()
                    ->addFacets(
                        $this->parameterFactory->get(ParameterFactoryInterface::BOXALINO_API_REQUEST_PARAMETER_TYPE_FACET)
                            ->add(html_entity_decode($propertyName), -1, 1)
                    );
            }
        }
    }

    /**
     * @param RequestInterface $request
     * @return void
     */
    protected function addContextParameters(RequestInterface $request) : void
    {
        parent::addContextParameters($request);

        $params = $request->getRequest()->attributes->get('_route_params');
        if(isset($params['navigationId']))
        {
            $this->getApiRequest()->addHeaderParameters(
                $this->parameterFactory->get(ParameterFactoryInterface::BOXALINO_API_REQUEST_PARAMETER_TYPE_HEADER)
                    ->add("navigationId", $params['navigationId'])
            );
        }

        if($this->getProperty("sidebar"))
        {
            $this->getApiRequest()->addHeaderParameters(
                $this->parameterFactory->get(ParameterFactoryInterface::BOXALINO_API_REQUEST_PARAMETER_TYPE_HEADER)
                    ->add("position", "sidebar")
            );
        }

        if($this->has("contextParametersList"))
        {
            $configuredContextParameters = explode(",", $this->getProperty("contextParametersList"));
            foreach($configuredContextParameters as $contextParameter)
            {
                $params = explode("=", $contextParameter);
                $values = $params[1];
                if(strpos($params[1], "|"))
                {
                    $values = array_map("html_entity_decode",  explode("|", $params[1]));
                }

                if(is_array($values))
                {
                    $this->getApiRequest()->addParameters(
                        $this->parameterFactory->get(ParameterFactoryInterface::BOXALINO_API_REQUEST_PARAMETER_TYPE_USER)
                            ->add($params[0], $values)
                    );

                    continue;
                }

                $this->getApiRequest()->addHeaderParameters(
                    $this->parameterFactory->get(ParameterFactoryInterface::BOXALINO_API_REQUEST_PARAMETER_TYPE_HEADER)
                        ->add($params[0], $values)
                );
            }
        }
    }

    /**
     * Via CMS it is allowed the following options for the category filter:
     * - root (use the sales channel navigation category id)
     * - navigation (use the category the CMS element is on)
     * - custom (will use the category ID configured in the categoryFilterList)
     *
     * @param RequestInterface $request
     * @return string
     */
    public function getContextNavigationId(RequestInterface $request): array
    {
        if($this->has('categoryFilter'))
        {
            if($this->getProperty("categoryFilter") == 'root')
            {
                return [$this->getSalesChannelContext()->getSalesChannel()->getNavigationCategoryId()];
            }

            if($this->getProperty('categoryFilter') == 'navigation')
            {
                $params = $request->getRequest()->attributes->get('_route_params');
                if ($params && isset($params['navigationId']))
                {
                    return [$params['navigationId']];
                }
            }
        }

        if($this->has("categoryFilterList"))
        {
            $configuredCategoryIds = $this->getProperty('categoryFilterList');
            return explode(",", $configuredCategoryIds);
        }

        return [];
    }

    
}
