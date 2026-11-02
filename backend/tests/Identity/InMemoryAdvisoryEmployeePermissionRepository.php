<?php

declare(strict_types=1);

namespace App\Tests\Identity;

use App\Entity\AdvisoryEmployeePermission;
use App\Entity\AdvisoryFirm;
use App\Entity\AdvisoryMembership;
use App\Identity\AdvisoryEmployeePermissionRepository;

final class InMemoryAdvisoryEmployeePermissionRepository implements AdvisoryEmployeePermissionRepository
{
    /** @var list<AdvisoryEmployeePermission> */
    private array $permissions = [];

    public function save(AdvisoryEmployeePermission $permission): void
    {
        $this->permissions[] = $permission;
    }

    public function remove(AdvisoryEmployeePermission $permission): void
    {
        $this->permissions = array_values(array_filter(
            $this->permissions,
            static fn (AdvisoryEmployeePermission $stored): bool => $stored->id() !== $permission->id(),
        ));
    }

    public function findForMembership(AdvisoryMembership $membership): ?AdvisoryEmployeePermission
    {
        foreach ($this->permissions as $permission) {
            if ($permission->membership()->id() === $membership->id()) {
                return $permission;
            }
        }

        return null;
    }

    public function grantedMembershipIds(AdvisoryFirm $advisoryFirm): array
    {
        $ids = [];
        foreach ($this->permissions as $permission) {
            if ($permission->membership()->advisoryFirm()->id() === $advisoryFirm->id()) {
                $ids[] = $permission->membership()->id();
            }
        }

        return $ids;
    }
}
