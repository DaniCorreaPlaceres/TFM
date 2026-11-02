<?php

declare(strict_types=1);

namespace App\Income;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

final class EuroExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [new TwigFilter('euros', Euro::format(...))];
    }
}
