<?php

declare(strict_types=1);

namespace App\Income;

use App\Entity\Account;
use App\Entity\FiscalEntity;
use App\Entity\Income;
use App\Entity\IncomeAction;
use App\Identity\RegistrationStore;
use Symfony\Component\Clock\ClockInterface;

final class CollectIncome
{
    public function __construct(
        private readonly IncomeAccess $access,
        private readonly IncomeRepository $incomes,
        private readonly IncomeActionRepository $actions,
        private readonly RegistrationStore $store,
        private readonly ClockInterface $clock,
    ) {
    }

    public function __invoke(
        Account $account,
        FiscalEntity $fiscalEntity,
        string $incomeId,
        int $version,
        string $amount,
        \DateTimeImmutable $paidOn,
        string $method,
        string $reference,
        string $token,
    ): Income {
        $income = $this->access->income($account, $fiscalEntity, $incomeId);
        PaymentToken::assert($token);
        if (null !== $income->findPayment($token)) {
            return $income;
        }
        $this->access->version($income, $version);
        $this->access->open($fiscalEntity, $income->issuedOn());
        $payment = $income->collect($amount, $paidOn, $method, $reference, $token);
        $action = IncomeAction::record(
            $income,
            $account,
            'cobro',
            'Cobro de '.Euro::format($payment->amount()).'.',
            $this->clock->now(),
        );
        $this->store->commit(function () use ($income, $action): void {
            $this->incomes->save($income);
            $this->actions->save($action);
        });

        return $income;
    }
}
