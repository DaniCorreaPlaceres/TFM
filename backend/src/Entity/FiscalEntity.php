<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'fiscal_entity')]
class FiscalEntity
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(length: 36)]
        private string $id,
        #[ORM\Column(length: 180)]
        private string $legalName,
        #[ORM\Column(length: 20)]
        private string $taxId,
        #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
        private \DateTimeImmutable $createdAt,
    ) {
    }

    public static function open(string $legalName, string $taxId, \DateTimeImmutable $now): self
    {
        return new self(Uuid::v7()->toRfc4122(), $legalName, $taxId, $now);
    }

    public function id(): string
    {
        return $this->id;
    }

    public function legalName(): string
    {
        return $this->legalName;
    }

    public function taxId(): string
    {
        return $this->taxId;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
