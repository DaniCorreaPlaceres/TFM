<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'contact')]
#[ORM\UniqueConstraint(name: 'UNIQ_contact_entity_tax', columns: ['fiscal_entity_id', 'tax_id'])]
class Contact
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(length: 36)]
        private string $id,
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private FiscalEntity $fiscalEntity,
        #[ORM\Column(length: 180)]
        private string $displayName,
        #[ORM\Column(length: 20)]
        private string $taxId,
        #[ORM\Column(name: 'is_client')]
        private bool $client,
        #[ORM\Column(name: 'is_supplier')]
        private bool $supplier,
        #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
        private \DateTimeImmutable $createdAt,
    ) {
    }

    public static function open(
        FiscalEntity $fiscalEntity,
        string $displayName,
        string $taxId,
        bool $client,
        bool $supplier,
        \DateTimeImmutable $now,
    ): self {
        return new self(Uuid::v7()->toRfc4122(), $fiscalEntity, $displayName, $taxId, $client, $supplier, $now);
    }

    public function id(): string
    {
        return $this->id;
    }

    public function fiscalEntity(): FiscalEntity
    {
        return $this->fiscalEntity;
    }

    public function belongsTo(FiscalEntity $fiscalEntity): bool
    {
        return $this->fiscalEntity->id() === $fiscalEntity->id();
    }

    public function displayName(): string
    {
        return $this->displayName;
    }

    public function taxId(): string
    {
        return $this->taxId;
    }

    public function isClient(): bool
    {
        return $this->client;
    }

    public function isSupplier(): bool
    {
        return $this->supplier;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function rename(string $displayName, string $taxId, bool $client, bool $supplier): void
    {
        $this->displayName = $displayName;
        $this->taxId = $taxId;
        $this->client = $client;
        $this->supplier = $supplier;
    }

    public function grantClient(): void
    {
        $this->client = true;
    }
}
