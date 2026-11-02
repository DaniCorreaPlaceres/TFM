<?php

declare(strict_types=1);

namespace App\Tests\Identity;

use App\Entity\Account;
use App\Entity\AdvisoryMembership;
use App\Identity\AcceptAdvisoryInvitation;
use App\Identity\AdvisoryInvitationLink;
use App\Identity\CreateAdvisoryFirm;
use App\Identity\InvalidAdvisory;
use App\Identity\InviteAdvisoryEmployee;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;

final class AdvisoryFirmTest extends TestCase
{
    public function testElNombreVacioNoCreaLaAsesoria(): void
    {
        $firms = new InMemoryAdvisoryFirmRepository();
        $create = $this->create($firms);

        try {
            $create($this->account('admin@example.com'), '  ', 'B0000001B');
            self::fail('Un nombre vacío no debe crear la asesoría.');
        } catch (InvalidAdvisory $exception) {
            self::assertSame(InvalidAdvisory::NAME, $exception->reason);
        }

        self::assertNull($firms->findById('no-existe'));
    }

    public function testLaInvitacionCaducaALosSieteDiasYNoExigeSegundoFactor(): void
    {
        $clock = new MockClock('2026-11-01T22:30:00Z');
        $memberships = new InMemoryAdvisoryMembershipRepository();
        $invitations = new InMemoryAdvisoryInvitationRepository();
        $create = $this->create(new InMemoryAdvisoryFirmRepository(), $memberships, $clock);
        $admin = $this->account('admin@example.com');
        $firm = $create($admin, ' Norte Asesores ', 'b-0000001b');
        self::assertSame('Norte Asesores', $firm->name());
        self::assertSame('B0000001B', $firm->taxId());

        $invite = $this->invite($memberships, $invitations, $clock);
        $invite($admin, $firm->id(), 'Empleado@example.com');
        $first = $this->tokenFrom(RecordingAccountMailer::$messages[0]['url']);
        $invite($admin, $firm->id(), 'empleado@example.com');
        $second = $this->tokenFrom(RecordingAccountMailer::$messages[1]['url']);

        $accept = new AcceptAdvisoryInvitation($invitations, $memberships, new InMemoryRegistrationStore(), $clock);
        $other = $this->account('otra@example.com');
        try {
            $accept->preview($other, $second);
            self::fail('La invitación era para otro correo.');
        } catch (InvalidAdvisory $exception) {
            self::assertSame(InvalidAdvisory::WRONG_ACCOUNT, $exception->reason);
        }

        self::assertFalse($admin->requiresSecondFactor());
        $employee = $this->account('empleado@example.com');
        self::assertFalse($employee->requiresSecondFactor());
        $membership = $accept($employee, $second);
        self::assertSame(AdvisoryMembership::EMPLOYEE, $membership->role());

        try {
            $accept($employee, $first);
            self::fail('La invitación anterior tenía que quedar inservible.');
        } catch (InvalidAdvisory $exception) {
            self::assertSame(InvalidAdvisory::UNUSABLE, $exception->reason);
        }

        $invite($admin, $firm->id(), 'nueva@example.com');
        $third = $this->tokenFrom(RecordingAccountMailer::$messages[2]['url']);
        $clock->modify('+8 days');
        try {
            $accept($this->account('nueva@example.com'), $third);
            self::fail('La invitación tenía que caducar a los 7 días.');
        } catch (InvalidAdvisory $exception) {
            self::assertSame(InvalidAdvisory::UNUSABLE, $exception->reason);
        }

        try {
            $invite($employee, $firm->id(), 'otra-persona@example.com');
            self::fail('Una empleada no debe invitar.');
        } catch (InvalidAdvisory $exception) {
            self::assertSame(InvalidAdvisory::FORBIDDEN, $exception->reason);
        }
    }

    private function create(
        InMemoryAdvisoryFirmRepository $firms,
        ?InMemoryAdvisoryMembershipRepository $memberships = null,
        ?MockClock $clock = null,
    ): CreateAdvisoryFirm {
        return new CreateAdvisoryFirm(
            $firms,
            $memberships ?? new InMemoryAdvisoryMembershipRepository(),
            new InMemoryRegistrationStore(),
            $clock ?? new MockClock('2026-11-01T22:30:00Z'),
        );
    }

    private function invite(
        InMemoryAdvisoryMembershipRepository $memberships,
        InMemoryAdvisoryInvitationRepository $invitations,
        MockClock $clock,
    ): InviteAdvisoryEmployee {
        RecordingAccountMailer::reset();

        return new InviteAdvisoryEmployee(
            $memberships,
            $invitations,
            new InMemoryRegistrationStore(),
            new RecordingAccountMailer(),
            new class implements AdvisoryInvitationLink {
                public function url(string $token): string
                {
                    return 'https://aselto.test/asesorias/invitacion/'.$token;
                }
            },
            $clock,
        );
    }

    private function account(string $email): Account
    {
        $account = Account::register($email, 'hash', new \DateTimeImmutable('2026-11-01T22:30:00Z'));
        $account->verify(new \DateTimeImmutable('2026-11-01T22:30:00Z'));

        return $account;
    }

    private function tokenFrom(string $url): string
    {
        $token = basename($url);
        self::assertSame(64, strlen($token));

        return $token;
    }
}
