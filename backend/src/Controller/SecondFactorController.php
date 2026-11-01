<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Account;
use App\Identity\AccountRepository;
use App\Identity\AttemptLimiter;
use App\Identity\InvalidSecondFactor;
use App\Identity\ProvisioningQr;
use App\Identity\SecondFactor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SecondFactorController extends AbstractController
{
    public function __construct(
        private readonly SecondFactor $secondFactor,
        private readonly ProvisioningQr $qr,
        private readonly AccountRepository $accounts,
        private readonly Security $security,
        #[Autowire(service: 'App\Identity\LoginAttemptLimiter')]
        private readonly AttemptLimiter $attempts,
    ) {
    }

    #[Route('/entrar/codigo', name: 'account_login_code', methods: ['GET', 'POST'])]
    public function code(Request $request): Response
    {
        $account = $this->pendingAccount($request);
        if (!$account instanceof Account) {
            return $this->redirectToRoute('account_login');
        }

        if ($request->isMethod('POST')) {
            $error = $this->checkCode($request, $account, 'codigo', 'codigo:'.$account->id());
            if (null === $error) {
                $this->security->login($account);
                $session = $request->getSession();
                $session->remove('second_factor_account');
                $session->set('credentials_changed_at', $account->sessionMarker());

                return $this->redirectToRoute('account_session');
            }

            return $this->render('sesion/codigo.html.twig', [
                'error' => $error,
            ], new Response(status: Response::HTTP_UNPROCESSABLE_ENTITY));
        }

        return $this->render('sesion/codigo.html.twig', [
            'error' => null,
        ]);
    }

    #[Route('/sesion/segundo-factor', name: 'account_second_factor', methods: ['GET', 'POST'])]
    public function manage(Request $request): Response
    {
        $account = $this->currentAccount();
        if (!$account instanceof Account) {
            return $this->redirectToRoute('account_login');
        }

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('segundo-factor', $request->request->getString('_token'))) {
                return $this->setupOrStatus($request, $account, 'El formulario ha caducado. Vuelve a enviarlo.', Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            return match ($request->request->getString('accion')) {
                'activar' => $this->start($request, $account),
                'confirmar' => $this->confirm($request, $account),
                'desactivar' => $this->disable($request, $account),
                default => $this->setupOrStatus($request, $account, 'El formulario ha caducado. Vuelve a enviarlo.', Response::HTTP_UNPROCESSABLE_ENTITY),
            };
        }

        return $this->setupOrStatus($request, $account, null, Response::HTTP_OK);
    }

    #[Route('/sesion/segundo-factor/codigos', name: 'account_recovery_codes', methods: ['GET'])]
    public function codes(Request $request): Response
    {
        $account = $this->currentAccount();
        if (!$account instanceof Account) {
            return $this->redirectToRoute('account_login');
        }

        $stored = $request->getSession()->get('recovery_codes_once');
        $request->getSession()->remove('recovery_codes_once');
        $codes = [];
        if (is_array($stored)) {
            foreach ($stored as $code) {
                if (is_string($code)) {
                    $codes[] = $code;
                }
            }
        }

        return $this->render('sesion/codigos.html.twig', [
            'codes' => $codes,
        ]);
    }

    private function start(Request $request, Account $account): Response
    {
        if ($account->requiresSecondFactor()) {
            return $this->redirectToRoute('account_second_factor');
        }

        $request->getSession()->set('totp_setup_secret', base64_encode($this->secondFactor->newSecret()));

        return $this->redirectToRoute('account_second_factor');
    }

    private function confirm(Request $request, Account $account): Response
    {
        $secret = $this->setupSecret($request);
        if (null === $secret || $account->requiresSecondFactor()) {
            return $this->redirectToRoute('account_second_factor');
        }

        $key = 'alta:'.$account->id();
        if ($this->attempts->isBlocked($key)) {
            return $this->renderSetup($account, $secret, 'Demasiados intentos. Espera unos minutos y vuelve a probar.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $codes = $this->secondFactor->confirm($account, $secret, $request->request->getString('code'));
        } catch (InvalidSecondFactor) {
            $this->attempts->recordFailure($key);

            return $this->renderSetup($account, $secret, 'El código no es válido.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $session = $request->getSession();
        $session->remove('totp_setup_secret');
        $session->set('recovery_codes_once', $codes);

        return $this->redirectToRoute('account_recovery_codes');
    }

    private function disable(Request $request, Account $account): Response
    {
        if (!$account->requiresSecondFactor()) {
            return $this->redirectToRoute('account_second_factor');
        }

        $key = 'baja:'.$account->id();
        if ($this->attempts->isBlocked($key)) {
            return $this->renderStatus($account, 'Demasiados intentos. Espera unos minutos y vuelve a probar.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $this->secondFactor->disable($account, $request->request->getString('code'));
        } catch (InvalidSecondFactor) {
            $this->attempts->recordFailure($key);

            return $this->renderStatus($account, 'El código no es válido.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->redirectToRoute('account_session');
    }

    private function checkCode(Request $request, Account $account, string $csrfId, string $key): ?string
    {
        if (!$this->isCsrfTokenValid($csrfId, $request->request->getString('_token'))) {
            return 'El formulario ha caducado. Vuelve a enviarlo.';
        }

        if ($this->attempts->isBlocked($key)) {
            return 'Demasiados intentos. Espera unos minutos y vuelve a probar.';
        }

        try {
            $this->secondFactor->verify($account, $request->request->getString('code'));
        } catch (InvalidSecondFactor) {
            $this->attempts->recordFailure($key);

            return 'El código no es válido.';
        }

        return null;
    }

    private function pendingAccount(Request $request): ?Account
    {
        $id = $request->getSession()->get('second_factor_account');
        if (!is_string($id)) {
            return null;
        }

        $account = $this->accounts->findById($id);
        if (null === $account || !$account->requiresSecondFactor()) {
            $request->getSession()->remove('second_factor_account');

            return null;
        }

        return $account;
    }

    private function currentAccount(): ?Account
    {
        $account = $this->getUser();

        return $account instanceof Account ? $account : null;
    }

    private function setupSecret(Request $request): ?string
    {
        $encoded = $request->getSession()->get('totp_setup_secret');
        if (!is_string($encoded)) {
            return null;
        }

        $secret = base64_decode($encoded, true);

        return is_string($secret) && 20 === strlen($secret) ? $secret : null;
    }

    private function setupOrStatus(Request $request, Account $account, ?string $error, int $status): Response
    {
        $secret = $this->setupSecret($request);
        if (null !== $secret && !$account->requiresSecondFactor()) {
            return $this->renderSetup($account, $secret, $error, $status);
        }

        return $this->renderStatus($account, $error, $status);
    }

    private function renderSetup(Account $account, string $secret, ?string $error, int $status): Response
    {
        $provisioning = $this->secondFactor->provisioning($account->email(), $secret);

        return $this->render('sesion/segundo-factor-alta.html.twig', [
            'error' => $error,
            'provisioning' => $provisioning,
            'qr' => $this->qr->dataUri($provisioning['uri']),
        ], new Response(status: $status));
    }

    private function renderStatus(Account $account, ?string $error, int $status): Response
    {
        return $this->render('sesion/segundo-factor.html.twig', [
            'error' => $error,
            'enabled' => $account->requiresSecondFactor(),
        ], new Response(status: $status));
    }
}
