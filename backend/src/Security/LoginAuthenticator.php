<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\Account;
use App\Identity\AuthenticateAccount;
use App\Identity\InvalidLogin;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Twig\Environment;

final class LoginAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private readonly AuthenticateAccount $authenticate,
        private readonly CsrfTokenManagerInterface $csrf,
        private readonly Environment $twig,
    ) {
    }

    public function supports(Request $request): bool
    {
        return $request->isMethod('POST') && 'account_login' === $request->attributes->get('_route');
    }

    public function authenticate(Request $request): Passport
    {
        if (!$this->csrf->isTokenValid(new CsrfToken('entrar', $request->request->getString('_token')))) {
            throw new InvalidCsrfTokenException();
        }

        try {
            $account = ($this->authenticate)($request->request->getString('email'), $request->request->getString('password'));
        } catch (InvalidLogin $exception) {
            throw new CustomUserMessageAuthenticationException(self::message($exception->reason));
        }

        return new SelfValidatingPassport(
            new UserBadge($account->getUserIdentifier(), static fn (): Account => $account),
            [new CsrfTokenBadge('entrar', $request->request->getString('_token'))],
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): Response
    {
        $account = $token->getUser();
        if ($account instanceof Account) {
            $request->getSession()->set('credentials_changed_at', $account->sessionMarker());
        }

        return new RedirectResponse('/sesion');
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        $message = $exception instanceof InvalidCsrfTokenException
            ? 'El formulario ha caducado. Vuelve a enviarlo.'
            : $exception->getMessage();

        return new Response($this->twig->render('sesion/entrar.html.twig', [
            'email' => $request->request->getString('email'),
            'error' => $message,
            'updated' => false,
        ]), Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    private static function message(string $reason): string
    {
        return match ($reason) {
            InvalidLogin::UNVERIFIED => 'Confirma el correo antes de entrar. Revisa tu bandeja.',
            InvalidLogin::TOO_MANY => 'Demasiados intentos. Espera unos minutos y vuelve a probar.',
            default => 'El correo o la contraseña no son correctos.',
        };
    }
}
