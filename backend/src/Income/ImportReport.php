<?php

declare(strict_types=1);

namespace App\Income;

final readonly class ImportReport
{
    /**
     * @param list<array{row: int, message: string}> $errors
     */
    public function __construct(
        public int $imported,
        public array $errors,
    ) {
    }
}
