<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'advisory_client_invitation')]
class AdvisoryClientInvitation
{
    public const string FROM_ENTITY = 'entidad';

    public const string FROM_FIRM = 'asesoria';

    public function __construct(
        #[ORM\Id]
        #[ORM\Column(length: 36)]
        private string $id,
        #[ORM\Column(length: 16)]
        private string $origin,
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
        private ?FiscalEntity $fiscalEntity,
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
        private ?AdvisoryFirm $advisoryFirm,
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Account $issuer,
        #[ORM\Column(length: 180)]
        private string $email,
        #[ORM\Column(length: 64, unique: true)]
        private string $tokenHash,
        #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
        private \DateTimeImmutable $expiresAt,
        #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
        private ?\DateTimeImmutable $usedAt = null,
    ) {
    }

    public static function fromEntity(
        FiscalEntity $fiscalEntity,
        Account $issuer,
        string $email,
        string $tokenHash,
        \DateTimeImmutable $expiresAt,
    ): self {
        return new self(Uuid::v7()->toRfc4122(), self::FROM_ENTITY, $fiscalEntity, null, $issuer, $email, $tokenHash, $expiresAt);
    }

    public static function fromFirm(
        AdvisoryFirm $advisoryFirm,
        Account $issuer,
        string $email,
        string $tokenHash,
        \DateTimeImmutable $expiresAt,
    ): self {
        return new self(Uuid::v7()->toRfc4122(), self::FROM_FIRM, null, $advisoryFirm, $issuer, $email, $tokenHash, $expiresAt);
    }

    public function id(): string
    {
        return $this->id;
    }

    public function origin(): string
    {
        return $this->origin;
    }

    public function fiscalEntity(): ?FiscalEntity
    {
        return $this->fiscalEntity;
    }

    public function advisoryFirm(): ?AdvisoryFirm
    {
        return $this->advisoryFirm;
    }

    public function issuer(): Account
    {
        return $this->issuer;
    }

    public function email(): string
    {
        return $this->email;
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
            throw new \LogicException('La invitación no se puede usar.');
        }

        $this->usedAt = $now;
    }
}
