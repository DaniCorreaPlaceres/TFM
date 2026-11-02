<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Account;
use App\Entity\Expense;
use App\Entity\FiscalEntity;
use App\Expense\DeactivateExpenseCategory;
use App\Expense\DecideExpenseDeduction;
use App\Expense\EnsureExpenseCategories;
use App\Expense\ExpenseCategoryRepository;
use App\Expense\ExpenseDuplicates;
use App\Expense\ExpenseKind;
use App\Expense\ExpenseMessages;
use App\Expense\ExpenseRepository;
use App\Expense\InvalidExpense;
use App\Expense\MarkExpenseRecurrence;
use App\Expense\PayExpense;
use App\Expense\PaymentMethod;
use App\Expense\ProposeExpense;
use App\Expense\RegisterExpense;
use App\Expense\ReviewExpense;
use App\Expense\SaveExpenseCategory;
use App\Identity\ActiveFiscalEntity;
use App\Income\CivilDate;
use App\Income\ContactRepository;
use App\Income\PostedLines;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

final class ExpenseController extends AbstractController
{
    public function __construct(
        private readonly ActiveFiscalEntity $active,
        private readonly EnsureExpenseCategories $ensure,
        private readonly SaveExpenseCategory $saveCategory,
        private readonly DeactivateExpenseCategory $deactivate,
        private readonly DecideExpenseDeduction $decide,
        private readonly RegisterExpense $register,
        private readonly ExpenseCategoryRepository $categories,
        private readonly ExpenseRepository $expenses,
        private readonly ContactRepository $contacts,
        private readonly PayExpense $pay,
        private readonly ReviewExpense $review,
        private readonly MarkExpenseRecurrence $recurrence,
        private readonly ProposeExpense $propose,
    ) {
    }

