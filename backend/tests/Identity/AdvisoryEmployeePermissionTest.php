<?php

declare(strict_types=1);

namespace App\Tests\Identity;

use App\Entity\Account;
use App\Entity\AdvisoryFirm;
use App\Entity\AdvisoryMembership;
use App\Identity\InvalidAdvisory;
use App\Identity\SetEmployeeEntityView;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;

final class AdvisoryEmployeePermissionTest extends TestCase
{
    public function testSoloLaAdministradoraConcedeYRetiraVerLasEntidades(): void
    {
        $clock = new MockClock('2026-11-02T01:50:00Z');
        $memberships = new InMemoryAdvisoryMembershipRepository();
        $permissions = new InMemoryAdvisoryEmployeePermissionRepository();
        $admin = $this->account('admin-permiso@example.com');
        $employee = $this->account('empleada-permiso@example.com');
        $firm = AdvisoryFirm::open('Norte Permisos', 'B0000005B', $clock->now());
        $adminMembership = AdvisoryMembership::administer($firm, $admin);
        $employeeMembership = AdvisoryMembership::employ($firm, $employee);
        $memberships->save($adminMembership);
        $memberships->save($employeeMembership);
        $otherAdmin = $this->account('otra-permiso@example.com');
        $otherFirm = AdvisoryFirm::open('Sur Permisos', 'B0000007B', $clock->now());
        $memberships->save(AdvisoryMembership::administer($otherFirm, $otherAdmin));
        $set = new SetEmployeeEntityView($memberships, $permissions, new InMemoryRegistrationStore(), $clock);

        try {
            $set($employee, $firm->id(), $employeeMembership->id(), true);
            self::fail('Una empleada no concede el permiso.');
        } catch (InvalidAdvisory $exception) {
            self::assertSame(InvalidAdvisory::FORBIDDEN, $exception->reason);
        }

        try {
            $set($otherAdmin, $firm->id(), $employeeMembership->id(), true);
            self::fail('Otra asesoría no concede el permiso.');
        } catch (InvalidAdvisory $exception) {
            self::assertSame(InvalidAdvisory::FORBIDDEN, $exception->reason);
        }

        try {
            $set($admin, $firm->id(), $adminMembership->id(), true);
            self::fail('La administradora no necesita esa concesión.');
        } catch (InvalidAdvisory $exception) {
            self::assertSame(InvalidAdvisory::FORBIDDEN, $exception->reason);
        }

        self::assertNull($permissions->findForMembership($employeeMembership));
        $set($admin, $firm->id(), $employeeMembership->id(), true);
        $set($admin, $firm->id(), $employeeMembership->id(), true);
        self::assertSame([$employeeMembership->id()], $permissions->grantedMembershipIds($firm));
        $set($admin, $firm->id(), $employeeMembership->id(), false);
        self::assertSame([], $permissions->grantedMembershipIds($firm));
        self::assertNull($permissions->findForMembership($employeeMembership));
    }

    private function account(string $email): Account
    {
        $account = Account::register($email, 'hash', new \DateTimeImmutable('2026-11-02T01:50:00Z'));
        $account->verify(new \DateTimeImmutable('2026-11-02T01:50:00Z'));

        return $account;
    }
}
