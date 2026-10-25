<?php

declare(strict_types=1);

namespace App\Identity;

use Doctrine\ORM\EntityManagerInterface;

final class DoctrineRegistrationStore implements RegistrationStore
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function commit(\Closure $operation): void
    {
        $this->entityManager->wrapInTransaction($operation);
    }
}
