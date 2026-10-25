<?php

declare(strict_types=1);

namespace App\Tests\Identity;

use App\Entity\Account;
use App\Identity\AccountRepository;

final class InMemoryAccountRepository implements AccountRepository
{
    /** @var array<string, Account> */
    private array $accounts = [];

    public function findByEmail(string $email): ?Account
    {
        return $this->accounts[$email] ?? null;
    }

    public function save(Account $account): void
    {
        $this->accounts[$account->email()] = $account;
    }
}
