<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Framework\Content\Page;

use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Page\PageLoadedEvent;
use Symfony\Component\HttpFoundation\Request;

class ApiPageLoadedEvent extends PageLoadedEvent
{
    /**
     * @var ApiResponsePage
     */
    protected $page;

    public function __construct(ApiResponsePage $page, SalesChannelContext $salesChannelContext, Request $request)
    {
        $this->page = $page;
        parent::__construct($salesChannelContext, $request);
    }

    public function getPage(): ApiResponsePage
    {
        return $this->page;
    }

}
