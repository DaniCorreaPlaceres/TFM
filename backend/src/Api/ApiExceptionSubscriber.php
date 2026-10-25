<?php

namespace App\Api;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Uid\Uuid;

final class ApiExceptionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onException', 10],
        ];
    }

    public function onException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();
        if (!str_starts_with($request->getPathInfo(), '/api/')) {
            return;
        }

        $status = $event->getThrowable() instanceof HttpExceptionInterface
            ? $event->getThrowable()->getStatusCode()
            : 500;
        $requestId = $request->attributes->get('request_id');
        if (!\is_string($requestId) || '' === $requestId) {
            $requestId = Uuid::v4()->toRfc4122();
            $request->attributes->set('request_id', $requestId);
        }

        [$title, $detail] = self::messageFor($status);
        $response = new JsonResponse([
            'type' => 'about:blank',
            'title' => $title,
            'status' => $status,
            'detail' => $detail,
            'requestId' => $requestId,
        ], $status);
        $response->headers->set('Content-Type', 'application/problem+json');
        $event->setResponse($response);
    }

    /**
     * @return array{0: string, 1: string}
     */
    private static function messageFor(int $status): array
    {
        return match ($status) {
            404 => ['No encontrado', 'No se encontró el recurso.'],
            405 => ['Método no permitido', 'El método no está permitido.'],
            default => ['Error interno', 'No se pudo completar la solicitud.'],
        };
    }
}
