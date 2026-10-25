<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'account')]
class Account
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(length: 36)]
        private string $id,
        #[ORM\Column(length: 180, unique: true)]
        private string $email,
        #[ORM\Column(length: 255)]
        private string $passwordHash,
        #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
        private \DateTimeImmutable $createdAt,
        #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
        private ?\DateTimeImmutable $verifiedAt = null,
    ) {
    }

    public static function register(string $email, string $passwordHash, \DateTimeImmutable $now): self
    {
        return new self(Uuid::v7()->toRfc4122(), $email, $passwordHash, $now);
    }

    public function id(): string
    {
        return $this->id;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function passwordHash(): string
    {
        return $this->passwordHash;
    }

    public function allowsFiscalAccess(): bool
    {
        return null !== $this->verifiedAt;
    }

    public function verify(\DateTimeImmutable $now): void
    {
        $this->verifiedAt ??= $now;
    }
}
