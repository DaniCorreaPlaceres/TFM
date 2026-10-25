<?php

declare(strict_types=1);

namespace App\Tests\Identity;

use App\Entity\EmailConfirmation;
use App\Identity\EmailConfirmationRepository;

final class InMemoryEmailConfirmationRepository implements EmailConfirmationRepository
{
    /** @var array<string, EmailConfirmation> */
    private array $confirmations = [];

    public function findByTokenHash(string $tokenHash): ?EmailConfirmation
    {
        return $this->confirmations[$tokenHash] ?? null;
    }

    public function save(EmailConfirmation $confirmation): void
    {
        $this->confirmations[$confirmation->tokenHash()] = $confirmation;
    }
}
