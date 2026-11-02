<?php

declare(strict_types=1);

namespace App\Income;

use App\Entity\Account;
use App\Entity\ClosedQuarter;
use App\Entity\FiscalEntity;
use App\Identity\RegistrationStore;
use Symfony\Component\Clock\ClockInterface;

final class CloseFiscalQuarter
{
    public function __construct(
        private readonly IncomeAccess $access,
        private readonly ClosedQuarterRepository $quarters,
        private readonly RegistrationStore $store,
        private readonly ClockInterface $clock,
    ) {
    }

    public function __invoke(Account $account, FiscalEntity $fiscalEntity, int $year, int $quarter): ClosedQuarter
    {
        $this->access->holder($account, $fiscalEntity);
        if ($year < 2000 || $year > 2100 || $quarter < 1 || $quarter > 4) {
            throw new InvalidIncome(InvalidIncome::PERIOD);
        }
        if (null !== $this->quarters->findOne($fiscalEntity->id(), $year, $quarter)) {
            throw new InvalidIncome(InvalidIncome::ALREADY_CLOSED);
        }
        $closed = ClosedQuarter::close($fiscalEntity, $year, $quarter, $account, $this->clock->now());
        $this->store->commit(function () use ($closed): void {
            $this->quarters->save($closed);
        });

        return $closed;
    }
}
