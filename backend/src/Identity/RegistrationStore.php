<?php

declare(strict_types=1);

namespace App\Identity;

interface RegistrationStore
{
    public function commit(\Closure $operation): void;
}
