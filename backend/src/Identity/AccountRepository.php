<?php

declare(strict_types=1);

namespace App\Identity;

use App\Entity\Account;

interface AccountRepository
{
    public function findByEmail(string $email): ?Account;

    public function save(Account $account): void;
}
