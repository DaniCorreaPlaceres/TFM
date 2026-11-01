<?php

declare(strict_types=1);

namespace App\Identity;

final class InvalidFiscalEntity extends \DomainException
{
    public const string NAME = 'name';
    public const string TAX_ID = 'tax_id';

    public function __construct(public readonly string $reason)
    {
        parent::__construct($reason);
    }
}
