<?php

declare(strict_types=1);

namespace App\Tests\Identity;

use App\Entity\Account;
use App\Entity\AdvisoryClientInvitation;
use App\Entity\AdvisoryFirm;
use App\Entity\AdvisoryMembership;
use App\Entity\FiscalEntity;
use App\Entity\FiscalEntityMembership;
use App\Identity\AcceptAdvisoryClientInvitation;
use App\Identity\AdvisoryClientInvitationLink;
use App\Identity\InvalidAdvisory;
use App\Identity\InviteAdvisoryForEntity;
use App\Identity\InviteEntityForAdvisory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;

final class AdvisoryClientLinkTest extends TestCase
{
    public function testUnaEntidadNoPuedeTenerDosAsesoriasActivas(): void
    {
        $clock = new MockClock('2026-11-02T00:40:00Z');
        $links = new InMemoryAdvisoryClientLinkRepository();
        $invitations = new InMemoryAdvisoryClientInvitationRepository();
        $advisoryMemberships = new InMemoryAdvisoryMembershipRepository();
        $fiscalMemberships = new InMemoryFiscalEntityMembershipRepository();
        $titular = $this->account('titular@example.com');
        $entity = FiscalEntity::open('Taller Norte', 'B0000001B', $clock->now());
        $fiscalMemberships->save(FiscalEntityMembership::hold($entity, $titular));
        $admin = $this->account('admin@example.com');
        $firm = AdvisoryFirm::open('Norte Asesores', 'B0000002B', $clock->now());
        $advisoryMemberships->save(AdvisoryMembership::administer($firm, $admin));
        $otherAdmin = $this->account('otra@example.com');
        $otherFirm = AdvisoryFirm::open('Sur Asesores', 'B0000003B', $clock->now());
        $advisoryMemberships->save(AdvisoryMembership::administer($otherFirm, $otherAdmin));

        $invite = $this->inviteEntity($fiscalMemberships, $links, $invitations, $clock);
        $invite($titular, $entity, 'Admin@example.com');
        $first = $this->tokenFrom(RecordingAccountMailer::$messages[0]['url']);
        $invite($titular, $entity, 'admin@example.com');
        $second = $this->tokenFrom(RecordingAccountMailer::$messages[1]['url']);

        $accept = new AcceptAdvisoryClientInvitation(
            $invitations,
            $links,
            $advisoryMemberships,
            $fiscalMemberships,
            new InMemoryRegistrationStore(),
            $clock,
        );
        try {
            $accept->preview($titular, $second);
            self::fail('La invitación era para otro correo.');
        } catch (InvalidAdvisory $exception) {
            self::assertSame(InvalidAdvisory::WRONG_ACCOUNT, $exception->reason);
        }

        try {
            $accept($admin, $first, $firm->id());
            self::fail('La invitación anterior tenía que quedar inservible.');
        } catch (InvalidAdvisory $exception) {
            self::assertSame(InvalidAdvisory::UNUSABLE, $exception->reason);
        }

        $link = $accept($admin, $second, $firm->id());
        self::assertSame($firm->id(), $link->advisoryFirm()->id());
        self::assertSame($entity->id(), $link->fiscalEntity()->id());

        try {
            $invite($titular, $entity, 'otra@example.com');
            self::fail('La entidad ya tiene una asesoría activa.');
        } catch (InvalidAdvisory $exception) {
            self::assertSame(InvalidAdvisory::ALREADY_LINKED, $exception->reason);
        }

        $inviteFirm = $this->inviteFirm($advisoryMemberships, $invitations, $clock);
        $inviteFirm($otherAdmin, $otherFirm->id(), 'titular@example.com');
        $third = $this->tokenFrom(RecordingAccountMailer::$messages[0]['url']);
        try {
            $accept($titular, $third, $entity->id());
            self::fail('La segunda asesoría no debe vincularse.');
        } catch (InvalidAdvisory $exception) {
            self::assertSame(InvalidAdvisory::ALREADY_LINKED, $exception->reason);
        }
    }

