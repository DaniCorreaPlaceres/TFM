<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'income_attachment')]
class IncomeAttachment
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(length: 36)]
        private string $id,
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private FiscalEntity $fiscalEntity,
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Income $income,
        #[ORM\Column(length: 180)]
        private string $fileName,
        #[ORM\Column(length: 80)]
        private string $mimeType,
        #[ORM\Column(length: 255)]
        private string $objectKey,
        #[ORM\Column]
        private int $bytes,
        #[ORM\Column(length: 64)]
        private string $sha256,
        #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
        private \DateTimeImmutable $createdAt,
    ) {
    }

    public static function store(
        Income $income,
        string $fileName,
        string $mimeType,
        string $objectKey,
        int $bytes,
        string $sha256,
        \DateTimeImmutable $now,
    ): self {
        return new self(
            Uuid::v7()->toRfc4122(),
            $income->fiscalEntity(),
            $income,
            $fileName,
            $mimeType,
            $objectKey,
            $bytes,
            $sha256,
            $now,
        );
    }

    public function id(): string
    {
        return $this->id;
    }

    public function income(): Income
    {
        return $this->income;
    }

    public function fileName(): string
    {
        return $this->fileName;
    }

    public function mimeType(): string
    {
        return $this->mimeType;
    }

    public function objectKey(): string
    {
        return $this->objectKey;
    }

    public function bytes(): int
    {
        return $this->bytes;
    }

    public function sha256(): string
    {
        return $this->sha256;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function belongsTo(Income $income): bool
    {
        return $this->income->id() === $income->id() && $this->fiscalEntity->id() === $income->fiscalEntity()->id();
    }
}
