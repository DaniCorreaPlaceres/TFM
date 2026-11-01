<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Account;
use App\Identity\ActiveFiscalEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SessionController extends AbstractController
{
    public function __construct(private readonly ActiveFiscalEntity $active)
    {
    }

    #[Route('/entrar', name: 'account_login', methods: ['GET', 'POST'])]
    public function login(Request $request): Response
    {
        return $this->render('sesion/entrar.html.twig', [
            'email' => '',
            'error' => null,
            'updated' => $request->query->getBoolean('actualizada'),
        ]);
    }

    #[Route('/sesion', name: 'account_session', methods: ['GET'])]
    public function show(Request $request): Response
    {
        $account = $this->getUser();
        if (!$account instanceof Account) {
            return $this->redirectToRoute('account_login');
        }

        $fiscalEntity = $this->active->resolve($account, $request);

        return $this->render('sesion/dentro.html.twig', [
            'email' => $account->email(),
            'secondFactor' => $account->requiresSecondFactor(),
            'entityName' => $fiscalEntity?->legalName(),
        ]);
    }

    #[Route('/salir', name: 'account_logout', methods: ['POST'])]
    public function logout(): never
    {
        throw new \LogicException('El cierre de sesión lo resuelve el firewall.');
    }
}
