<?php

declare(strict_types=1);

namespace App\Expense;

final class ReviewReason
{
    public static function canonical(string $raw): string
    {
        $reason = trim(preg_replace('/\s+/u', ' ', $raw) ?? '');
        if ('' === $reason || mb_strlen($reason) > 240) {
            throw new InvalidExpense(InvalidExpense::REVIEW_REASON);
        }

        return $reason;
    }
}
