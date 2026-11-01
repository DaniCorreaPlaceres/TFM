<?php

declare(strict_types=1);

namespace App\Tests\Identity;

use App\Entity\FiscalEntity;
use App\Identity\FiscalEntityRepository;

final class InMemoryFiscalEntityRepository implements FiscalEntityRepository
{
    /** @var array<string, FiscalEntity> */
    private array $entities = [];

    public function findById(string $id): ?FiscalEntity
    {
        return $this->entities[$id] ?? null;
    }

    public function save(FiscalEntity $fiscalEntity): void
    {
        $this->entities[$fiscalEntity->id()] = $fiscalEntity;
    }
}
