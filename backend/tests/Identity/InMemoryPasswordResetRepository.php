<?php

declare(strict_types=1);

namespace App\Tests\Identity;

use App\Entity\Account;
use App\Entity\PasswordReset;
use App\Identity\PasswordResetRepository;

final class InMemoryPasswordResetRepository implements PasswordResetRepository
{
    /** @var array<string, PasswordReset> */
    private array $resets = [];

    public function findByTokenHash(string $tokenHash): ?PasswordReset
    {
        return $this->resets[$tokenHash] ?? null;
    }

    public function findByAccount(Account $account): array
    {
        return array_values(array_filter(
            $this->resets,
            static fn (PasswordReset $reset): bool => $reset->account()->id() === $account->id(),
        ));
    }

    public function save(PasswordReset $reset): void
    {
        $this->resets[$reset->tokenHash()] = $reset;
    }
}
