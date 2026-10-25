<?php

declare(strict_types=1);

namespace App\Identity;

use Symfony\Component\RateLimiter\RateLimiterFactory;

final class SymfonyAttemptLimiter implements AttemptLimiter
{
    public function __construct(private readonly RateLimiterFactory $factory)
    {
    }

    public function isBlocked(string $key): bool
    {
        return 0 === $this->factory->create($key)->consume(0)->getRemainingTokens();
    }

    public function recordFailure(string $key): void
    {
        $this->factory->create($key)->consume(1);
    }

    public function accept(string $key): bool
    {
        return $this->factory->create($key)->consume(1)->isAccepted();
    }
}
