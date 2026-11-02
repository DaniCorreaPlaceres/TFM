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
    public const string AMOUNT = 'amount';
    public const string EXCEEDS = 'exceeds';
    public const string TOKEN = 'token';
    public const string REFERENCE = 'reference';
    public const string STATE = 'state';
    public const string REVIEW_REASON = 'review_reason';
    public const string FROZEN = 'frozen';
    public const string RECURRENCE = 'recurrence';
    public const string PROPOSAL = 'proposal';
    public const string SOURCE = 'source';

    public function __construct(public readonly string $reason)
    {
        parent::__construct($reason);
    }
}
