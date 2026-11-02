<?php

declare(strict_types=1);

namespace App\Identity;

use App\Entity\AdvisoryClientLink;
use App\Entity\AdvisoryFirm;
use App\Entity\FiscalEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AdvisoryClientLink>
 */
final class DoctrineAdvisoryClientLinkRepository extends ServiceEntityRepository implements AdvisoryClientLinkRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdvisoryClientLink::class);
    }

    public function save(AdvisoryClientLink $link): void
    {
        $this->getEntityManager()->persist($link);
    }

    public function findByFiscalEntity(FiscalEntity $fiscalEntity): ?AdvisoryClientLink
    {
        return $this->findOneBy(['fiscalEntity' => $fiscalEntity]);
    }

    public function findByFirm(AdvisoryFirm $advisoryFirm): array
    {
        return $this->findBy(['advisoryFirm' => $advisoryFirm]);
    }
}
