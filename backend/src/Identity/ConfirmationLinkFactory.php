<?php

declare(strict_types=1);

namespace App\Identity;

interface ConfirmationLinkFactory
{
    public function url(string $token): string;
}
