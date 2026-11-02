<?php

declare(strict_types=1);

namespace App\Income;

final readonly class LineValues
{
    public function __construct(
        public string $quantity,
        public string $unitPrice,
        public string $taxRate,
        public string $withholdingRate,
        public string $base,
        public string $tax,
        public string $withholding,
        public string $total,
    ) {
    }
}
