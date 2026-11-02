<?php

declare(strict_types=1);

namespace App\Entity;

use App\Income\Amount;
use App\Income\DraftLine;
use App\Income\InvalidIncome;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'income')]
class Income
{
    /** @var Collection<int, IncomeLine> */
    #[ORM\OneToMany(targetEntity: IncomeLine::class, mappedBy: 'income', cascade: ['persist'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $lines;

    /** @var Collection<int, IncomePayment> */
    #[ORM\OneToMany(targetEntity: IncomePayment::class, mappedBy: 'income', cascade: ['persist'], orphanRemoval: true)]
    private Collection $payments;

    private function __construct(
        #[ORM\Id]
        #[ORM\Column(length: 36)]
        private string $id,
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private FiscalEntity $fiscalEntity,
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
        private Contact $contact,
        #[ORM\Column(length: 40)]
        private string $invoiceNo,
        #[ORM\Column(type: Types::DATE_IMMUTABLE)]
        private \DateTimeImmutable $issuedOn,
        #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
        private ?\DateTimeImmutable $dueOn,
        #[ORM\Column(length: 180)]
        private string $snapshotName,
        #[ORM\Column(length: 20)]
        private string $snapshotTaxId,
        #[ORM\Column(length: 3)]
        private string $currency,
        #[ORM\Column(length: 32)]
        private string $recordStatus,
        #[ORM\Column]
        private int $version,
        #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
        private ?\DateTimeImmutable $annulledAt,
        #[ORM\Column(length: 500, nullable: true)]
        private ?string $annulReason,
        #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
        private \DateTimeImmutable $createdAt,
    ) {
        $this->lines = new ArrayCollection();
        $this->payments = new ArrayCollection();
    }