    public function testLaAdministradoraInvitaYLaInvitacionCaduca(): void
    {
        $clock = new MockClock('2026-11-02T00:40:00Z');
        $invitations = new InMemoryAdvisoryClientInvitationRepository();
        $advisoryMemberships = new InMemoryAdvisoryMembershipRepository();
        $fiscalMemberships = new InMemoryFiscalEntityMembershipRepository();
        $admin = $this->account('admin@example.com');
        $employee = $this->account('empleada@example.com');
        $firm = AdvisoryFirm::open('Norte Asesores', 'B0000002B', $clock->now());
        $advisoryMemberships->save(AdvisoryMembership::administer($firm, $admin));
        $advisoryMemberships->save(AdvisoryMembership::employ($firm, $employee));
        $titular = $this->account('titular@example.com');
        $entity = FiscalEntity::open('Taller Norte', 'B0000001B', $clock->now());
        $fiscalMemberships->save(FiscalEntityMembership::hold($entity, $titular));

        $invite = $this->inviteFirm($advisoryMemberships, $invitations, $clock);
        try {
            $invite($employee, $firm->id(), 'titular@example.com');
            self::fail('Una empleada no debe invitar a una entidad.');
        } catch (InvalidAdvisory $exception) {
            self::assertSame(InvalidAdvisory::FORBIDDEN, $exception->reason);
        }

        try {
            $invite($admin, $firm->id(), ' ');
            self::fail('Un correo vacío no debe invitar.');
        } catch (InvalidAdvisory $exception) {
            self::assertSame(InvalidAdvisory::EMAIL, $exception->reason);
        }

        $invite($admin, $firm->id(), 'Titular@example.com');
        $token = $this->tokenFrom(RecordingAccountMailer::$messages[0]['url']);
        $accept = new AcceptAdvisoryClientInvitation(
            $invitations,
            new InMemoryAdvisoryClientLinkRepository(),
            $advisoryMemberships,
            $fiscalMemberships,
            new InMemoryRegistrationStore(),
            $clock,
        );
        $clock->modify('+8 days');
        try {
            $accept($titular, $token, $entity->id());
            self::fail('La invitación tenía que caducar a los 7 días.');
        } catch (InvalidAdvisory $exception) {
            self::assertSame(InvalidAdvisory::UNUSABLE, $exception->reason);
        }
        self::assertSame(AdvisoryClientInvitation::FROM_FIRM, $invitations->findByFirmAndEmail($firm, 'titular@example.com')[0]->origin());
    }

    private function inviteEntity(
        InMemoryFiscalEntityMembershipRepository $fiscalMemberships,
        InMemoryAdvisoryClientLinkRepository $links,
        InMemoryAdvisoryClientInvitationRepository $invitations,
        MockClock $clock,
    ): InviteAdvisoryForEntity {
        RecordingAccountMailer::reset();

        return new InviteAdvisoryForEntity(
            $fiscalMemberships,
            $links,
            $invitations,
            new InMemoryRegistrationStore(),
            new RecordingAccountMailer(),
            $this->links(),
            $clock,
        );
    }

    private function inviteFirm(
        InMemoryAdvisoryMembershipRepository $memberships,
        InMemoryAdvisoryClientInvitationRepository $invitations,
        MockClock $clock,
    ): InviteEntityForAdvisory {
        RecordingAccountMailer::reset();

        return new InviteEntityForAdvisory(
            $memberships,
            $invitations,
            new InMemoryRegistrationStore(),
            new RecordingAccountMailer(),
            $this->links(),
            $clock,
        );
    }

    private function links(): AdvisoryClientInvitationLink
    {
        return new class implements AdvisoryClientInvitationLink {
            public function url(string $token): string
            {
                return 'https://aselto.test/asesorias/vinculo/'.$token;
            }
        };
    }

    private function account(string $email): Account
    {
        $account = Account::register($email, 'hash', new \DateTimeImmutable('2026-11-02T00:40:00Z'));
        $account->verify(new \DateTimeImmutable('2026-11-02T00:40:00Z'));

        return $account;
    }

    private function tokenFrom(string $url): string
    {
        $token = basename($url);
        self::assertSame(64, strlen($token));

        return $token;
    }
}
