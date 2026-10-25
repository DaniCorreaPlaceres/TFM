<?php

declare(strict_types=1);

namespace App\Identity;

final class InvalidLogin extends \DomainException
{
    public const string WRONG = 'wrong';
    public const string UNVERIFIED = 'unverified';
    public const string TOO_MANY = 'too_many';

    public function __construct(public readonly string $reason)
    {
        parent::__construct($reason);
    }
}
