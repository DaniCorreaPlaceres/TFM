<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'password_reset')]
class PasswordReset
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(length: 36)]
        private string $id,
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Account $account,
        #[ORM\Column(length: 64, unique: true)]
        private string $tokenHash,
        #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
        private \DateTimeImmutable $expiresAt,
        #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
        private ?\DateTimeImmutable $usedAt = null,
    ) {
    }

    public static function issue(Account $account, string $tokenHash, \DateTimeImmutable $expiresAt): self
    {
        return new self(Uuid::v7()->toRfc4122(), $account, $tokenHash, $expiresAt);
    }

    public function id(): string
    {
        return $this->id;
    }

    public function account(): Account
    {
        return $this->account;
    }

    public function tokenHash(): string
    {
        return $this->tokenHash;
    }

    public function isUsableAt(\DateTimeImmutable $now): bool
    {
        return null === $this->usedAt && $now < $this->expiresAt;
    }

    public function use(\DateTimeImmutable $now): void
    {
        if (!$this->isUsableAt($now)) {
            throw new \LogicException('El enlace no se puede usar.');
        }

        $this->usedAt = $now;
    }
}
