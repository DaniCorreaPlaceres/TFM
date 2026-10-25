<?php

declare(strict_types=1);

namespace App\Controller;

use App\Identity\ConfirmEmail;
use App\Identity\InvalidConfirmation;
use App\Identity\InvalidRegistration;
use App\Identity\RegisterAccount;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

final class RegistrationController extends AbstractController
{
    #[Route('/registro', name: 'account_register', methods: ['GET', 'POST'])]
    public function register(Request $request, RegisterAccount $register, CsrfTokenManagerInterface $csrf): Response
    {
        $email = '';
        $error = null;
        if ($request->isMethod('POST')) {
            $email = $request->request->getString('email');
            if (!$csrf->isTokenValid(new CsrfToken('registro', $request->request->getString('_token')))) {
                $error = 'El formulario ha caducado. Vuelve a enviarlo.';
            } else {
                try {
                    $register($email, $request->request->getString('password'));

                    return $this->redirectToRoute('account_register_sent');
                } catch (InvalidRegistration $exception) {
                    $error = self::message($exception->reason);
                }
            }
        }

        return $this->render('registro/form.html.twig', [
            'email' => $email,
            'error' => $error,
        ], new Response(status: null === $error ? Response::HTTP_OK : Response::HTTP_UNPROCESSABLE_ENTITY));
    }

    #[Route('/registro/enviado', name: 'account_register_sent', methods: ['GET'])]
    public function sent(): Response
    {
        return $this->render('registro/enviado.html.twig');
    }

    #[Route('/registro/confirmar/{token}', name: 'account_confirm', requirements: ['token' => '[A-Za-z0-9]+'], methods: ['GET'])]
    public function confirm(string $token, ConfirmEmail $confirm): Response
    {
        try {
            $confirm($token);
        } catch (InvalidConfirmation) {
            return $this->render('registro/confirmacion.html.twig', [
                'confirmed' => false,
            ], new Response(status: Response::HTTP_BAD_REQUEST));
        }

        return $this->render('registro/confirmacion.html.twig', [
            'confirmed' => true,
        ]);
    }

    private static function message(string $reason): string
    {
        return match ($reason) {
            InvalidRegistration::EMAIL => 'Escribe un correo válido.',
            InvalidRegistration::SHORT => 'La contraseña debe tener al menos 12 caracteres.',
            InvalidRegistration::PWNED => 'Esa contraseña aparece en filtraciones conocidas. Elige otra.',
            InvalidRegistration::UNAVAILABLE => 'No se ha podido comprobar si la contraseña está filtrada. Inténtalo de nuevo.',
            InvalidRegistration::DUPLICATE => 'Ya existe una cuenta con ese correo.',
            default => 'No se ha podido crear la cuenta.',
        };
    }
}
