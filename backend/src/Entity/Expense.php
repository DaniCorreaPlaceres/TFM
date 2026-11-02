<?php

declare(strict_types=1);

namespace App\Entity;

use App\Expense\ExpenseKind;
use App\Expense\PaymentMethod;
use App\Income\Amount;
use App\Income\DraftLine;
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
    ) {
        $this->lines = new ArrayCollection();
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
        $this->baseDeductible = $deductible;
        $this->baseReason = $reason;
    }

    public function decideTax(bool $deductible, string $reason): void
    {
        $this->taxDeductible = $deductible;
        $this->taxReason = $reason;
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
