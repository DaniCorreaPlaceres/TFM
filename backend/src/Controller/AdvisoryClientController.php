<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Account;
use App\Entity\AdvisoryClientInvitation;
use App\Entity\AdvisoryFirm;
use App\Entity\FiscalEntity;
use App\Identity\AcceptAdvisoryClientInvitation;
use App\Identity\InvalidAdvisory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdvisoryClientController extends AbstractController
{
    public function __construct(private readonly AcceptAdvisoryClientInvitation $accept)
    {
    }

    #[Route('/asesorias/vinculo/{token}', name: 'advisory_client_invitation', methods: ['GET', 'POST'], requirements: ['token' => '[0-9a-f]{64}'])]
    public function invitation(Request $request, string $token): Response
    {
        $account = $this->getUser();
        if (!$account instanceof Account) {
            return $this->redirectToRoute('account_login');
        }

        try {
            $invitation = $this->accept->preview($account, $token);
        } catch (InvalidAdvisory $exception) {
            if (InvalidAdvisory::WRONG_ACCOUNT === $exception->reason) {
                return $this->render('asesoria/vinculo.html.twig', [
                    'error' => 'Esta invitación es para otro correo.',
                    'summary' => null,
                    'choices' => [],
                    'emptyChoice' => null,
                ]);
            }

            throw $this->createNotFoundException('Invitación no encontrada.');
        }

        $fromEntity = AdvisoryClientInvitation::FROM_ENTITY === $invitation->origin();
        $choices = [];
        if ($fromEntity) {
            foreach ($this->accept->firms($account) as $firm) {
                $choices[] = ['id' => $firm->id(), 'label' => $firm->name()];
            }
            $entity = $invitation->fiscalEntity();
            $summary = $entity instanceof FiscalEntity
                ? 'Puedes vincular '.$entity->legalName().' con una asesoría que administras.'
                : null;
            $emptyChoice = 'Crea una asesoría y vuelve a abrir el enlace.';
        } else {
            foreach ($this->accept->entities($account) as $entity) {
                $choices[] = ['id' => $entity->id(), 'label' => $entity->legalName()];
            }
            $firm = $invitation->advisoryFirm();
            $summary = $firm instanceof AdvisoryFirm
                ? 'Puedes vincular '.$firm->name().' con una de tus entidades.'
                : null;
            $emptyChoice = 'Crea una entidad fiscal y vuelve a abrir el enlace.';
        }

        $error = null;
        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('aceptar-vinculo', $request->request->getString('_token'))) {
                $error = 'El formulario ha caducado. Vuelve a enviarlo.';
            } else {
                try {
                    $link = ($this->accept)($account, $token, $request->request->getString('target'));
                    if ($fromEntity) {
                        return $this->redirectToRoute('advisory', ['id' => $link->advisoryFirm()->id()]);
                    }

                    return $this->redirectToRoute('fiscal_entities');
                } catch (InvalidAdvisory $exception) {
                    $error = match ($exception->reason) {
                        InvalidAdvisory::ALREADY_LINKED => 'Esta entidad ya tiene una asesoría activa.',
                        InvalidAdvisory::FORBIDDEN => 'Elige una opción tuya.',
                        default => 'La invitación ya no se puede usar.',
                    };
                }
            }
        }

        return $this->render('asesoria/vinculo.html.twig', [
            'error' => $error,
            'summary' => $summary,
            'choices' => $choices,
            'emptyChoice' => [] === $choices ? $emptyChoice : null,
        ], new Response(status: null === $error ? Response::HTTP_OK : Response::HTTP_UNPROCESSABLE_ENTITY));
    }
}
