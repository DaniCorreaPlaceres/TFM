<?php

declare(strict_types=1);

namespace App\Tests\Income;

use App\Entity\ClosedQuarter;
use App\Entity\FiscalEntity;
use App\Income\ClosedQuarterRepository;

final class InMemoryClosedQuarterRepository implements ClosedQuarterRepository
{
    /** @var list<ClosedQuarter> */
    private array $quarters = [];

    public function save(ClosedQuarter $closedQuarter): void
    {
        $this->quarters[] = $closedQuarter;
    }

    public function findOne(string $fiscalEntityId, int $year, int $quarter): ?ClosedQuarter
    {
        foreach ($this->quarters as $closed) {
            if ($closed->year() === $year && $closed->quarter() === $quarter && $closed->fiscalEntity()->id() === $fiscalEntityId) {
                return $closed;
            }
        }

        return null;
    }

    public function forEntity(FiscalEntity $fiscalEntity): array
    {
        $found = [];
        foreach ($this->quarters as $closed) {
            if ($closed->fiscalEntity()->id() === $fiscalEntity->id()) {
                $found[] = $closed;
            }
        }

        return $found;
    }
}
