<?php

declare(strict_types=1);

namespace App\Income;

final readonly class DraftLine
{
    public function __construct(
        public string $description,
        public LineValues $values,
    ) {
    }
}
