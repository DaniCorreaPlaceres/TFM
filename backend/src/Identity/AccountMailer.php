<?php

declare(strict_types=1);

namespace App\Identity;

interface AccountMailer
{
    public function sendConfirmation(string $to, string $url): void;

    public function sendPasswordReset(string $to, string $url): void;
}
