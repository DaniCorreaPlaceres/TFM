<?php

declare(strict_types=1);

namespace App\Expense;

use App\Entity\Account;
use App\Entity\Expense;
use App\Entity\FiscalEntity;
use App\Identity\RegistrationStore;
use Symfony\Component\Clock\ClockInterface;

final class ProposeExpense
{
    public function __construct(
        private readonly ExpenseAccess $access,
        private readonly ExpenseRepository $expenses,
        private readonly RegistrationStore $store,
        private readonly ClockInterface $clock,
    ) {
    }

    public function __invoke(Account $account, FiscalEntity $fiscalEntity, string $expenseId): Expense
    {
        $this->access->holder($account, $fiscalEntity);
        $source = $this->expenses->findForEntity($fiscalEntity, $expenseId);
        if (null === $source) {
            throw new InvalidExpense(InvalidExpense::FORBIDDEN);
        }
        if (!$source->isCurrent()) {
            throw new InvalidExpense(InvalidExpense::FROZEN);
        }
        if (!$source->isMonthly()) {
            throw new InvalidExpense(InvalidExpense::RECURRENCE);
        }
        $issuedOn = NextMonth::of($source->issuedOn());
        foreach ($this->expenses->forEntity($fiscalEntity) as $existing) {
            $origin = $existing->sourceExpense();
            if (null !== $origin && $origin->id() === $source->id() && $existing->issuedOn()->format('Y-m-d') === $issuedOn->format('Y-m-d')) {
                throw new InvalidExpense(InvalidExpense::PROPOSAL);
            }
        }
        $proposal = Expense::register(
            $fiscalEntity,
            $source->contact(),
            $source->category(),
            $source->kind(),
            $source->paymentMethod(),
            ProposalNumber::from($source->documentNo()),
            $issuedOn,
            ExpenseLines::parse($this->rows($source)),
            $this->clock->now(),
        );
        $proposal->linkSource($source);
        $this->store->commit(function () use ($proposal): void {
            $this->expenses->save($proposal);
        });

        return $proposal;
    }

    /**
     * @return list<array{description: string, quantity: string, unitPrice: string, taxRate: string, withholdingRate: string}>
     */
    private function rows(Expense $source): array
    {
        $rows = [];
        foreach ($source->lines() as $line) {
            $rows[] = [
                'description' => $line->description(),
                'quantity' => $line->quantity(),
                'unitPrice' => $line->unitPrice(),
                'taxRate' => $line->taxRate(),
                'withholdingRate' => $line->withholdingRate(),
            ];
        }

        return $rows;
    }
}
