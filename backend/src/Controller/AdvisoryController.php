<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Account;
use App\Entity\AdvisoryMembership;
use App\Identity\AcceptAdvisoryInvitation;
use App\Identity\AdvisoryClientLinkRepository;
use App\Identity\AdvisoryEmployeePermissionRepository;
use App\Identity\AdvisoryInvitationRepository;
use App\Identity\AdvisoryMembershipRepository;
use App\Identity\CreateAdvisoryFirm;
use App\Identity\InvalidAdvisory;
use App\Identity\InviteAdvisoryEmployee;
use App\Identity\InviteEntityForAdvisory;
use App\Identity\JoinPendingAdvisoryInvitations;
use App\Identity\SetEmployeeEntityView;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdvisoryController extends AbstractController
{
    public function __construct(
        private readonly CreateAdvisoryFirm $create,
        private readonly InviteAdvisoryEmployee $invite,
        private readonly AcceptAdvisoryInvitation $accept,
        private readonly JoinPendingAdvisoryInvitations $joinPending,
        private readonly AdvisoryMembershipRepository $memberships,
        private readonly AdvisoryInvitationRepository $invitations,
        private readonly InviteEntityForAdvisory $inviteEntity,
        private readonly AdvisoryClientLinkRepository $clientLinks,
        private readonly SetEmployeeEntityView $setEntityView,
        private readonly AdvisoryEmployeePermissionRepository $permissions,
    ) {
    }

    #[Route('/asesorias', name: 'advisories', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        $account = $this->currentAccount();
        if (!$account instanceof Account) {
            return $this->redirectToRoute('account_login');
        }

        ($this->joinPending)($account);
        $memberships = $this->memberships->findForAccount($account);
        $error = null;
        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('asesoria', $request->request->getString('_token'))) {
                $error = 'El formulario ha caducado. Vuelve a enviarlo.';
            } elseif (!$this->canOpenFirm($memberships)) {
                $error = 'Perteneces a una asesoría como empleado.';
            } else {
                try {
                    $advisoryFirm = ($this->create)(
                        $account,
                        $request->request->getString('name'),
                        $request->request->getString('tax_id'),
                    );

                    return $this->redirectToRoute('advisory', ['id' => $advisoryFirm->id()]);
                } catch (InvalidAdvisory $exception) {
                    $error = $this->message($exception);
                }
            }
        }

        return $this->render('asesoria/lista.html.twig', [
            'error' => $error,
            'name' => $request->request->getString('name'),
            'taxId' => $request->request->getString('tax_id'),
            'memberships' => $memberships,
            'canOpenFirm' => $this->canOpenFirm($memberships),
        ], new Response(status: null === $error ? Response::HTTP_OK : Response::HTTP_UNPROCESSABLE_ENTITY));
    }

    #[Route('/asesorias/invitacion/{token}', name: 'advisory_invitation', methods: ['GET', 'POST'], requirements: ['token' => '[0-9a-f]{64}'])]
    public function invitation(Request $request, string $token): Response
    {
        $account = $this->currentAccount();
        if (!$account instanceof Account) {
            return $this->redirectToRoute('account_login');
        }

        ($this->joinPending)($account);
        try {
            $invitation = $this->accept->preview($account, $token);
        } catch (InvalidAdvisory $exception) {
            if (InvalidAdvisory::WRONG_ACCOUNT === $exception->reason) {
                return $this->render('asesoria/invitacion.html.twig', [
                    'error' => 'Esta invitación es para otro correo.',
                    'firmName' => null,
                    'token' => $token,
                ]);
            }

            $stored = $this->invitations->findByTokenHash(hash('sha256', $token));
            if (null !== $stored && null !== $this->memberships->findForAccountAndFirm($account, $stored->advisoryFirm()->id())) {
                return $this->redirectToRoute('advisory', ['id' => $stored->advisoryFirm()->id()]);
            }

            throw $this->createNotFoundException('Invitación no encontrada.');
        }

        $error = null;
        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('aceptar-asesoria', $request->request->getString('_token'))) {
                $error = 'El formulario ha caducado. Vuelve a enviarlo.';
            } else {
                $membership = ($this->accept)($account, $token);

                return $this->redirectToRoute('advisory', ['id' => $membership->advisoryFirm()->id()]);
            }
        }

        return $this->render('asesoria/invitacion.html.twig', [
            'error' => $error,
            'firmName' => $invitation->advisoryFirm()->name(),
            'token' => $token,
        ], new Response(status: null === $error ? Response::HTTP_OK : Response::HTTP_UNPROCESSABLE_ENTITY));
    }

    #[Route('/asesorias/{id}', name: 'advisory', methods: ['GET'], requirements: ['id' => '[0-9a-fA-F-]{36}'])]
    public function show(Request $request, string $id): Response
    {
        $account = $this->currentAccount();
        if (!$account instanceof Account) {
            return $this->redirectToRoute('account_login');
        }

        $membership = $this->memberships->findForAccountAndFirm($account, $id);
        if (null === $membership) {
            throw $this->createNotFoundException('Asesoría no encontrada.');
        }

        return $this->renderDetail($request, $membership, null, '', null, '');
    }

    #[Route('/asesorias/{id}/invitar', name: 'advisory_invite', methods: ['POST'], requirements: ['id' => '[0-9a-fA-F-]{36}'])]
    public function inviteEmployee(Request $request, string $id): Response
    {
        $account = $this->currentAccount();
        if (!$account instanceof Account) {
            return $this->redirectToRoute('account_login');
        }

        $membership = $this->memberships->findForAccountAndFirm($account, $id);
        if (null === $membership || !$membership->isAdmin()) {
            throw $this->createNotFoundException('Asesoría no encontrada.');
        }

        $error = null;
        if (!$this->isCsrfTokenValid('invitar-asesoria', $request->request->getString('_token'))) {
            $error = 'El formulario ha caducado. Vuelve a enviarlo.';
        } else {
            try {
                ($this->invite)($account, $id, $request->request->getString('email'));

                return $this->redirectToRoute('advisory', ['id' => $id, 'invitada' => '1']);
            } catch (InvalidAdvisory $exception) {
                if (InvalidAdvisory::FORBIDDEN === $exception->reason) {
                    throw $this->createNotFoundException('Asesoría no encontrada.');
                }
                $error = $this->message($exception);
            }
        }

        return $this->renderDetail($request, $membership, $error, $request->request->getString('email'), null, '', Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    #[Route('/asesorias/{id}/invitar-entidad', name: 'advisory_invite_entity', methods: ['POST'], requirements: ['id' => '[0-9a-fA-F-]{36}'])]
    public function inviteFiscalEntity(Request $request, string $id): Response
    {
        $account = $this->currentAccount();
        if (!$account instanceof Account) {
            return $this->redirectToRoute('account_login');
        }

        $membership = $this->memberships->findForAccountAndFirm($account, $id);
        if (null === $membership || !$membership->isAdmin()) {
            throw $this->createNotFoundException('Asesoría no encontrada.');
        }

        if (!$this->isCsrfTokenValid('invitar-entidad', $request->request->getString('_token'))) {
            return $this->renderDetail($request, $membership, null, '', 'El formulario ha caducado. Vuelve a enviarlo.', $request->request->getString('entity_email'), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            ($this->inviteEntity)($account, $id, $request->request->getString('entity_email'));

            return $this->redirectToRoute('advisory', ['id' => $id, 'entidad' => '1']);
        } catch (InvalidAdvisory $exception) {
            $error = InvalidAdvisory::EMAIL === $exception->reason
                ? 'El correo no es válido.'
                : 'No se ha podido enviar la invitación.';

            return $this->renderDetail($request, $membership, null, '', $error, $request->request->getString('entity_email'), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    #[Route('/asesorias/{id}/permiso-entidades', name: 'advisory_employee_permission', methods: ['POST'], requirements: ['id' => '[0-9a-fA-F-]{36}'])]
    public function changeEntityView(Request $request, string $id): Response
    {
        $account = $this->currentAccount();
        if (!$account instanceof Account) {
            return $this->redirectToRoute('account_login');
        }

        $membership = $this->memberships->findForAccountAndFirm($account, $id);
        if (null === $membership || !$membership->isAdmin()) {
            throw $this->createNotFoundException('Asesoría no encontrada.');
        }

        if (!$this->isCsrfTokenValid('permiso-entidades', $request->request->getString('_token'))) {
            return $this->renderDetail($request, $membership, null, '', null, '', Response::HTTP_UNPROCESSABLE_ENTITY, 'El formulario ha caducado. Vuelve a enviarlo.');
        }

        try {
            ($this->setEntityView)(
                $account,
                $id,
                $request->request->getString('membership_id'),
                '1' === $request->request->getString('granted'),
            );
        } catch (InvalidAdvisory) {
            throw $this->createNotFoundException('Asesoría no encontrada.');
        }

        return $this->redirectToRoute('advisory', ['id' => $id, 'permiso' => '1']);
    }

    private function renderDetail(
        Request $request,
        AdvisoryMembership $membership,
        ?string $error,
        string $email,
        ?string $clientError,
        string $clientEmail,
        int $status = Response::HTTP_OK,
        ?string $permissionError = null,
    ): Response {
        $firm = $membership->advisoryFirm();
        $canViewClients = $membership->isAdmin() || null !== $this->permissions->findForMembership($membership);

        return $this->render('asesoria/detalle.html.twig', [
            'firm' => $firm,
            'members' => $this->memberships->findForFirm($firm),
            'isAdmin' => $membership->isAdmin(),
            'error' => $error,
            'email' => $email,
            'invited' => $request->query->getBoolean('invitada'),
            'clients' => $canViewClients ? $this->clientLinks->findByFirm($firm) : [],
            'canViewClients' => $canViewClients,
            'grantedIds' => $membership->isAdmin() ? $this->permissions->grantedMembershipIds($firm) : [],
            'permissionError' => $permissionError,
            'permissionSaved' => $request->query->getBoolean('permiso'),
            'clientError' => $clientError,
            'clientEmail' => $clientEmail,
            'entityInvited' => $request->query->getBoolean('entidad'),
        ], new Response(status: $status));
    }

    /**
     * @param list<AdvisoryMembership> $memberships
     */
    private function canOpenFirm(array $memberships): bool
    {
        if ([] === $memberships) {
            return true;
        }

        foreach ($memberships as $membership) {
            if ($membership->isAdmin()) {
                return true;
            }
        }

        return false;
    }

    private function currentAccount(): ?Account
    {
        $account = $this->getUser();

        return $account instanceof Account ? $account : null;
    }

    private function message(InvalidAdvisory $exception): string
    {
        return match ($exception->reason) {
            InvalidAdvisory::NAME => 'El nombre es obligatorio y no puede pasar de 180 caracteres.',
            InvalidAdvisory::EMAIL => 'El correo no es válido.',
            InvalidAdvisory::ALREADY_MEMBER => 'Esa persona ya pertenece a la asesoría.',
            default => 'El NIF no es válido.',
        };
    }
}
