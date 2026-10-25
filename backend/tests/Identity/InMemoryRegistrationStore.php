<?php

declare(strict_types=1);

namespace App\Tests\Identity;

use App\Identity\RegistrationStore;

final class InMemoryRegistrationStore implements RegistrationStore
{
    public function commit(\Closure $operation): void
    {
        $operation();
    }
}
