<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Core\Content\Cms\Event;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Event\NestedEvent;

class ApiCmsEvent extends NestedEvent
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var Context
     */
    protected $context;

    public function __construct(string $id, Context $context)
    {
        $this->id = $id;
        $this->context = $context;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getContext(): Context
    {
        return $this->context;
    }


}
