<?php

declare(strict_types=1);

namespace App\Tests\Identity;

use App\Entity\AdvisoryClientLink;
use App\Entity\AdvisoryFirm;
use App\Entity\FiscalEntity;
use App\Identity\AdvisoryClientLinkRepository;

final class InMemoryAdvisoryClientLinkRepository implements AdvisoryClientLinkRepository
{
    /** @var list<AdvisoryClientLink> */
    private array $links = [];

    public function save(AdvisoryClientLink $link): void
    {
        $this->links[] = $link;
    }

    public function findByFiscalEntity(FiscalEntity $fiscalEntity): ?AdvisoryClientLink
    {
        foreach ($this->links as $link) {
            if ($link->fiscalEntity()->id() === $fiscalEntity->id()) {
                return $link;
            }
        }

        return null;
    }

    public function findByFirm(AdvisoryFirm $advisoryFirm): array
    {
        return array_values(array_filter(
            $this->links,
            static fn (AdvisoryClientLink $link): bool => $link->advisoryFirm()->id() === $advisoryFirm->id(),
        ));
    }
}
