<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Account;
use App\Entity\Contact;
use App\Entity\FiscalEntity;
use App\Entity\Income;
use App\Entity\IncomeAttachment;
use App\Identity\ActiveFiscalEntity;
use App\Income\Amount;
use App\Income\AnnulIncome;
use App\Income\AttachIncomeFile;
use App\Income\CivilDate;
use App\Income\ClosedQuarterRepository;
use App\Income\CloseFiscalQuarter;
use App\Income\CollectIncome;
use App\Income\ContactRepository;
use App\Income\CorrectIncome;
use App\Income\ImportIncomes;
use App\Income\IncomeActionRepository;
use App\Income\IncomeAttachmentRepository;
use App\Income\IncomeMessages;
use App\Income\IncomeRepository;
use App\Income\InvalidIncome;
use App\Income\ObjectStore;
use App\Income\PostedLines;
use App\Income\Quarter;
use App\Income\RegisterIncome;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

final class IncomeController extends AbstractController
{
    public function __construct(
        private readonly ActiveFiscalEntity $active,
        private readonly RegisterIncome $register,
        private readonly CorrectIncome $correct,
        private readonly CollectIncome $collect,
        private readonly AnnulIncome $annul,
        private readonly AttachIncomeFile $attach,
        private readonly CloseFiscalQuarter $closeQuarter,
        private readonly ImportIncomes $import,
        private readonly ContactRepository $contacts,
        private readonly IncomeRepository $incomes,
        private readonly IncomeActionRepository $actions,
        private readonly IncomeAttachmentRepository $attachments,
        private readonly ClosedQuarterRepository $quarters,
        private readonly ObjectStore $objects,
    ) {
    }

