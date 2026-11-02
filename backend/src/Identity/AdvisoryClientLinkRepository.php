<?php

declare(strict_types=1);

namespace App\Identity;

use App\Entity\AdvisoryClientLink;
use App\Entity\AdvisoryFirm;
use App\Entity\FiscalEntity;

interface AdvisoryClientLinkRepository
{
    public function save(AdvisoryClientLink $link): void;

    public function findByFiscalEntity(FiscalEntity $fiscalEntity): ?AdvisoryClientLink;

    /**
     * @return list<AdvisoryClientLink>
     */
    public function findByFirm(AdvisoryFirm $advisoryFirm): array;
}
