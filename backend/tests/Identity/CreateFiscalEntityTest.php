<?php

declare(strict_types=1);

namespace App\Tests\Identity;

use App\Entity\Account;
use App\Entity\FiscalEntityMembership;
use App\Identity\ActiveFiscalEntity;
use App\Identity\CreateFiscalEntity;
use App\Identity\InvalidFiscalEntity;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

final class CreateFiscalEntityTest extends TestCase
{
    public function testElNombreVacioNoCreaLaEntidad(): void
    {
        [$create] = $this->services();

        try {
            $create($this->account(), '  ', '12345678Z');
            self::fail('Un nombre vacío no debe crear la entidad.');
        } catch (InvalidFiscalEntity $exception) {
            self::assertSame(InvalidFiscalEntity::NAME, $exception->reason);
        }
    }

    public function testCreaDosEntidadesYSoloElTitularPuedeElegirlas(): void
    {
        [$create, $memberships, $active] = $this->services();
        $owner = $this->account();
        $other = Account::register('otra@example.com', 'hash', new \DateTimeImmutable('2026-11-01T20:00:00Z'));

        $first = $create($owner, ' Taller Norte ', '12345678z');
        $second = $create($owner, 'Taller Sur', 'b-12345678');

        self::assertSame('Taller Norte', $first->legalName());
        self::assertSame('12345678Z', $first->taxId());
        self::assertSame('B12345678', $second->taxId());
        self::assertCount(2, $memberships->findForAccount($owner));
        self::assertSame([], $memberships->findForAccount($other));
        self::assertSame(
            FiscalEntityMembership::HOLDER,
            $memberships->findForAccount($owner)[0]->role(),
        );

        $request = $this->request();
        self::assertFalse($active->select($other, $request, $first->id()));
        self::assertNull($request->getSession()->get('fiscal_entity_id'));
        self::assertTrue($active->select($owner, $request, $second->id()));
        $selected = $active->resolve($owner, $request);
        self::assertNotNull($selected);
        self::assertSame($second->id(), $selected->id());
        self::assertSame('Taller Sur', $selected->legalName());
        self::assertNull($active->resolve($other, $request));
    }

    public function testConUnaSolaEntidadLaSesionLaElige(): void
    {
        [$create, , $active] = $this->services();
        $owner = $this->account();
        $fiscalEntity = $create($owner, 'Taller Norte', '12345678Z');
        $request = $this->request();
        $selected = $active->resolve($owner, $request);
        self::assertNotNull($selected);
        self::assertSame($fiscalEntity->id(), $selected->id());
    }

    /**
     * @return array{CreateFiscalEntity, InMemoryFiscalEntityMembershipRepository, ActiveFiscalEntity}
     */
    private function services(): array
    {
        $memberships = new InMemoryFiscalEntityMembershipRepository();
        $create = new CreateFiscalEntity(
            new InMemoryFiscalEntityRepository(),
            $memberships,
            new InMemoryRegistrationStore(),
            new MockClock('2026-11-01T20:00:00Z'),
        );

        return [$create, $memberships, new ActiveFiscalEntity($memberships)];
    }

    private function account(): Account
    {
        $account = Account::register('persona@example.com', 'hash', new \DateTimeImmutable('2026-11-01T20:00:00Z'));
        $account->verify(new \DateTimeImmutable('2026-11-01T20:00:00Z'));

        return $account;
    }

    private function request(): Request
    {
        $request = Request::create('/sesion');
        $request->setSession(new Session(new MockArraySessionStorage()));

        return $request;
    }
}
