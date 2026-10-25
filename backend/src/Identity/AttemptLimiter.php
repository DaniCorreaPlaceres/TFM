<?php

declare(strict_types=1);

namespace App\Identity;

interface AttemptLimiter
{
    public function isBlocked(string $key): bool;

    public function recordFailure(string $key): void;

    public function accept(string $key): bool;
}
