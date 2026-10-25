<?php

declare(strict_types=1);

namespace App\Identity;

use App\Entity\EmailConfirmation;

interface EmailConfirmationRepository
{
    public function findByTokenHash(string $tokenHash): ?EmailConfirmation;

    public function save(EmailConfirmation $confirmation): void;
}
