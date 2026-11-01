<?php

declare(strict_types=1);

namespace App\Identity;

final class InvalidSecondFactor extends \DomainException
{
    public const string CODE = 'code';

    public function __construct(public readonly string $reason)
    {
        parent::__construct($reason);
    }
}
