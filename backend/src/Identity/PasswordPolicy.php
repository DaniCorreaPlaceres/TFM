<?php

declare(strict_types=1);

namespace App\Identity;

final class PasswordPolicy
{
    public function __construct(private readonly PwnedPasswordList $pwnedPasswords)
    {
    }

    public function assertAcceptable(#[\SensitiveParameter] string $password): void
    {
        if (mb_strlen($password) < 12) {
            throw new InvalidRegistration(InvalidRegistration::SHORT);
        }

        try {
            if ($this->pwnedPasswords->contains($password)) {
                throw new InvalidRegistration(InvalidRegistration::PWNED);
            }
        } catch (PwnedPasswordLookupFailed) {
            throw new InvalidRegistration(InvalidRegistration::UNAVAILABLE);
        }
    }
}
