<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Framework\Cookie;

use Boxalino\RealTimeUserExperienceApi\Service\Api\ApiCookieSubscriber;
use Shopware\Storefront\Framework\Cookie\CookieProviderInterface;

class RtuxCookieProvider implements CookieProviderInterface
{
    /**
     * @var CookieProviderInterface
     */
    private $inner;

    public function __construct(CookieProviderInterface $inner)
    {
        $this->inner = $inner;
    }

    private const cookieGroup = [
        'snippet_name' => 'Interaction Optimization and Analytics',
        'snippet_description' => 'Allows defining individual next-best actions for your experience and real-time personalization of displayed content',
        'entries' => [
            [
                'snippet_name' => 'Visitor Analytics',
                'cookie' => ApiCookieSubscriber::BOXALINO_API_COOKIE_VISITOR,
                'snippet_description' => 'This is visitor ID. Every time you come on the e-shop, we will recommend content just for you.'
            ],
            [
                'snippet_name' => 'Session Analytics',
                'cookie' => ApiCookieSubscriber::BOXALINO_API_COOKIE_SESSION,
                'snippet_description' => 'This is the current user session. Every time you come on the e-shop, we will recommend content just for you.'
            ]
        ],
    ];

    public function getCookieGroups(): array
    {
        return array_merge(
            $this->inner->getCookieGroups(),
            [
                self::cookieGroup
            ]
        );
    }


}
