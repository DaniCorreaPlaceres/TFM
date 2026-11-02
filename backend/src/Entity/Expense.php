<?php

declare(strict_types=1);

namespace App\Entity;

use App\Expense\ExpenseKind;
use App\Expense\InvalidExpense;
use App\Expense\PaymentMethod;
use App\Expense\ReviewReason;
use App\Income\Amount;
use App\Income\DraftLine;
use App\Income\InvalidIncome;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'expense')]
class Expense
{
    /** @var Collection<int, ExpenseLine> */
    #[ORM\OneToMany(targetEntity: ExpenseLine::class, mappedBy: 'expense', cascade: ['persist'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $lines;

    /** @var Collection<int, ExpensePayment> */
    #[ORM\OneToMany(targetEntity: ExpensePayment::class, mappedBy: 'expense', cascade: ['persist'])]
    #[ORM\OrderBy(['id' => 'ASC'])]
    private Collection $payments;

    /**
     * @param list<DraftLine> $lines
     */
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(length: 36)]
        private string $id,
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private FiscalEntity $fiscalEntity,
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
        private Contact $contact,
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
        private ExpenseCategory $category,
        #[ORM\Column(length: 16)]
        private string $kind,
        #[ORM\Column(length: 32)]
        private string $paymentMethod,
        #[ORM\Column(length: 40)]
        private string $documentNo,
        #[ORM\Column(type: 'date_immutable')]
        private \DateTimeImmutable $issuedOn,
        #[ORM\Column(length: 180)]
        private string $snapshotName,
        #[ORM\Column(length: 20)]
        private string $snapshotTaxId,
        #[ORM\Column(length: 60)]
        private string $snapshotCategory,
        #[ORM\Column(length: 3)]
        private string $currency,
        #[ORM\Column]
        private \DateTimeImmutable $createdAt,
        array $lines,
        #[ORM\Column(nullable: true)]
        private ?bool $baseDeductible = null,
        #[ORM\Column(length: 240, nullable: true)]
        private ?string $baseReason = null,
        #[ORM\Column(nullable: true)]
        private ?bool $taxDeductible = null,
        #[ORM\Column(length: 240, nullable: true)]
        private ?string $taxReason = null,
        #[ORM\Column(length: 16, options: ['default' => 'borrador'])]
        private string $reviewState = 'borrador',
        #[ORM\Column(length: 240, nullable: true)]
        private ?string $reviewReason = null,
    ) {
        $this->lines = new ArrayCollection();
        $this->payments = new ArrayCollection();
        $this->replaceLines($lines);
    }

    /**
     * @param list<DraftLine> $lines
     */
    public static function register(
        FiscalEntity $fiscalEntity,
        Contact $contact,
        ExpenseCategory $category,
        string $kind,
        string $paymentMethod,
        string $documentNo,
        \DateTimeImmutable $issuedOn,
        array $lines,
        \DateTimeImmutable $createdAt,
    ): self {
        return new self(
            Uuid::v7()->toRfc4122(),
            $fiscalEntity,
            $contact,
            $category,
            $kind,
            $paymentMethod,
            $documentNo,
            $issuedOn,
            $contact->displayName(),
            $contact->taxId(),
            $category->name(),
            'EUR',
            $createdAt,
            $lines,
        );
    }

    public function id(): string
    {
        return $this->id;
    }

    public function fiscalEntity(): FiscalEntity
    {
        return $this->fiscalEntity;
    }

    public function belongsTo(FiscalEntity $fiscalEntity): bool
    {
        return $this->fiscalEntity->id() === $fiscalEntity->id();
    }

    public function contact(): Contact
    {
        return $this->contact;
    }

    public function category(): ExpenseCategory
    {
        return $this->category;
    }

    public function kind(): string
    {
        return $this->kind;
    }

    public function kindLabel(): string
    {
        return ExpenseKind::label($this->kind);
    }

    public function paymentMethod(): string
    {
        return $this->paymentMethod;
    }

    public function paymentMethodLabel(): string
    {
        return PaymentMethod::label($this->paymentMethod);
    }

    public function documentNo(): string
    {
        return $this->documentNo;
    }

    public function issuedOn(): \DateTimeImmutable
    {
        return $this->issuedOn;
    }

    public function snapshotName(): string
    {
        return $this->snapshotName;
    }

    public function snapshotTaxId(): string
    {
        return $this->snapshotTaxId;
    }

    public function snapshotCategory(): string
    {
        return $this->snapshotCategory;
    }

    public function currency(): string
    {
        return $this->currency;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return list<ExpenseLine>
     */
    public function lines(): array
    {
        $lines = $this->lines->getValues();
        usort($lines, static fn (ExpenseLine $left, ExpenseLine $right): int => $left->position() <=> $right->position());

        return $lines;
    }

    public function base(): string
    {
        return $this->sum(static fn (ExpenseLine $line): string => $line->baseAmount());
    }

    public function tax(): string
    {
        return $this->sum(static fn (ExpenseLine $line): string => $line->taxAmount());
    }

    public function withholding(): string
    {
        return $this->sum(static fn (ExpenseLine $line): string => $line->withholdingAmount());
    }

    public function total(): string
    {
        return $this->sum(static fn (ExpenseLine $line): string => $line->total());
    }

    public function baseDeductible(): ?bool
    {
        return $this->baseDeductible;
    }

    public function baseReason(): ?string
    {
        return $this->baseReason;
    }

    public function taxDeductible(): ?bool
    {
        return $this->taxDeductible;
    }

    public function taxReason(): ?string
    {
        return $this->taxReason;
    }

    public function baseDecisionLabel(): string
    {
        return self::decisionLabel($this->baseDeductible);
    }

    public function taxDecisionLabel(): string
    {
        return self::decisionLabel($this->taxDeductible);
    }

    public function decideBase(bool $deductible, string $reason): void
    {
        $this->assertCurrent();
        $this->baseDeductible = $deductible;
        $this->baseReason = $reason;
    }

    public function decideTax(bool $deductible, string $reason): void
    {
        $this->assertCurrent();
        $this->taxDeductible = $deductible;
        $this->taxReason = $reason;
    }

    public function reviewState(): string
    {
        return $this->reviewState;
    }

    public function reviewReason(): ?string
    {
        return $this->reviewReason;
    }

    public function reviewLabel(): string
    {
        return match ($this->reviewState) {
            'revisado' => 'Revisado',
            'rechazado' => 'Rechazado',
            'anulado' => 'Anulado',
            default => 'Borrador',
        };
    }

    public function standingLabel(): string
    {
        return match ($this->reviewState) {
            'revisado' => 'Definitivo',
            'rechazado', 'anulado' => 'No vigente',
            default => 'No definitivo',
        };
    }

    public function isDefinitive(): bool
    {
        return 'revisado' === $this->reviewState;
    }

    public function isCurrent(): bool
    {
        return 'borrador' === $this->reviewState || 'revisado' === $this->reviewState;
    }

    /**
     * @return list<ExpensePayment>
     */
    public function payments(): array
    {
        return $this->payments->getValues();
    }

    public function findPayment(string $token): ?ExpensePayment
    {
        foreach ($this->payments() as $payment) {
            if ($payment->clientToken() === $token) {
                return $payment;
            }
        }

        return null;
    }

    public function paid(): string
    {
        $sum = '0.00';
        foreach ($this->payments() as $payment) {
            $sum = Amount::add($sum, $payment->amount());
        }

        return $sum;
    }

    public function balance(): string
    {
        return Amount::subtract($this->total(), $this->paid());
    }

    public function paymentStatus(): string
    {
        if (0 === Amount::compare($this->paid(), '0.00') && Amount::compare($this->total(), '0.00') > 0) {
            return 'pendiente';
        }
        if (0 === Amount::compare($this->balance(), '0.00')) {
            return 'pagado';
        }

        return 'parcial';
    }

    public function paymentStatusLabel(): string
    {
        return match ($this->paymentStatus()) {
            'parcial' => 'Parcial',
            'pagado' => 'Pagado',
            default => 'Pendiente',
        };
    }

    public function acceptsPayment(): bool
    {
        return $this->isCurrent() && Amount::compare($this->balance(), '0.00') > 0;
    }

    public function pay(
        string $amount,
        \DateTimeImmutable $paidOn,
        string $method,
        string $reference,
        string $token,
    ): ExpensePayment {
        $this->assertCurrent();
        if (1 !== preg_match('/^[0-9a-fA-F-]{36}$/', $token)) {
            throw new InvalidExpense(InvalidExpense::TOKEN);
        }
        $known = $this->findPayment($token);
        if (null !== $known) {
            return $known;
        }
        $method = PaymentMethod::canonical($method);
        $reference = trim($reference);
        if (mb_strlen($reference) > 80) {
            throw new InvalidExpense(InvalidExpense::REFERENCE);
        }
        try {
            $amount = Amount::parseMoney($amount);
        } catch (InvalidIncome) {
            throw new InvalidExpense(InvalidExpense::AMOUNT);
        }
        if (Amount::compare($amount, '0.00') <= 0) {
            throw new InvalidExpense(InvalidExpense::AMOUNT);
        }
        if (Amount::compare($amount, $this->balance()) > 0) {
            throw new InvalidExpense(InvalidExpense::EXCEEDS);
        }
        $payment = ExpensePayment::record($this, $amount, $paidOn, $method, $reference, $token);
        $this->payments->add($payment);

        return $payment;
    }

    public function review(string $next, string $reason): void
    {
        if (!$this->isCurrent()) {
            throw new InvalidExpense(InvalidExpense::STATE);
        }
        if ('revisado' === $next && 'borrador' === $this->reviewState) {
            $this->reviewState = 'revisado';
            $this->reviewReason = null;

            return;
        }
        if ('rechazado' === $next && 'borrador' === $this->reviewState) {
            $this->reviewReason = ReviewReason::canonical($reason);
            $this->reviewState = 'rechazado';

            return;
        }
        if ('anulado' === $next && ('borrador' === $this->reviewState || 'revisado' === $this->reviewState)) {
            $this->reviewReason = ReviewReason::canonical($reason);
            $this->reviewState = 'anulado';

            return;
        }

        throw new InvalidExpense(InvalidExpense::STATE);
    }

    private function assertCurrent(): void
    {
        if (!$this->isCurrent()) {
            throw new InvalidExpense(InvalidExpense::FROZEN);
        }
    }

    private static function decisionLabel(?bool $deductible): string
    {
        return match ($deductible) {
            true => 'Deducible',
            false => 'No deducible',
            default => 'Sin decidir',
        };
    }

    /**
     * @param list<DraftLine> $lines
     */
    private function replaceLines(array $lines): void
    {
        $this->lines->clear();
        $position = 1;
        foreach ($lines as $line) {
            $this->lines->add(ExpenseLine::fromDraft($this, $line, $position));
            ++$position;
        }
    }

    /**
     * @param \Closure(ExpenseLine): string $value
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
