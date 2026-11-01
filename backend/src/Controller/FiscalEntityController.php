<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Account;
use App\Identity\ActiveFiscalEntity;
use App\Identity\CreateFiscalEntity;
use App\Identity\FiscalEntityMembershipRepository;
use App\Identity\InvalidFiscalEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class FiscalEntityController extends AbstractController
{
    public function __construct(
        private readonly CreateFiscalEntity $create,
        private readonly ActiveFiscalEntity $active,
        private readonly FiscalEntityMembershipRepository $memberships,
    ) {
    }

    #[Route('/entidades', name: 'fiscal_entities', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        $account = $this->currentAccount();
        if (!$account instanceof Account) {
            return $this->redirectToRoute('account_login');
        }

        $error = null;
        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('entidad', $request->request->getString('_token'))) {
                $error = 'El formulario ha caducado. Vuelve a enviarlo.';
            } else {
                try {
                    $fiscalEntity = ($this->create)(
                        $account,
                        $request->request->getString('legal_name'),
                        $request->request->getString('tax_id'),
                    );
                    $request->getSession()->set('fiscal_entity_id', $fiscalEntity->id());

                    return $this->redirectToRoute('account_session');
                } catch (InvalidFiscalEntity $exception) {
                    $error = InvalidFiscalEntity::NAME === $exception->reason
                        ? 'El nombre es obligatorio y no puede pasar de 180 caracteres.'
                        : 'El NIF no es válido.';
                }
            }
        }

        $current = $this->active->resolve($account, $request);

        return $this->render('entidad/lista.html.twig', [
            'error' => $error,
            'legalName' => $request->request->getString('legal_name'),
            'taxId' => $request->request->getString('tax_id'),
            'memberships' => $this->memberships->findForAccount($account),
            'activeId' => $current?->id(),
        ], new Response(status: null === $error ? Response::HTTP_OK : Response::HTTP_UNPROCESSABLE_ENTITY));
    }

    #[Route('/entidades/{id}/elegir', name: 'fiscal_entity_select', methods: ['POST'], requirements: ['id' => '[0-9a-fA-F-]{36}'])]
    public function select(Request $request, string $id): Response
    {
        $account = $this->currentAccount();
        if (!$account instanceof Account) {
            return $this->redirectToRoute('account_login');
        }

        if (!$this->isCsrfTokenValid('elegir-entidad', $request->request->getString('_token'))) {
            return $this->redirectToRoute('fiscal_entities');
        }

        if (!$this->active->select($account, $request, $id)) {
            throw $this->createNotFoundException('Entidad no encontrada.');
        }

        return $this->redirectToRoute('account_session');
    }

    private function currentAccount(): ?Account
    {
        $account = $this->getUser();

        return $account instanceof Account ? $account : null;
    }
}
