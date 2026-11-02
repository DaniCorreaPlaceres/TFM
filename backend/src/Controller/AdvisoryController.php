<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Account;
use App\Entity\AdvisoryMembership;
use App\Identity\AcceptAdvisoryInvitation;
use App\Identity\AdvisoryInvitationRepository;
use App\Identity\AdvisoryMembershipRepository;
use App\Identity\CreateAdvisoryFirm;
use App\Identity\InvalidAdvisory;
use App\Identity\InviteAdvisoryEmployee;
use App\Identity\JoinPendingAdvisoryInvitations;
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

        return $this->render('asesoria/detalle.html.twig', [
            'firm' => $membership->advisoryFirm(),
            'members' => $this->memberships->findForFirm($membership->advisoryFirm()),
            'isAdmin' => $membership->isAdmin(),
            'error' => null,
            'email' => '',
            'invited' => $request->query->getBoolean('invitada'),
        ]);
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

        return $this->render('asesoria/detalle.html.twig', [
            'firm' => $membership->advisoryFirm(),
            'members' => $this->memberships->findForFirm($membership->advisoryFirm()),
            'isAdmin' => $membership->isAdmin(),
            'error' => $error,
            'email' => $request->request->getString('email'),
            'invited' => false,
        ], new Response(status: Response::HTTP_UNPROCESSABLE_ENTITY));
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