    #[Route('/gastos', name: 'expenses', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        $account = $this->currentAccount();
        if (!$account instanceof Account) {
            return $this->redirectToRoute('account_login');
        }
        $fiscalEntity = $this->active->resolve($account, $request);
        if (null === $fiscalEntity) {
            return $this->render('gasto/lista.html.twig', ['entityReady' => false]);
        }
        ($this->ensure)($fiscalEntity);
        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('gasto', $request->request->getString('_token'))) {
                return $this->page($request, $fiscalEntity, 'El formulario ha caducado. Vuelve a enviarlo.');
            }
            try {
                ($this->register)(
                    $account,
                    $fiscalEntity,
                    $request->request->getString('contacto'),
                    $request->request->getString('categoria'),
                    $request->request->getString('tipo'),
                    $request->request->getString('metodo'),
                    $request->request->getString('numero'),
                    $this->date($request->request->getString('fecha')),
                    PostedLines::from($request),
                );
            } catch (InvalidExpense $exception) {
                if (InvalidExpense::FORBIDDEN === $exception->reason) {
                    throw $this->createNotFoundException('Gasto no encontrado.');
                }

                return $this->page($request, $fiscalEntity, ExpenseMessages::of($exception->reason));
            }

            return $this->redirectToRoute('expenses', ['gasto' => '1']);
        }

        return $this->page($request, $fiscalEntity);
    }

    #[Route('/gastos/categorias', name: 'expense_category', methods: ['POST'])]
    public function category(Request $request): Response
    {
        $account = $this->currentAccount();
        if (!$account instanceof Account) {
            return $this->redirectToRoute('account_login');
        }
        $fiscalEntity = $this->active->resolve($account, $request);
        if (null === $fiscalEntity) {
            throw $this->createNotFoundException('Categoría no encontrada.');
        }
        ($this->ensure)($fiscalEntity);
        if (!$this->isCsrfTokenValid('categoria', $request->request->getString('_token'))) {
            return $this->page($request, $fiscalEntity, 'El formulario ha caducado. Vuelve a enviarlo.');
        }
        try {
            ($this->saveCategory)($account, $fiscalEntity, $request->request->getString('nombre'));
        } catch (InvalidExpense $exception) {
            if (InvalidExpense::FORBIDDEN === $exception->reason) {
                throw $this->createNotFoundException('Categoría no encontrada.');
            }

            return $this->page($request, $fiscalEntity, ExpenseMessages::of($exception->reason));
        }

        return $this->redirectToRoute('expenses', ['categoria' => '1']);
    }

    #[Route('/gastos/categorias/{id}/desactivar', name: 'expense_category_deactivate', methods: ['POST'])]
    public function deactivateCategory(Request $request, string $id): Response
    {
        $account = $this->currentAccount();
        if (!$account instanceof Account) {
            return $this->redirectToRoute('account_login');
        }
        $fiscalEntity = $this->active->resolve($account, $request);
        if (null === $fiscalEntity) {
            throw $this->createNotFoundException('Categoría no encontrada.');
        }
        if (!$this->isCsrfTokenValid('desactivar-categoria', $request->request->getString('_token'))) {
            return $this->page($request, $fiscalEntity, 'El formulario ha caducado. Vuelve a enviarlo.');
        }
        try {
            ($this->deactivate)($account, $fiscalEntity, $id);
        } catch (InvalidExpense) {
            throw $this->createNotFoundException('Categoría no encontrada.');
        }

        return $this->redirectToRoute('expenses', ['categoria' => '1']);
    }

    #[Route('/gastos/{id}/deducibilidad', name: 'expense_deduction', methods: ['POST'])]
    public function deduction(Request $request, string $id): Response
    {
        $account = $this->currentAccount();
        if (!$account instanceof Account) {
            return $this->redirectToRoute('account_login');
        }
        $fiscalEntity = $this->active->resolve($account, $request);
        if (null === $fiscalEntity || null === $this->expenses->findForEntity($fiscalEntity, $id)) {
            throw $this->createNotFoundException('Gasto no encontrado.');
        }
        if (!$this->isCsrfTokenValid('deducibilidad', $request->request->getString('_token'))) {
            return $this->page($request, $fiscalEntity, 'El formulario ha caducado. Vuelve a enviarlo.');
        }
        $choice = $request->request->getString('deducible');
        if ('1' !== $choice && '0' !== $choice) {
            return $this->page($request, $fiscalEntity, ExpenseMessages::of(InvalidExpense::PART));
        }
        try {
            ($this->decide)(
                $account,
                $fiscalEntity,
                $id,
                $request->request->getString('parte'),
                '1' === $choice,
                $request->request->getString('motivo'),
            );
        } catch (InvalidExpense $exception) {
            if (InvalidExpense::FORBIDDEN === $exception->reason) {
                throw $this->createNotFoundException('Gasto no encontrado.');
            }

            return $this->page($request, $fiscalEntity, ExpenseMessages::of($exception->reason));
        }

        return $this->redirectToRoute('expenses', ['deduccion' => '1']);
    }

    #[Route('/gastos/{id}/pagos', name: 'expense_pay', methods: ['POST'], requirements: ['id' => '[0-9a-fA-F-]{36}'])]
    public function payment(Request $request, string $id): Response
    {
        $account = $this->currentAccount();
        if (!$account instanceof Account) {
            return $this->redirectToRoute('account_login');
        }
        $fiscalEntity = $this->active->resolve($account, $request);
        if (null === $fiscalEntity || null === $this->expenses->findForEntity($fiscalEntity, $id)) {
            throw $this->createNotFoundException('Gasto no encontrado.');
        }
        if (!$this->isCsrfTokenValid('pago', $request->request->getString('_token'))) {
            return $this->page($request, $fiscalEntity, 'El formulario ha caducado. Vuelve a enviarlo.');
        }
        try {
            ($this->pay)(
                $account,
                $fiscalEntity,
                $id,
                $request->request->getString('importe'),
                $this->date($request->request->getString('fecha')),
                $request->request->getString('metodo'),
                $request->request->getString('referencia'),
                $request->request->getString('payment_token'),
            );
        } catch (InvalidExpense $exception) {
            if (InvalidExpense::FORBIDDEN === $exception->reason) {
                throw $this->createNotFoundException('Gasto no encontrado.');
            }

            return $this->page($request, $fiscalEntity, ExpenseMessages::of($exception->reason));
        }

        return $this->redirectToRoute('expenses', ['pago' => '1']);
    }

    #[Route('/gastos/{id}/revision', name: 'expense_review', methods: ['POST'], requirements: ['id' => '[0-9a-fA-F-]{36}'])]
    public function revision(Request $request, string $id): Response
    {
        $account = $this->currentAccount();
        if (!$account instanceof Account) {
            return $this->redirectToRoute('account_login');
        }
        $fiscalEntity = $this->active->resolve($account, $request);
        if (null === $fiscalEntity || null === $this->expenses->findForEntity($fiscalEntity, $id)) {
            throw $this->createNotFoundException('Gasto no encontrado.');
        }
        if (!$this->isCsrfTokenValid('revision', $request->request->getString('_token'))) {
            return $this->page($request, $fiscalEntity, 'El formulario ha caducado. Vuelve a enviarlo.');
        }
        try {
            ($this->review)(
                $account,
                $fiscalEntity,
                $id,
                $request->request->getString('estado'),
                $request->request->getString('motivo'),
            );
        } catch (InvalidExpense $exception) {
            if (InvalidExpense::FORBIDDEN === $exception->reason) {
                throw $this->createNotFoundException('Gasto no encontrado.');
            }

            return $this->page($request, $fiscalEntity, ExpenseMessages::of($exception->reason));
        }

        return $this->redirectToRoute('expenses', ['revision' => '1']);
    }

    #[Route('/gastos/{id}/recurrencia', name: 'expense_recurrence', methods: ['POST'], requirements: ['id' => '[0-9a-fA-F-]{36}'])]
    public function recurrence(Request $request, string $id): Response
    {
        $account = $this->currentAccount();
        if (!$account instanceof Account) {
            return $this->redirectToRoute('account_login');
        }
        $fiscalEntity = $this->active->resolve($account, $request);
        if (null === $fiscalEntity || null === $this->expenses->findForEntity($fiscalEntity, $id)) {
            throw $this->createNotFoundException('Gasto no encontrado.');
        }
        if (!$this->isCsrfTokenValid('recurrencia', $request->request->getString('_token'))) {
            return $this->page($request, $fiscalEntity, 'El formulario ha caducado. Vuelve a enviarlo.');
        }
        $period = $request->request->getString('periodo');
        if ('mensual' !== $period && 'ninguna' !== $period) {
            return $this->page($request, $fiscalEntity, ExpenseMessages::of(InvalidExpense::RECURRENCE));
        }
        try {
            ($this->recurrence)($account, $fiscalEntity, $id, 'mensual' === $period);
        } catch (InvalidExpense $exception) {
            if (InvalidExpense::FORBIDDEN === $exception->reason) {
                throw $this->createNotFoundException('Gasto no encontrado.');
            }

            return $this->page($request, $fiscalEntity, ExpenseMessages::of($exception->reason));
        }

        return $this->redirectToRoute('expenses', ['recurrencia' => '1']);
    }

    #[Route('/gastos/{id}/propuesta', name: 'expense_proposal', methods: ['POST'], requirements: ['id' => '[0-9a-fA-F-]{36}'])]
    public function proposal(Request $request, string $id): Response
    {
        $account = $this->currentAccount();
        if (!$account instanceof Account) {
            return $this->redirectToRoute('account_login');
        }
        $fiscalEntity = $this->active->resolve($account, $request);
        if (null === $fiscalEntity || null === $this->expenses->findForEntity($fiscalEntity, $id)) {
            throw $this->createNotFoundException('Gasto no encontrado.');
        }
        if (!$this->isCsrfTokenValid('propuesta', $request->request->getString('_token'))) {
            return $this->page($request, $fiscalEntity, 'El formulario ha caducado. Vuelve a enviarlo.');
        }
        try {
            ($this->propose)($account, $fiscalEntity, $id);
        } catch (InvalidExpense $exception) {
            if (InvalidExpense::FORBIDDEN === $exception->reason) {
                throw $this->createNotFoundException('Gasto no encontrado.');
            }

            return $this->page($request, $fiscalEntity, ExpenseMessages::of($exception->reason));
        }

        return $this->redirectToRoute('expenses', ['propuesta' => '1']);
    }

    private function page(Request $request, FiscalEntity $fiscalEntity, ?string $error = null): Response
    {
        $all = $this->categories->allForEntity($fiscalEntity);
        $active = [];
        foreach ($all as $category) {
            if ($category->isActive()) {
                $active[] = $category;
            }
        }
        $suppliers = [];
        foreach ($this->contacts->search($fiscalEntity, '') as $contact) {
            if ($contact->isSupplier()) {
                $suppliers[] = $contact;
            }
        }

        $expenses = $this->expenses->forEntity($fiscalEntity);

        return $this->render('gasto/lista.html.twig', [
            'entityReady' => true,
            'categories' => $active,
            'allCategories' => $all,
            'suppliers' => $suppliers,
            'expenses' => $expenses,
            'duplicateNotes' => $this->duplicateNotes($expenses),
            'kinds' => ExpenseKind::labels(),
            'methods' => PaymentMethod::labels(),
            'error' => $error,
            'savedExpense' => '1' === $request->query->getString('gasto'),
            'savedCategory' => '1' === $request->query->getString('categoria'),
            'savedDeduction' => '1' === $request->query->getString('deduccion'),
            'savedPayment' => '1' === $request->query->getString('pago'),
            'savedReview' => '1' === $request->query->getString('revision'),
            'savedRecurrence' => '1' === $request->query->getString('recurrencia'),
            'savedProposal' => '1' === $request->query->getString('propuesta'),
            'paymentToken' => Uuid::v7()->toRfc4122(),
        ], new Response(status: null === $error ? Response::HTTP_OK : Response::HTTP_UNPROCESSABLE_ENTITY));
    }

    /**
     * @param list<Expense> $expenses
     *
     * @return array<string, list<Expense>>
     */
    private function duplicateNotes(array $expenses): array
    {
        $notes = [];
        foreach ($expenses as $expense) {
            $matches = ExpenseDuplicates::matching($expense, $expenses);
            if ([] !== $matches) {
                $notes[$expense->id()] = $matches;
            }
        }

        return $notes;
    }

    private function date(string $raw): \DateTimeImmutable
    {
        $date = CivilDate::parse($raw);
        if (null === $date) {
            throw new InvalidExpense(InvalidExpense::DATE);
        }

        return $date;
    }

    private function currentAccount(): ?Account
    {
        $account = $this->getUser();

        return $account instanceof Account ? $account : null;
    }
}
