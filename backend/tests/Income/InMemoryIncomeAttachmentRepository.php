<?php

declare(strict_types=1);

namespace App\Tests\Income;

use App\Entity\FiscalEntity;
use App\Entity\Income;
use App\Entity\IncomeAttachment;
use App\Income\IncomeAttachmentRepository;

final class InMemoryIncomeAttachmentRepository implements IncomeAttachmentRepository
{
    /** @var array<string, IncomeAttachment> */
    private array $attachments = [];

    public function save(IncomeAttachment $attachment): void
    {
        $this->attachments[$attachment->id()] = $attachment;
    }

    public function findForIncome(FiscalEntity $fiscalEntity, Income $income, string $id): ?IncomeAttachment
    {
        $attachment = $this->attachments[$id] ?? null;
        if (null === $attachment || !$attachment->belongsTo($income) || $income->fiscalEntity()->id() !== $fiscalEntity->id()) {
            return null;
        }

        return $attachment;
    }

    public function forIncome(Income $income): array
    {
        $found = [];
        foreach ($this->attachments as $attachment) {
            if ($attachment->belongsTo($income)) {
                $found[] = $attachment;
            }
        }

        return $found;
    }
}
