<?php

declare(strict_types=1);

namespace App\Identity;

use App\Entity\Account;
use App\Entity\PasswordReset;

interface PasswordResetRepository
{
    public function findByTokenHash(string $tokenHash): ?PasswordReset;

    /**
     * @return list<PasswordReset>
     */
    public function findByAccount(Account $account): array;

    public function save(PasswordReset $reset): void;
}
