<?php

declare(strict_types=1);

namespace App\Identity;

final class InvalidAdvisory extends \DomainException
{
    public const string NAME = 'name';

    public const string TAX_ID = 'tax_id';

    public const string EMAIL = 'email';

    public const string ALREADY_MEMBER = 'already_member';

    public const string FORBIDDEN = 'forbidden';

    public const string WRONG_ACCOUNT = 'wrong_account';

    public const string UNUSABLE = 'unusable';

    public const string ALREADY_LINKED = 'already_linked';

    public function __construct(public readonly string $reason)
    {
        parent::__construct($reason);
    }
}
