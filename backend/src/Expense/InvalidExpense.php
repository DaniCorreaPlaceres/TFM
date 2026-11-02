<?php

declare(strict_types=1);

namespace App\Expense;

final class InvalidExpense extends \DomainException
{
    public const string FORBIDDEN = 'forbidden';
    public const string CONTACT = 'contact';
    public const string CATEGORY = 'category';
    public const string KIND = 'kind';
    public const string METHOD = 'method';
    public const string DOCUMENT = 'document';
    public const string DATE = 'date';
    public const string LINE = 'line';
    public const string NAME = 'name';
    public const string EXISTS = 'exists';
    public const string PART = 'part';
    public const string REASON = 'reason';

    public function __construct(public readonly string $reason)
    {
        parent::__construct($reason);
    }
}
