<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\EventListener;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SessionListener implements EventSubscriberInterface
{
    private $loginPath;

    public function __construct($loginPath)
    {
        $this->loginPath = $loginPath;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        $request = $event->getRequest();
        if (!$request->hasSession() || $request->getPathInfo() != $this->loginPath) {
            return;
        }

        $session = $request->getSession();
        $request->cookies->set($session->getName(), $session->getId());
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => array('onKernelRequest', 127),
        ];
    }
}
