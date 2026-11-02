<?php

declare(strict_types=1);

namespace App\Income;

use App\Entity\Account;
use App\Entity\FiscalEntity;
use App\Entity\Income;
use App\Entity\IncomeAction;
use App\Entity\IncomeAttachment;
use App\Identity\RegistrationStore;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\Uid\Uuid;

final class AttachIncomeFile
{
    public const array TYPES = [
        'application/pdf' => 'pdf',
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    public const int MAX_BYTES = 8388608;

    public function __construct(
        private readonly IncomeAccess $access,
        private readonly IncomeRepository $incomes,
        private readonly IncomeAttachmentRepository $attachments,
        private readonly IncomeActionRepository $actions,
        private readonly ObjectStore $objects,
        private readonly RegistrationStore $store,
        private readonly ClockInterface $clock,
    ) {
    }

    public function __invoke(
        Account $account,
        FiscalEntity $fiscalEntity,
        string $incomeId,
        int $version,
        string $originalName,
        string $mimeType,
        string $bytes,
    ): Income {
        $income = $this->access->income($account, $fiscalEntity, $incomeId);
        $this->access->version($income, $version);
        $this->access->open($fiscalEntity, $income->issuedOn());
        if (!isset(self::TYPES[$mimeType]) || '' === $bytes || strlen($bytes) > self::MAX_BYTES) {
            throw new InvalidIncome(InvalidIncome::FILE);
        }
        $income->touch();
        $extension = self::TYPES[$mimeType];
        $key = $fiscalEntity->id().'/incomes/'.$income->id().'/'.Uuid::v7()->toRfc4122().'.'.$extension;
        $this->objects->put($key, $bytes);
        $attachment = IncomeAttachment::store(
            $income,
            self::fileName($originalName),
            $mimeType,
            $key,
            strlen($bytes),
            hash('sha256', $bytes),
            $this->clock->now(),
        );
        $action = IncomeAction::record($income, $account, 'adjunto', 'Adjunto añadido.', $this->clock->now());
        $this->store->commit(function () use ($income, $attachment, $action): void {
            $this->incomes->save($income);
            $this->attachments->save($attachment);
            $this->actions->save($action);
        });

        return $income;
    }

    public static function fileName(string $original): string
    {
        $name = basename(str_replace('\\', '/', $original));
        $name = preg_replace('/[^A-Za-z0-9._-]+/', '_', $name) ?? '';
        $name = trim($name, '._');
        if ('' === $name) {
            $name = 'adjunto';
        }

        return mb_substr($name, 0, 180);
    }
}
