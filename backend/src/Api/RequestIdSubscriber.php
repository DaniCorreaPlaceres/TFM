<?php

namespace App\Api;

use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Uid\Uuid;

#[WithMonologChannel('api')]
final class RequestIdSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onRequest', 100],
            KernelEvents::RESPONSE => 'onResponse',
        ];
    }

    public function onRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest() || !self::isApiRequest($event)) {
            return;
        }

        $requestId = Uuid::v4()->toRfc4122();
        $request = $event->getRequest();
        $request->attributes->set('request_id', $requestId);
        $this->logger->info('Solicitud API.', [
            'requestId' => $requestId,
            'path' => $request->getPathInfo(),
        ]);
    }

    public function onResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $requestId = $event->getRequest()->attributes->get('request_id');
        if (!\is_string($requestId) || '' === $requestId) {
            return;
        }

        $event->getResponse()->headers->set('X-Request-Id', $requestId);
    }

    private static function isApiRequest(RequestEvent $event): bool
    {
        return str_starts_with($event->getRequest()->getPathInfo(), '/api/');
    }
}
