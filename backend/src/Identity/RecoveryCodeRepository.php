<?php

declare(strict_types=1);

namespace App\Identity;

use App\Entity\Account;
use App\Entity\RecoveryCode;

interface RecoveryCodeRepository
{
    public function save(RecoveryCode $code): void;

    public function findUsable(Account $account, string $codeHash): ?RecoveryCode;

    public function deleteFor(Account $account): void;
}
