<?php

declare(strict_types=1);

namespace App\Identity;

interface PasswordResetLinkFactory
{
    public function url(string $token): string;
}
