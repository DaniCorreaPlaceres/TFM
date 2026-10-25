<?php

declare(strict_types=1);

namespace App\Identity;

interface PwnedPasswordList
{
    /**
     * @throws PwnedPasswordLookupFailed
     */
    public function contains(string $password): bool;
}
