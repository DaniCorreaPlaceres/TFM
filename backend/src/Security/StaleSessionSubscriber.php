<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\Account;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class StaleSessionSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly TokenStorageInterface $tokens)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['closeStaleSession', 6],
        ];
    }

    public function closeStaleSession(RequestEvent $event): void
    {
        if (!$event->isMainRequest() || $event->hasResponse() || !$event->getRequest()->hasPreviousSession()) {
            return;
        }

        $user = $this->tokens->getToken()?->getUser();
        if (!$user instanceof Account) {
            return;
        }

        $session = $event->getRequest()->getSession();
        if ($session->get('credentials_changed_at') === $user->sessionMarker()) {
            return;
        }

        $session->invalidate();
        $this->tokens->setToken(null);
        if ('/entrar' === $event->getRequest()->getPathInfo()) {
            return;
        }

        $event->setResponse(new RedirectResponse('/entrar'));
    }
}