    /**
     * @param list<DraftLine> $lines
     */
    public static function register(
        FiscalEntity $fiscalEntity,
        Contact $contact,
        string $invoiceNo,
        \DateTimeImmutable $issuedOn,
        ?\DateTimeImmutable $dueOn,
        array $lines,
        \DateTimeImmutable $now,
    ): self {
        $income = new self(
            Uuid::v7()->toRfc4122(),
            $fiscalEntity,
            $contact,
            $invoiceNo,
            $issuedOn,
            $dueOn,
            $contact->displayName(),
            $contact->taxId(),
            'EUR',
            'registrado',
            1,
            null,
            null,
            $now,
        );
        $income->replaceLines($lines);

        return $income;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function fiscalEntity(): FiscalEntity
    {
        return $this->fiscalEntity;
    }

    public function contact(): Contact
    {
        return $this->contact;
    }

    public function invoiceNo(): string
    {
        return $this->invoiceNo;
    }

    public function issuedOn(): \DateTimeImmutable
    {
        return $this->issuedOn;
    }

    public function dueOn(): ?\DateTimeImmutable
    {
        return $this->dueOn;
    }

    public function snapshotName(): string
    {
        return $this->snapshotName;
    }

    public function snapshotTaxId(): string
    {
        return $this->snapshotTaxId;
    }

    public function currency(): string
    {
        return $this->currency;
    }

    public function version(): int
    {
        return $this->version;
    }

    public function annulledAt(): ?\DateTimeImmutable
    {
        return $this->annulledAt;
    }

    public function annulReason(): ?string
    {
        return $this->annulReason;
    }

    public function isAnnulled(): bool
    {
        return 'anulado' === $this->recordStatus;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return list<IncomeLine>
     */
    public function lines(): array
    {
        $lines = $this->lines->getValues();
        usort($lines, static fn (IncomeLine $left, IncomeLine $right): int => $left->position() <=> $right->position());

        return $lines;
    }

    /**
     * @return list<IncomePayment>
     */
    public function payments(): array
    {
        return $this->payments->getValues();
    }

    public function base(): string
    {
        return $this->sum(static fn (IncomeLine $line): string => $line->baseAmount());
    }

    public function tax(): string
    {
        return $this->sum(static fn (IncomeLine $line): string => $line->taxAmount());
    }

    public function withholding(): string
    {
        return $this->sum(static fn (IncomeLine $line): string => $line->withholdingAmount());
    }

    public function total(): string
    {
        return $this->sum(static fn (IncomeLine $line): string => $line->total());
    }

    public function collected(): string
    {
        $sum = '0.00';
        foreach ($this->payments() as $payment) {
            $sum = Amount::add($sum, $payment->amount());
        }

        return $sum;
    }

    public function balance(): string
    {
        return Amount::subtract($this->total(), $this->collected());
    }

    public function paymentStatus(): string
    {
        if ($this->isAnnulled()) {
            return 'anulado';
        }
        if (0 === Amount::compare($this->collected(), '0.00') && Amount::compare($this->total(), '0.00') > 0) {
            return 'pendiente';
        }
        if (0 === Amount::compare($this->balance(), '0.00')) {
            return 'cobrado';
        }

        return 'parcial';
    }

    public function paymentStatusLabel(): string
    {
        return match ($this->paymentStatus()) {
            'anulado' => 'Anulado',
            'parcial' => 'Parcial',
            'cobrado' => 'Cobrado',
            default => 'Pendiente',
        };
    }

    public function findPayment(string $token): ?IncomePayment
    {
        foreach ($this->payments() as $payment) {
            if ($payment->clientToken() === $token) {
                return $payment;
            }
        }

        return null;
    }

    /**
     * @param list<DraftLine> $lines
     */
    public function correct(
        Contact $contact,
        string $invoiceNo,
        \DateTimeImmutable $issuedOn,
        ?\DateTimeImmutable $dueOn,
        array $lines,
    ): void {
        $this->assertActive();
        if (!$contact->belongsTo($this->fiscalEntity) || !$contact->isClient()) {
            throw new InvalidIncome(InvalidIncome::CONTACT);
        }
        $nextTotal = '0.00';
        foreach ($lines as $line) {
            $nextTotal = Amount::add($nextTotal, $line->values->total);
        }
        if (Amount::compare($nextTotal, $this->collected()) < 0) {
            throw new InvalidIncome(InvalidIncome::BELOW_PAYMENTS);
        }
        if ($contact->id() !== $this->contact->id()) {
            $this->contact = $contact;
            $this->snapshotName = $contact->displayName();
            $this->snapshotTaxId = $contact->taxId();
        }
        $this->invoiceNo = $invoiceNo;
        $this->issuedOn = $issuedOn;
        $this->dueOn = $dueOn;
        $this->replaceLines($lines);
        ++$this->version;
    }

    public function collect(
        string $amount,
        \DateTimeImmutable $paidOn,
        string $method,
        string $reference,
        string $clientToken,
    ): IncomePayment {
        $this->assertActive();
        if (!isset(IncomePayment::METHODS[$method])) {
            throw new InvalidIncome(InvalidIncome::METHOD);
        }
        $reference = trim($reference);
        if (mb_strlen($reference) > 80) {
            throw new InvalidIncome(InvalidIncome::AMOUNT);
        }
        $amount = Amount::parseMoney($amount);
        if (Amount::compare($amount, '0.00') <= 0) {
            throw new InvalidIncome(InvalidIncome::AMOUNT);
        }
        if (Amount::compare($amount, $this->balance()) > 0) {
            throw new InvalidIncome(InvalidIncome::EXCEEDS);
        }
        $payment = IncomePayment::record($this, $amount, $paidOn, $method, $reference, $clientToken);
        $this->payments->add($payment);
        ++$this->version;

        return $payment;
    }

    public function annul(string $reason, \DateTimeImmutable $at): void
    {
        if ($this->isAnnulled()) {
            throw new InvalidIncome(InvalidIncome::ANNULLED);
        }
        $reason = trim($reason);
        if ('' === $reason || mb_strlen($reason) > 500) {
            throw new InvalidIncome(InvalidIncome::REASON);
        }
        $this->annulledAt = $at;
        $this->annulReason = $reason;
        $this->recordStatus = 'anulado';
        ++$this->version;
    }

    public function touch(): void
    {
        $this->assertActive();
        ++$this->version;
    }

    /**
     * @param list<DraftLine> $lines
     */
    private function replaceLines(array $lines): void
    {
        $this->lines->clear();
        $position = 1;
        foreach ($lines as $line) {
            $this->lines->add(IncomeLine::fromDraft($this, $line, $position));
            ++$position;
        }
    }

    private function assertActive(): void
    {
        if ($this->isAnnulled()) {
            throw new InvalidIncome(InvalidIncome::ANNULLED);
        }
    }

    /**
     * @param \Closure(IncomeLine): string $value
     */
    private function sum(\Closure $value): string
    {
        $sum = '0.00';
        foreach ($this->lines() as $line) {
            $sum = Amount::add($sum, $value($line));
        }

        return $sum;
    }
}
