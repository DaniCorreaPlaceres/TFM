<?php

declare(strict_types=1);

namespace App\Identity;

final class InvalidRegistration extends \DomainException
{
    public const string EMAIL = 'email';
    public const string SHORT = 'short';
    public const string PWNED = 'pwned';
    public const string UNAVAILABLE = 'unavailable';
    public const string DUPLICATE = 'duplicate';

    public function __construct(public readonly string $reason)
    {
        parent::__construct($reason);
    }
}
