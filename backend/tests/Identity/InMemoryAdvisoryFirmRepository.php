<?php

declare(strict_types=1);

namespace App\Tests\Identity;

use App\Entity\AdvisoryFirm;
use App\Identity\AdvisoryFirmRepository;

final class InMemoryAdvisoryFirmRepository implements AdvisoryFirmRepository
{
    /** @var array<string, AdvisoryFirm> */
    private array $firms = [];

    public function findById(string $id): ?AdvisoryFirm
    {
        return $this->firms[$id] ?? null;
    }

    public function save(AdvisoryFirm $advisoryFirm): void
    {
        $this->firms[$advisoryFirm->id()] = $advisoryFirm;
    }
}
