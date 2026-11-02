<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Account;
use App\Entity\AdvisoryMembership;
use App\Entity\FiscalEntity;
use App\Entity\Income;
use App\Entity\IncomeAttachment;
use App\Identity\AdvisoryClientLinkRepository;
use App\Identity\AdvisoryMembershipRepository;
use App\Income\IncomeActionRepository;
use App\Income\IncomeAttachmentRepository;
use App\Income\IncomeRepository;
use App\Income\ObjectStore;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdvisoryIncomeController extends AbstractController
{
    public function __construct(
        private readonly AdvisoryMembershipRepository $memberships,
        private readonly AdvisoryClientLinkRepository $links,
        private readonly IncomeRepository $incomes,
        private readonly IncomeActionRepository $actions,
        private readonly IncomeAttachmentRepository $attachments,
        private readonly ObjectStore $objects,
    ) {
    }

    #[Route('/asesorias/{id}/entidades/{entityId}/ingresos', name: 'advisory_incomes', methods: ['GET'], requirements: ['id' => '[0-9a-fA-F-]{36}', 'entityId' => '[0-9a-fA-F-]{36}'])]
    public function index(Request $request, string $id, string $entityId): Response
    {
        $opened = $this->entity($request, $id, $entityId);
        if ($opened instanceof Response) {
            return $opened;
        }
        [$membership, $fiscalEntity] = $opened;

        return $this->render('ingreso/asesoria-lista.html.twig', [
            'firm' => $membership->advisoryFirm(),
            'fiscalEntity' => $fiscalEntity,
            'incomes' => $this->incomes->search($fiscalEntity, ''),
        ]);
    }

    #[Route('/asesorias/{id}/entidades/{entityId}/ingresos/{incomeId}', name: 'advisory_income', methods: ['GET'], requirements: ['id' => '[0-9a-fA-F-]{36}', 'entityId' => '[0-9a-fA-F-]{36}', 'incomeId' => '[0-9a-fA-F-]{36}'])]
    public function show(Request $request, string $id, string $entityId, string $incomeId): Response
    {
        $opened = $this->entity($request, $id, $entityId);
        if ($opened instanceof Response) {
            return $opened;
        }
        [$membership, $fiscalEntity] = $opened;
        $income = $this->incomes->findForEntity($fiscalEntity, $incomeId);
        if (!$income instanceof Income) {
            throw $this->createNotFoundException('Ingreso no encontrado.');
        }

        return $this->render('ingreso/asesoria-detalle.html.twig', [
            'firm' => $membership->advisoryFirm(),
            'fiscalEntity' => $fiscalEntity,
            'income' => $income,
            'actions' => $this->actions->forIncome($income),
            'attachments' => $this->attachments->forIncome($income),
        ]);
    }

    #[Route('/asesorias/{id}/entidades/{entityId}/ingresos/{incomeId}/adjuntos/{attachmentId}', name: 'advisory_income_attachment', methods: ['GET'], requirements: ['id' => '[0-9a-fA-F-]{36}', 'entityId' => '[0-9a-fA-F-]{36}', 'incomeId' => '[0-9a-fA-F-]{36}', 'attachmentId' => '[0-9a-fA-F-]{36}'])]
    public function attachment(Request $request, string $id, string $entityId, string $incomeId, string $attachmentId): Response
    {
        $opened = $this->entity($request, $id, $entityId);
        if ($opened instanceof Response) {
            return $opened;
        }
        [, $fiscalEntity] = $opened;
        $income = $this->incomes->findForEntity($fiscalEntity, $incomeId);
        if (!$income instanceof Income) {
            throw $this->createNotFoundException('Ingreso no encontrado.');
        }
        $attachment = $this->attachments->findForIncome($fiscalEntity, $income, $attachmentId);
        if (!$attachment instanceof IncomeAttachment) {
            throw $this->createNotFoundException('Archivo no encontrado.');
        }
        try {
            $bytes = $this->objects->get($attachment->objectKey());
        } catch (\RuntimeException) {
            throw $this->createNotFoundException('Archivo no encontrado.');
        }
        $filename = str_replace(['"', "\r", "\n"], '', $attachment->fileName());

        return new Response($bytes, Response::HTTP_OK, [
            'Content-Type' => $attachment->mimeType(),
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /**
     * @return array{0: AdvisoryMembership, 1: FiscalEntity}|Response
     */
    private function entity(Request $request, string $firmId, string $entityId): array|Response
    {
        $account = $this->getUser();
        if (!$account instanceof Account) {
            return $this->redirectToRoute('account_login');
        }
        $membership = $this->memberships->findForAccountAndFirm($account, $firmId);
        if (null === $membership || !$membership->isAdmin()) {
            throw $this->createNotFoundException('Asesoría no encontrada.');
        }
        foreach ($this->links->findByFirm($membership->advisoryFirm()) as $link) {
            if ($link->fiscalEntity()->id() === $entityId) {
                return [$membership, $link->fiscalEntity()];
            }
        }

        throw $this->createNotFoundException('Entidad no encontrada.');
    }
}
