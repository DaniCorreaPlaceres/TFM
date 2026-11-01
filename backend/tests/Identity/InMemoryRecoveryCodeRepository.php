<?php

declare(strict_types=1);

namespace App\Tests\Identity;

use App\Entity\Account;
use App\Entity\RecoveryCode;
use App\Identity\RecoveryCodeRepository;

final class InMemoryRecoveryCodeRepository implements RecoveryCodeRepository
{
    /** @var list<RecoveryCode> */
    private array $codes = [];

    public function save(RecoveryCode $code): void
    {
        foreach ($this->codes as $index => $existing) {
            if ($existing->id() === $code->id()) {
                $this->codes[$index] = $code;

                return;
            }
        }

        $this->codes[] = $code;
    }

    public function findUsable(Account $account, string $codeHash): ?RecoveryCode
    {
        foreach ($this->codes as $code) {
            if ($code->belongsTo($account) && $code->matches($codeHash) && $code->isUsable()) {
                return $code;
            }
        }

        return null;
    }

    public function deleteFor(Account $account): void
    {
        $remaining = [];
        foreach ($this->codes as $code) {
            if (!$code->belongsTo($account)) {
                $remaining[] = $code;
            }
        }

        $this->codes = $remaining;
    }
}
