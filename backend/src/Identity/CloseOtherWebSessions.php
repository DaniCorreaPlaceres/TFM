<?php

declare(strict_types=1);

namespace App\Identity;

use App\Entity\Account;

final class CloseOtherWebSessions
{
    public function __construct(
        private readonly AccountRepository $accounts,
        private readonly RegistrationStore $store,
    ) {
    }

    public function __invoke(Account $account): void
    {
        $account->closeOtherSessions();
        $this->store->commit(function () use ($account): void {
            $this->accounts->save($account);
        });
    }
}
