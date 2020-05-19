<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Framework\Request;

use Boxalino\RealTimeUserExperience\Framework\SalesChannelContextTrait;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\Context\ListingContextInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\ParameterFactory;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

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
     * Via CMS it is allowed the following options for the category filter:
     * - root (use the sales channel navigation category id)
     * - navigation (use the category the CMS element is on)
     * - custom (will use the category ID configured in the categoryFilterList)
     *
     * @param Request $request
     * @return string
     */
    public function getContextNavigationId(Request $request): array
    {
        if($this->has('categoryFilter'))
        {
            if($this->getProperty("categoryFilter") == 'root')
            {
                return [$this->getSalesChannelContext()->getSalesChannel()->getNavigationCategoryId()];
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

}
