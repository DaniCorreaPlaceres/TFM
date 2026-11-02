<?php

declare(strict_types=1);

namespace App\Identity;

use App\Entity\AdvisoryEmployeePermission;
use App\Entity\AdvisoryFirm;
use App\Entity\AdvisoryMembership;

interface AdvisoryEmployeePermissionRepository
{
    public function save(AdvisoryEmployeePermission $permission): void;

    public function remove(AdvisoryEmployeePermission $permission): void;

    public function findForMembership(AdvisoryMembership $membership): ?AdvisoryEmployeePermission;

    /**
     * @return list<string>
     */
    public function grantedMembershipIds(AdvisoryFirm $advisoryFirm): array;
}
