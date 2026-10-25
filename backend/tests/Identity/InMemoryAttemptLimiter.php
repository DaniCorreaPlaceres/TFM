<?php

declare(strict_types=1);

namespace App\Tests\Identity;

use App\Identity\AttemptLimiter;

final class InMemoryAttemptLimiter implements AttemptLimiter
{
    public int $max = 5;

    /** @var array<string, int> */
    private array $failures = [];

    public function isBlocked(string $key): bool
    {
        return ($this->failures[$key] ?? 0) >= $this->max;
    }

    public function recordFailure(string $key): void
    {
        $this->failures[$key] = ($this->failures[$key] ?? 0) + 1;
    }

    public function accept(string $key): bool
    {
        $this->failures[$key] = ($this->failures[$key] ?? 0) + 1;

        return $this->failures[$key] <= $this->max;
    }
}
