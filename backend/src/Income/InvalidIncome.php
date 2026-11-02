<?php

declare(strict_types=1);

namespace App\Income;

final class InvalidIncome extends \DomainException
{
    public const string FORBIDDEN = 'forbidden';
    public const string NAME = 'name';
    public const string TAX_ID = 'tax_id';
    public const string ROLE = 'role';
    public const string EXISTS = 'exists';
    public const string CONTACT = 'contact';
    public const string DATE = 'date';
    public const string DUE = 'due';
    public const string INVOICE = 'invoice';
    public const string LINE = 'line';
    public const string AMOUNT = 'amount';
    public const string EXCEEDS = 'exceeds';
    public const string BELOW_PAYMENTS = 'below_payments';
    public const string DUPLICATE = 'duplicate';
    public const string CLOSED = 'closed';
    public const string STALE = 'stale';
    public const string ANNULLED = 'annulled';
    public const string REASON = 'reason';
    public const string FILE = 'file';
    public const string METHOD = 'method';
    public const string TOKEN = 'token';
    public const string PERIOD = 'period';
    public const string ALREADY_CLOSED = 'already_closed';
    public const string CSV = 'csv';

    public function __construct(public readonly string $reason)
    {
        parent::__construct($reason);
    }
}
