<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'advisory_client_link')]
#[ORM\UniqueConstraint(name: 'UNIQ_advisory_client_link_entity', columns: ['fiscal_entity_id'])]
class AdvisoryClientLink
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(length: 36)]
        private string $id,
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private AdvisoryFirm $advisoryFirm,
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private FiscalEntity $fiscalEntity,
        #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
        private \DateTimeImmutable $createdAt,
    ) {
    }

    public static function open(AdvisoryFirm $advisoryFirm, FiscalEntity $fiscalEntity, \DateTimeImmutable $now): self
    {
        return new self(Uuid::v7()->toRfc4122(), $advisoryFirm, $fiscalEntity, $now);
    }

    public function id(): string
    {
        return $this->id;
    }

    public function advisoryFirm(): AdvisoryFirm
    {
        return $this->advisoryFirm;
    }

    public function fiscalEntity(): FiscalEntity
    {
        return $this->fiscalEntity;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
