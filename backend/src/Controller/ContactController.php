<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Account;
use App\Entity\FiscalEntity;
use App\Identity\ActiveFiscalEntity;
use App\Income\ContactRepository;
use App\Income\IncomeMessages;
use App\Income\InvalidIncome;
use App\Income\SaveContact;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ContactController extends AbstractController
{
    public function __construct(
        private readonly ActiveFiscalEntity $active,
        private readonly SaveContact $save,
        private readonly ContactRepository $contacts,
    ) {
    }

    #[Route('/contactos', name: 'contacts', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        $account = $this->currentAccount();
        if (!$account instanceof Account) {
            return $this->redirectToRoute('account_login');
        }
        $fiscalEntity = $this->active->resolve($account, $request);
        if (null === $fiscalEntity) {
            return $this->render('contacto/lista.html.twig', $this->emptyView());
        }
        if (!$request->isMethod('POST')) {
            return $this->renderList($request, $fiscalEntity, null, null);
        }
        if (!$this->isCsrfTokenValid('contacto', $request->request->getString('_token'))) {
            return $this->renderList($request, $fiscalEntity, 'El formulario ha caducado. Vuelve a enviarlo.', null);
        }
        try {
            $this->save->create(
                $account,
                $fiscalEntity,
                $request->request->getString('nombre'),
                $request->request->getString('nif'),
                '1' === $request->request->getString('cliente'),
                '1' === $request->request->getString('proveedor'),
            );
        } catch (InvalidIncome $exception) {
            if (InvalidIncome::FORBIDDEN === $exception->reason) {
                throw $this->createNotFoundException('Contacto no encontrado.');
            }

            return $this->renderList($request, $fiscalEntity, IncomeMessages::of($exception->reason), null);
        }

        return $this->redirectToRoute('contacts', ['guardado' => '1']);
    }

    #[Route('/contactos/{id}', name: 'contact_update', methods: ['POST'], requirements: ['id' => '[0-9a-fA-F-]{36}'])]
    public function update(Request $request, string $id): Response
    {
        $account = $this->currentAccount();
        if (!$account instanceof Account) {
            return $this->redirectToRoute('account_login');
        }
        $fiscalEntity = $this->active->resolve($account, $request);
        if (null === $fiscalEntity) {
            throw $this->createNotFoundException('Contacto no encontrado.');
        }
        if (!$this->isCsrfTokenValid('contacto', $request->request->getString('_token'))) {
            return $this->renderList($request, $fiscalEntity, 'El formulario ha caducado. Vuelve a enviarlo.', $id);
        }
        try {
            $this->save->update(
                $account,
                $fiscalEntity,
                $id,
                $request->request->getString('nombre'),
                $request->request->getString('nif'),
                '1' === $request->request->getString('cliente'),
                '1' === $request->request->getString('proveedor'),
            );
        } catch (InvalidIncome $exception) {
            if (InvalidIncome::FORBIDDEN === $exception->reason) {
                throw $this->createNotFoundException('Contacto no encontrado.');
            }

            return $this->renderList($request, $fiscalEntity, IncomeMessages::of($exception->reason), $id);
        }

        return $this->redirectToRoute('contacts', ['guardado' => '1']);
    }

    private function renderList(Request $request, FiscalEntity $fiscalEntity, ?string $error, ?string $editingId): Response
    {
        return $this->render('contacto/lista.html.twig', [
            'entityReady' => true,
            'contacts' => $this->contacts->search($fiscalEntity, $request->query->getString('q')),
            'query' => $request->query->getString('q'),
            'error' => $error,
            'editingId' => $editingId,
            'nombre' => $request->request->getString('nombre'),
            'nif' => $request->request->getString('nif'),
            'cliente' => '1' === $request->request->getString('cliente'),
            'proveedor' => '1' === $request->request->getString('proveedor'),
            'saved' => $request->query->getBoolean('guardado'),
        ], new Response(status: null === $error ? Response::HTTP_OK : Response::HTTP_UNPROCESSABLE_ENTITY));
    }

    /**
     * @return array{entityReady: bool, contacts: array{}, query: string, error: null, editingId: null, nombre: string, nif: string, cliente: bool, proveedor: bool, saved: bool}
     */
    private function emptyView(): array
    {
        return [
            'entityReady' => false,
            'contacts' => [],
            'query' => '',
            'error' => null,
            'editingId' => null,
            'nombre' => '',
            'nif' => '',
            'cliente' => false,
            'proveedor' => false,
            'saved' => false,
        ];
    }

    private function currentAccount(): ?Account
    {
        $account = $this->getUser();

        return $account instanceof Account ? $account : null;
    }
}
