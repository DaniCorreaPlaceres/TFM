<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Account;
use App\Entity\FiscalEntity;
use App\Expense\DeactivateExpenseCategory;
use App\Expense\EnsureExpenseCategories;
use App\Expense\ExpenseCategoryRepository;
use App\Expense\ExpenseKind;
use App\Expense\ExpenseMessages;
use App\Expense\ExpenseRepository;
use App\Expense\InvalidExpense;
use App\Expense\PaymentMethod;
use App\Expense\RegisterExpense;
use App\Expense\SaveExpenseCategory;
use App\Identity\ActiveFiscalEntity;
use App\Income\CivilDate;
use App\Income\ContactRepository;
use App\Income\PostedLines;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ExpenseController extends AbstractController
{
    public function __construct(
        private readonly ActiveFiscalEntity $active,
        private readonly EnsureExpenseCategories $ensure,
        private readonly SaveExpenseCategory $saveCategory,
        private readonly DeactivateExpenseCategory $deactivate,
        private readonly RegisterExpense $register,
        private readonly ExpenseCategoryRepository $categories,
        private readonly ExpenseRepository $expenses,
        private readonly ContactRepository $contacts,
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

        return $this->render('gasto/lista.html.twig', [
            'entityReady' => true,
            'categories' => $active,
            'allCategories' => $all,
            'suppliers' => $suppliers,
            'expenses' => $this->expenses->forEntity($fiscalEntity),
            'kinds' => ExpenseKind::labels(),
            'methods' => PaymentMethod::labels(),
            'error' => $error,
            'savedExpense' => '1' === $request->query->getString('gasto'),
            'savedCategory' => '1' === $request->query->getString('categoria'),
        ], new Response(status: null === $error ? Response::HTTP_OK : Response::HTTP_UNPROCESSABLE_ENTITY));
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
