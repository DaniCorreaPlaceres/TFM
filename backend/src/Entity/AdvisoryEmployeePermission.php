<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'advisory_employee_permission')]
#[ORM\UniqueConstraint(name: 'UNIQ_advisory_employee_permission_membership', columns: ['membership_id'])]
class AdvisoryEmployeePermission
{
    public const string VIEW_ENTITIES = 'ver_entidades';

    public function __construct(
        #[ORM\Id]
        #[ORM\Column(length: 36)]
        private string $id,
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private AdvisoryMembership $membership,
        #[ORM\Column(length: 32)]
        private string $permissionCode,
        #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
        private \DateTimeImmutable $createdAt,
    ) {
    }

    public static function viewEntities(AdvisoryMembership $membership, \DateTimeImmutable $now): self
    {
        return new self(Uuid::v7()->toRfc4122(), $membership, self::VIEW_ENTITIES, $now);
    }

    public function id(): string
    {
        return $this->id;
    }

    public function membership(): AdvisoryMembership
    {
        return $this->membership;
    }

    public function permissionCode(): string
    {
        return $this->permissionCode;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
