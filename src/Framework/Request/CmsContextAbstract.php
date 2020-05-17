<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Framework\Request;

use Boxalino\RealTimeUserExperience\Service\Api\Request\Context\ListingContextInterface;
use Boxalino\RealTimeUserExperience\Service\Api\Request\ParameterFactory;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

/**
 * Boxalino Cms Request handler
 * Allows to set the configurations from the Boxalino Narrative CMS block
 * (facets, filters, sidebar, etc_
 *
 * @package Boxalino\RealTimeUserExperience\Framework\Request
 */
abstract class CmsContextAbstract
    extends ListingContextAbstract
    implements ShopwareApiContextInterface, ListingContextInterface
{

    /**
     * @param Request $request
     * @return void
     */
    protected function addContextParameters(Request $request) : void
    {
        parent::addContextParameters($request);

        $params=$request->attributes->get('_route_params');
        if(isset($params['navigationId']))
        {
            $this->getApiRequest()->addHeaderParameters(
                $this->parameterFactory->get(ParameterFactory::BOXALINO_API_REQUEST_PARAMETER_TYPE_HEADER)
                    ->add("navigationId", $params['navigationId'])
            );
        }

        if($this->getProperty("sidebar"))
        {
            $this->getApiRequest()->addHeaderParameters(
                $this->parameterFactory->get(ParameterFactory::BOXALINO_API_REQUEST_PARAMETER_TYPE_HEADER)
                    ->add("position", "sidebar")
            );
        }
    }

    /**
     * Adding general filters
     * Add the category filter (if any)
     * Adding configured filters (if any)
     *
     * @param Request $request
     */
    protected function addFilters(Request $request) : void
    {
        $this->getApiRequest()
            ->setHitCount($this->getHitCount())
            ->addFilters(
                $this->parameterFactory->get(ParameterFactory::BOXALINO_API_REQUEST_PARAMETER_TYPE_FILTER)
                    ->addRange("products_visibility", $this->getContextVisibility(), 1000),
                $this->parameterFactory->get(ParameterFactory::BOXALINO_API_REQUEST_PARAMETER_TYPE_FILTER)
                    ->add("products_active", [1])
            );

        $categoryIds = $this->getContextNavigationId($request, $this->salesChannelContext);
        if(!empty($categoryIds))
        {
            $this->getApiRequest()->addFilters(
                $this->parameterFactory->get(ParameterFactory::BOXALINO_API_REQUEST_PARAMETER_TYPE_FILTER)
                    ->add("category_id", $categoryIds)
            );
        }

        if($this->has("filters"))
        {
            $configuredFilters = explode(",", $this->getProperty("filters"));
            foreach($configuredFilters as $filter)
            {
                $params = explode("=", $filter);
                $this->getApiRequest()->addFilters(
                    $this->parameterFactory->get(ParameterFactory::BOXALINO_API_REQUEST_PARAMETER_TYPE_FILTER)
                        ->add($params[0], array_map("html_entity_decode",  explode("|", $params[1])))
                );
            }
        }
    }

    /**
     * Adding the requested facets (if allowed)
     * Adding configured facets (if any)
     *
     * @param Request $request
     * @return SearchContextAbstract
     */
    public function addFacets(Request $request): ListingContextAbstract
    {
        if($this->getProperty("applyRequestParams"))
        {
            return parent::addFacets($request);
        }

        if($this->has("facets"))
        {
            $configuredFacets = explode(",", $this->getProperty("facets"));
            foreach($configuredFacets as $facet)
            {
                $params = explode("=", $facet);
                if (in_array($params[0], array_keys($this->getRangeProperties())))
                {
                    continue;
                }
                $this->getApiRequest()->addFacets(
                    $this->parameterFactory->get(ParameterFactory::BOXALINO_API_REQUEST_PARAMETER_TYPE_FACET)
                        ->addWithValues($params[0], array_map("html_entity_decode",  explode("|", $params[1])))
                );
            }
        }

        return $this;
    }

    /**
     * Via CMS it is allowed the following options for the category filter:
     * - root (use the sales channel navigation category id)
     * - navigation (use the category the CMS element is on)
     * - custom (will use the category ID configured in the categoryFilterList)
     *
     * @param Request $request
     * @param SalesChannelContext $salesChannelContext
     * @return string
     */
    public function getContextNavigationId(Request $request, SalesChannelContext $salesChannelContext): array
    {
        if($this->has('categoryFilter'))
        {
            if($this->getProperty("categoryFilter") == 'root')
            {
                return [$salesChannelContext->getSalesChannel()->getNavigationCategoryId()];
            }

            if($this->getProperty('categoryFilter') == 'navigation')
            {
                $params = $request->attributes->get('_route_params');
                if ($params && isset($params['navigationId']))
                {
                    return [$params['navigationId']];
                }
            }
        }

        if($this->has("categoryFilterList"))
        {
            return $this->getProperty('categoryFilterList');
        }

        return [];
    }

    /**
     * If there are configured returnFields in the CMS element - they will be used
     *
     * @return array
     */
    public function getReturnFields() : array
    {
        if($this->has("returnFields"))
        {
            return $this->getProperty("returnFields");
        }

        return ["id", "products_group_id"];
    }

    /**
     * If there is configured hitCount in the CMS element - they will be used
     *
     * @return int
     */
    public function getHitCount() : int
    {
        if($this->has("hitCount"))
        {
            return (int) $this->getProperty("hitCount");
        }

        return parent::getHitCount();
    }

}
