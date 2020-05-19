<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Framework\Content\Page;

use Boxalino\RealTimeUserExperience\Framework\Content\Listing\ApiCmsModel;
use Boxalino\RealTimeUserExperienceApi\Framework\Content\Listing\ApiCmsModelInterface;
use Boxalino\RealTimeUserExperienceApi\Framework\Content\Page\ApiCmsLoaderAbstract;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ApiCmsLoader
 * Sample based on a familiar ShopwarePageLoader component
 *
 * @package Boxalino\RealTimeUserExperience\Service\Api\Content\Page
 */
class ApiCmsLoader extends ApiCmsLoaderAbstract
{
    use ApiLoaderTrait;

    /**
     * @return ApiCmsModelInterface
     */
    public function getCmsPage(): ApiCmsModelInterface
    {
        return new ApiCmsModel();
    }

    /**
     * @param Request $request
     * @return string
     */
    protected function getNavigationId(Request $request): string
    {
        return $request->get("navigationId", $this->getSalesChannelContext()->getSalesChannel()->getNavigationCategoryId());
    }

    /**
     * The CMS configuration in Shopware are not as key=>value
     * @param array $config
     */
    public function setCmsConfig(array $config)
    {
        foreach($config as $key=>$configuration)
        {
            $this->cmsConfig[$key] = $configuration['value'];
        }

        return $this;
    }

}