    #[Route('/ingresos', name: 'incomes', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        $account = $this->currentAccount();
        if (!$account instanceof Account) {
            return $this->redirectToRoute('account_login');
        }
        $fiscalEntity = $this->active->resolve($account, $request);
        if (null === $fiscalEntity) {
            return $this->render('ingreso/lista.html.twig', ['entityReady' => false]);
        }
        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('ingreso', $request->request->getString('_token'))) {
                return $this->list($request, $fiscalEntity, 'El formulario ha caducado. Vuelve a enviarlo.');
            }
            try {
                $issuedOn = $this->date($request->request->getString('fecha'));
                $income = ($this->register)(
                    $account,
                    $fiscalEntity,
                    $request->request->getString('contacto'),
                    $request->request->getString('numero'),
                    $issuedOn,
                    $this->optionalDate($request->request->getString('vencimiento')),
                    PostedLines::from($request),
                );
            } catch (InvalidIncome $exception) {
                if (InvalidIncome::FORBIDDEN === $exception->reason) {
                    throw $this->createNotFoundException('Ingreso no encontrado.');
                }

                return $this->list($request, $fiscalEntity, IncomeMessages::of($exception->reason));
            }

            return $this->redirectToRoute('income_show', ['id' => $income->id()]);
        }

        return $this->list($request, $fiscalEntity, null);
    }

    #[Route('/ingresos/plantilla.csv', name: 'income_template', methods: ['GET'])]
    public function template(Request $request): Response
    {
        $account = $this->currentAccount();
        if (!$account instanceof Account) {
            return $this->redirectToRoute('account_login');
        }

        return new Response(
            "fecha,numero,contacto,nif,descripcion,cantidad,precio,iva,retencion\n",
            Response::HTTP_OK,
            [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="ingresos.csv"',
            ],
        );
    }

    #[Route('/ingresos/importar', name: 'income_import', methods: ['GET', 'POST'])]
    public function import(Request $request): Response
    {
        $account = $this->currentAccount();
        if (!$account instanceof Account) {
            return $this->redirectToRoute('account_login');
        }
        $fiscalEntity = $this->active->resolve($account, $request);
        if (null === $fiscalEntity) {
            return $this->render('ingreso/importar.html.twig', ['entityReady' => false, 'error' => null, 'report' => null]);
        }
        if (!$request->isMethod('POST')) {
            return $this->render('ingreso/importar.html.twig', ['entityReady' => true, 'error' => null, 'report' => null]);
        }
        if (!$this->isCsrfTokenValid('importar-ingresos', $request->request->getString('_token'))) {
            return $this->render('ingreso/importar.html.twig', [
                'entityReady' => true,
                'error' => 'El formulario ha caducado. Vuelve a enviarlo.',
                'report' => null,
            ], new Response(status: Response::HTTP_UNPROCESSABLE_ENTITY));
        }
        $uploaded = $request->files->get('archivo');
        if (!$uploaded instanceof UploadedFile || !$uploaded->isValid()) {
            return $this->render('ingreso/importar.html.twig', [
                'entityReady' => true,
                'error' => 'Elige un archivo CSV.',
                'report' => null,
            ], new Response(status: Response::HTTP_UNPROCESSABLE_ENTITY));
        }
        $contents = $uploaded->getContent();
        try {
            $report = ($this->import)($account, $fiscalEntity, $contents);
        } catch (InvalidIncome $exception) {
            if (InvalidIncome::FORBIDDEN === $exception->reason) {
                throw $this->createNotFoundException('Ingreso no encontrado.');
            }

            return $this->render('ingreso/importar.html.twig', [
                'entityReady' => true,
                'error' => IncomeMessages::of($exception->reason),
                'report' => null,
            ], new Response(status: Response::HTTP_UNPROCESSABLE_ENTITY));
        }
        $status = $report->imported > 0 || [] === $report->errors ? Response::HTTP_OK : Response::HTTP_UNPROCESSABLE_ENTITY;

        return $this->render('ingreso/importar.html.twig', [
            'entityReady' => true,
            'error' => null,
            'report' => $report,
        ], new Response(status: $status));
    }

    #[Route('/ingresos/cerrar-trimestre', name: 'income_close_quarter', methods: ['POST'])]
    public function closeQuarter(Request $request): Response
    {
        $account = $this->currentAccount();
        if (!$account instanceof Account) {
            return $this->redirectToRoute('account_login');
        }
        $fiscalEntity = $this->active->resolve($account, $request);
        if (null === $fiscalEntity) {
            return $this->render('ingreso/lista.html.twig', ['entityReady' => false]);
        }
        if (!$this->isCsrfTokenValid('cerrar-trimestre', $request->request->getString('_token'))) {
            return $this->list($request, $fiscalEntity, 'El formulario ha caducado. Vuelve a enviarlo.');
        }
        try {
            ($this->closeQuarter)(
                $account,
                $fiscalEntity,
                $request->request->getInt('anio'),
                $request->request->getInt('trimestre'),
            );
        } catch (InvalidIncome $exception) {
            if (InvalidIncome::FORBIDDEN === $exception->reason) {
                throw $this->createNotFoundException('Ingreso no encontrado.');
            }

            return $this->list($request, $fiscalEntity, IncomeMessages::of($exception->reason));
        }

        return $this->redirectToRoute('incomes', ['trimestre' => '1']);
    }

    #[Route('/ingresos/{id}', name: 'income_show', methods: ['GET'], requirements: ['id' => '[0-9a-fA-F-]{36}'])]
    public function show(Request $request, string $id): Response
    {
        return $this->open($request, $id, null);
    }

    #[Route('/ingresos/{id}', name: 'income_update', methods: ['POST'], requirements: ['id' => '[0-9a-fA-F-]{36}'])]
    public function update(Request $request, string $id): Response
    {
        return $this->change($request, $id, 'corregir-ingreso', function (Account $account, FiscalEntity $fiscalEntity, Income $income) use ($request): void {
            ($this->correct)(
                $account,
                $fiscalEntity,
                $income->id(),
                $request->request->getInt('version'),
                $request->request->getString('contacto'),
                $request->request->getString('numero'),
                $this->date($request->request->getString('fecha')),
                $this->optionalDate($request->request->getString('vencimiento')),
                PostedLines::from($request),
            );
        });
    }

    #[Route('/ingresos/{id}/cobros', name: 'income_collect', methods: ['POST'], requirements: ['id' => '[0-9a-fA-F-]{36}'])]
    public function collect(Request $request, string $id): Response
    {
        return $this->change($request, $id, 'cobro', function (Account $account, FiscalEntity $fiscalEntity, Income $income) use ($request): void {
            $paidOn = $this->date($request->request->getString('fecha_cobro'));
            ($this->collect)(
                $account,
                $fiscalEntity,
                $income->id(),
                $request->request->getInt('version'),
                $request->request->getString('importe'),
                $paidOn,
                $request->request->getString('metodo'),
                $request->request->getString('referencia'),
                $request->request->getString('payment_token'),
            );
        }, 'cobro');
    }

    #[Route('/ingresos/{id}/anular', name: 'income_annul', methods: ['POST'], requirements: ['id' => '[0-9a-fA-F-]{36}'])]
    public function annul(Request $request, string $id): Response
    {
        return $this->change($request, $id, 'anular-ingreso', function (Account $account, FiscalEntity $fiscalEntity, Income $income) use ($request): void {
            ($this->annul)(
                $account,
                $fiscalEntity,
                $income->id(),
                $request->request->getInt('version'),
                $request->request->getString('motivo'),
            );
        }, 'anulado');
    }

    #[Route('/ingresos/{id}/adjuntos', name: 'income_attach', methods: ['POST'], requirements: ['id' => '[0-9a-fA-F-]{36}'])]
    public function attach(Request $request, string $id): Response
    {
        return $this->change($request, $id, 'adjunto', function (Account $account, FiscalEntity $fiscalEntity, Income $income) use ($request): void {
            $uploaded = $request->files->get('archivo');
            if (!$uploaded instanceof UploadedFile || !$uploaded->isValid()) {
                throw new InvalidIncome(InvalidIncome::FILE);
            }
            $mime = $uploaded->getMimeType();
            if (!is_string($mime)) {
                throw new InvalidIncome(InvalidIncome::FILE);
            }
            ($this->attach)(
                $account,
                $fiscalEntity,
                $income->id(),
                $request->request->getInt('version'),
                $uploaded->getClientOriginalName(),
                $mime,
                $uploaded->getContent(),
            );
        }, 'adjunto');
    }

    #[Route('/ingresos/{id}/adjuntos/{attachmentId}', name: 'income_attachment', methods: ['GET'], requirements: ['id' => '[0-9a-fA-F-]{36}', 'attachmentId' => '[0-9a-fA-F-]{36}'])]
    public function attachment(Request $request, string $id, string $attachmentId): Response
    {
        $opened = $this->authorizedIncome($request, $id);
        if ($opened instanceof Response) {
            return $opened;
        }
        [$fiscalEntity, $income] = $opened;
        $attachment = $this->attachments->findForIncome($fiscalEntity, $income, $attachmentId);
        if (null === $attachment) {
            throw $this->createNotFoundException('Archivo no encontrado.');
        }

        return $this->download($attachment);
    }

    private function list(Request $request, FiscalEntity $fiscalEntity, ?string $error): Response
    {
        $drafts = PostedLines::from($request);
        if ([] === $drafts) {
            $drafts = [$this->blankLine(), $this->blankLine()];
        }

        return $this->render('ingreso/lista.html.twig', [
            'entityReady' => true,
            'incomes' => $this->incomes->search($fiscalEntity, $request->query->getString('q')),
            'clients' => $this->clients($fiscalEntity),
            'quarters' => $this->quarters->forEntity($fiscalEntity),
            'query' => $request->query->getString('q'),
            'error' => $error,
            'drafts' => $drafts,
            'contacto' => $request->request->getString('contacto'),
            'numero' => $request->request->getString('numero'),
            'fecha' => $request->request->getString('fecha'),
            'vencimiento' => $request->request->getString('vencimiento'),
            'quarterClosed' => $request->query->getBoolean('trimestre'),
        ], new Response(status: null === $error ? Response::HTTP_OK : Response::HTTP_UNPROCESSABLE_ENTITY));
    }

    private function open(Request $request, string $id, ?string $error): Response
    {
        $opened = $this->authorizedIncome($request, $id);
        if ($opened instanceof Response) {
            return $opened;
        }
        [$fiscalEntity, $income] = $opened;
        $drafts = PostedLines::from($request);
        if ([] === $drafts) {
            $drafts = $this->draftsFromIncome($income);
            $drafts[] = $this->blankLine();
        }

        return $this->render('ingreso/detalle.html.twig', [
            'income' => $income,
            'clients' => $this->clients($fiscalEntity),
            'actions' => $this->actions->forIncome($income),
            'attachments' => $this->attachments->forIncome($income),
            'closed' => $this->isClosed($fiscalEntity, $income->issuedOn()),
            'error' => $error,
            'drafts' => $drafts,
            'contacto' => $request->request->has('contacto') ? $request->request->getString('contacto') : $income->contact()->id(),
            'numero' => $request->request->has('numero') ? $request->request->getString('numero') : $income->invoiceNo(),
            'fecha' => $request->request->has('fecha') ? $request->request->getString('fecha') : $income->issuedOn()->format('Y-m-d'),
            'vencimiento' => $request->request->has('vencimiento') ? $request->request->getString('vencimiento') : ($income->dueOn()?->format('Y-m-d') ?? ''),
            'paymentToken' => Uuid::v7()->toRfc4122(),
            'methods' => \App\Entity\IncomePayment::METHODS,
            'collectedNotice' => $request->query->getBoolean('cobro'),
            'annulledNotice' => $request->query->getBoolean('anulado'),
            'attachedNotice' => $request->query->getBoolean('adjunto'),
        ], new Response(status: null === $error ? Response::HTTP_OK : Response::HTTP_UNPROCESSABLE_ENTITY));
    }

    /**
     * @param \Closure(Account, FiscalEntity, Income): void $operation
     */
    private function change(Request $request, string $id, string $csrfId, \Closure $operation, ?string $notice = null): Response
    {
        $account = $this->currentAccount();
        if (!$account instanceof Account) {
            return $this->redirectToRoute('account_login');
        }
        $fiscalEntity = $this->active->resolve($account, $request);
        if (null === $fiscalEntity) {
            throw $this->createNotFoundException('Ingreso no encontrado.');
        }
        $income = $this->incomes->findForEntity($fiscalEntity, $id);
        if (null === $income) {
            throw $this->createNotFoundException('Ingreso no encontrado.');
        }
        if (!$this->isCsrfTokenValid($csrfId, $request->request->getString('_token'))) {
            return $this->open($request, $id, 'El formulario ha caducado. Vuelve a enviarlo.');
        }
        try {
            $operation($account, $fiscalEntity, $income);
        } catch (InvalidIncome $exception) {
            if (InvalidIncome::FORBIDDEN === $exception->reason) {
                throw $this->createNotFoundException('Ingreso no encontrado.');
            }

            return $this->open($request, $id, IncomeMessages::of($exception->reason));
        }
        $params = ['id' => $income->id()];
        if (null !== $notice) {
            $params[$notice] = '1';
        }

        return $this->redirectToRoute('income_show', $params);
    }

    /**
     * @return array{0: FiscalEntity, 1: Income}|Response
     */
    private function authorizedIncome(Request $request, string $id): array|Response
    {
        $account = $this->currentAccount();
        if (!$account instanceof Account) {
            return $this->redirectToRoute('account_login');
        }
        $fiscalEntity = $this->active->resolve($account, $request);
        if (null === $fiscalEntity) {
            throw $this->createNotFoundException('Ingreso no encontrado.');
        }
        $income = $this->incomes->findForEntity($fiscalEntity, $id);
        if (null === $income) {
            throw $this->createNotFoundException('Ingreso no encontrado.');
        }

        return [$fiscalEntity, $income];
    }

    private function download(IncomeAttachment $attachment): Response
    {
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
     * @return list<Contact>
     */
    private function clients(FiscalEntity $fiscalEntity): array
    {
        $clients = [];
        foreach ($this->contacts->search($fiscalEntity, '') as $contact) {
            if ($contact->isClient()) {
                $clients[] = $contact;
            }
        }

        return $clients;
    }

    private function isClosed(FiscalEntity $fiscalEntity, \DateTimeImmutable $date): bool
    {
        [$year, $quarter] = Quarter::of($date);

        return null !== $this->quarters->findOne($fiscalEntity->id(), $year, $quarter);
    }

    /**
     * @return list<array{description: string, quantity: string, unitPrice: string, taxRate: string, withholdingRate: string}>
     */
    private function draftsFromIncome(Income $income): array
    {
        $drafts = [];
        foreach ($income->lines() as $line) {
            $drafts[] = [
                'description' => $line->description(),
                'quantity' => Amount::plain($line->quantity()),
                'unitPrice' => Amount::plain($line->unitPrice()),
                'taxRate' => Amount::plain($line->taxRate()),
                'withholdingRate' => Amount::plain($line->withholdingRate()),
            ];
        }

        return $drafts;
    }

    /**
     * @return array{description: string, quantity: string, unitPrice: string, taxRate: string, withholdingRate: string}
     */
    private function blankLine(): array
    {
        return ['description' => '', 'quantity' => '', 'unitPrice' => '', 'taxRate' => '', 'withholdingRate' => ''];
    }

    private function date(string $raw): \DateTimeImmutable
    {
        $date = CivilDate::parse($raw);
        if (null === $date) {
            throw new InvalidIncome(InvalidIncome::DATE);
        }

        return $date;
    }

    private function optionalDate(string $raw): ?\DateTimeImmutable
    {
        if ('' === trim($raw)) {
            return null;
        }

        return $this->date($raw);
    }

    private function currentAccount(): ?Account
    {
        $account = $this->getUser();

        return $account instanceof Account ? $account : null;
    }
}
