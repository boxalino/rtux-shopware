<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Framework\Content\Page;

use Boxalino\RealTimeUserExperienceApi\Framework\Content\CreateFromTrait;
use Boxalino\RealTimeUserExperience\Framework\Content\Listing\ApiCmsModel;
use Boxalino\RealTimeUserExperience\Framework\SalesChannelContextTrait;
use Boxalino\RealTimeUserExperienceApi\Framework\Content\Listing\ApiCmsModelInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\ApiCallServiceInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Response\Accessor\Block;
use Boxalino\RealTimeUserExperienceApi\Service\ErrorHandler\UndefinedPropertyError;
use Boxalino\RealTimeUserExperienceApi\Framework\Content\Page\ApiCmsLoaderAbstract;
use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Shopware\Core\Framework\Struct\Struct;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ApiCmsLoader
 * Sample based on a familiar ShopwarePageLoader component
 *
 * @package Boxalino\RealTimeUserExperience\Service\Api\Content\Page
 */
class ApiCmsLoader extends ApiCmsLoaderAbstract
{
    use SalesChannelContextTrait;

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
        return $this->getSalesChannelContext()->getSalesChannel()->getNavigationCategoryId();
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
