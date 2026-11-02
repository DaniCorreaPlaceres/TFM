<?php

declare(strict_types=1);

namespace App\Expense;

use App\Entity\Expense;
use App\Income\Amount;

final class ExpenseDuplicates
{
    /**
     * @param list<Expense> $others
     *
     * @return list<Expense>
     */
    public static function matching(Expense $expense, array $others): array
    {
        $found = [];
        foreach ($others as $other) {
            if ($other->id() === $expense->id() || !$other->belongsTo($expense->fiscalEntity())) {
                continue;
            }
            if ($expense->contact()->id() !== $other->contact()->id()) {
                continue;
            }
            $sameNumber = mb_strtolower($expense->documentNo()) === mb_strtolower($other->documentNo());
            $sameDay = $expense->issuedOn()->format('Y-m-d') === $other->issuedOn()->format('Y-m-d');
            $sameTotal = 0 === Amount::compare($expense->total(), $other->total());
            if ($sameNumber || ($sameDay && $sameTotal)) {
                $found[] = $other;
            }
        }

        return $found;
    }
}
