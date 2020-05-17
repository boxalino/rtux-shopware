<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Service\Api;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class ApiCookieSubscriber
 * Sets the API cookies for tracking and content personalization
 *
 * @package Boxalino\RealTimeUserExperience\Service\Api
 */
class ApiCookieSubscriber implements EventSubscriberInterface
{
    public const BOXALINO_API_COOKIE_SESSION = "cems";
    public const BOXALINO_API_COOKIE_VISITOR = "cemv";

    public const BOXALINO_API_INIT_SESSION = "cems-init";
    public const BOXALINO_API_INIT_VISITOR = "cemv-init";

    public const VISITOR_COOKIE_TIME = 31536000;

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => [
                ['setApiCookie', -2500],
            ],
        ];
    }

    /**
     * @param ResponseEvent $event
     */
    public function setApiCookie(ResponseEvent $event): void
    {
        $response = $event->getResponse();
        $request = $event->getRequest();

        if($request->cookies->has(self::BOXALINO_API_INIT_SESSION))
        {
            $response->headers->setCookie(Cookie::create(self::BOXALINO_API_COOKIE_SESSION,
                $request->cookies->get(self::BOXALINO_API_INIT_SESSION), 0));
            $response->headers->removeCookie(self::BOXALINO_API_INIT_SESSION);
        }

        if($request->cookies->has(self::BOXALINO_API_INIT_VISITOR))
        {
            $response->headers->setCookie(Cookie::create(self::BOXALINO_API_COOKIE_VISITOR,
                $request->cookies->get(self::BOXALINO_API_INIT_VISITOR), time() + self::VISITOR_COOKIE_TIME ));
            $response->headers->removeCookie(self::BOXALINO_API_INIT_VISITOR);
        }
    }

}
