<?php

declare(strict_types=1);

namespace App\Identity;

use App\Entity\FiscalEntity;

interface FiscalEntityRepository
{
    public function findById(string $id): ?FiscalEntity;

    public function save(FiscalEntity $fiscalEntity): void;
}
