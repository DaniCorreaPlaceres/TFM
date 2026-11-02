<?php

declare(strict_types=1);

namespace App\Identity;

use App\Entity\AdvisoryFirm;

interface AdvisoryFirmRepository
{
    public function findById(string $id): ?AdvisoryFirm;

    public function save(AdvisoryFirm $advisoryFirm): void;
}
