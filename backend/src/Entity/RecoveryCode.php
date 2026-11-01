<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'recovery_code')]
class RecoveryCode
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(length: 36)]
        private string $id,
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Account $account,
        #[ORM\Column(length: 64)]
        private string $codeHash,
        #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
        private \DateTimeImmutable $createdAt,
        #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
        private ?\DateTimeImmutable $usedAt = null,
    ) {
    }

    public static function issue(Account $account, string $codeHash, \DateTimeImmutable $now): self
    {
        return new self(Uuid::v7()->toRfc4122(), $account, $codeHash, $now);
    }

    public function id(): string
    {
        return $this->id;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function belongsTo(Account $account): bool
    {
        return $this->account->id() === $account->id();
    }

    public function matches(string $codeHash): bool
    {
        return hash_equals($this->codeHash, $codeHash);
    }

    public function isUsable(): bool
    {
        return null === $this->usedAt;
    }

    public function use(\DateTimeImmutable $now): void
    {
        if (!$this->isUsable()) {
            throw new \LogicException('El código de recuperación ya se usó.');
        }

        $this->usedAt = $now;
    }
}
