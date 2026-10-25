<?php

declare(strict_types=1);

namespace App\Controller;

use App\Identity\InvalidPasswordReset;
use App\Identity\InvalidRegistration;
use App\Identity\RequestPasswordReset;
use App\Identity\ResetPassword;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

final class PasswordResetController extends AbstractController
{
    #[Route('/recuperar', name: 'account_recover', methods: ['GET', 'POST'])]
    public function request(Request $request, RequestPasswordReset $recover, CsrfTokenManagerInterface $csrf): Response
    {
        $error = null;
        if ($request->isMethod('POST')) {
            if (!$csrf->isTokenValid(new CsrfToken('recuperar', $request->request->getString('_token')))) {
                $error = 'El formulario ha caducado. Vuelve a enviarlo.';
            } else {
                $recover($request->request->getString('email'));

                return $this->redirectToRoute('account_recover_sent');
            }
        }

        return $this->render('sesion/recuperar.html.twig', [
            'error' => $error,
        ], new Response(status: null === $error ? Response::HTTP_OK : Response::HTTP_UNPROCESSABLE_ENTITY));
    }

    #[Route('/recuperar/enviado', name: 'account_recover_sent', methods: ['GET'])]
    public function sent(): Response
    {
        return $this->render('sesion/recuperar-enviado.html.twig');
    }

    #[Route('/recuperar/clave/{token}', name: 'account_password_reset', requirements: ['token' => '[A-Za-z0-9]{64}'], methods: ['GET', 'POST'])]
    public function reset(string $token, Request $request, ResetPassword $reset, CsrfTokenManagerInterface $csrf): Response
    {
        if (!$reset->usable($token)) {
            return $this->invalid();
        }

        $error = null;
        if ($request->isMethod('POST')) {
            if (!$csrf->isTokenValid(new CsrfToken('nueva-clave', $request->request->getString('_token')))) {
                $error = 'El formulario ha caducado. Vuelve a enviarlo.';
            } else {
                try {
                    $reset($token, $request->request->getString('password'));

                    return $this->redirectToRoute('account_login', ['actualizada' => 1]);
                } catch (InvalidPasswordReset) {
                    return $this->invalid();
                } catch (InvalidRegistration $exception) {
                    $error = self::message($exception->reason);
                }
            }
        }

        return $this->render('sesion/nueva-clave.html.twig', [
            'token' => $token,
            'error' => $error,
        ], new Response(status: null === $error ? Response::HTTP_OK : Response::HTTP_UNPROCESSABLE_ENTITY));
    }

    private function invalid(): Response
    {
        return $this->render('sesion/enlace.html.twig', [], new Response(status: Response::HTTP_BAD_REQUEST));
    }

    private static function message(string $reason): string
    {
        return match ($reason) {
            InvalidRegistration::SHORT => 'La contraseña debe tener al menos 12 caracteres.',
            InvalidRegistration::PWNED => 'Esa contraseña aparece en filtraciones conocidas. Elige otra.',
            InvalidRegistration::UNAVAILABLE => 'No se ha podido comprobar si la contraseña está filtrada. Inténtalo de nuevo.',
            default => 'No se ha podido cambiar la contraseña.',
        };
    }
}
