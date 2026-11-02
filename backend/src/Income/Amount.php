<?php

declare(strict_types=1);

namespace App\Income;

final class Amount
{
    private const int MONEY_SCALE = 2;
    private const int QUANTITY_SCALE = 4;
    private const int MAX_MONEY_CENTS = 999999999999;
    private const int MAX_SCALED_QUANTITY = 999999999;

    public static function parseMoney(string $raw): string
    {
        return self::parse($raw, self::MONEY_SCALE, self::MAX_MONEY_CENTS);
    }

    public static function parseQuantity(string $raw): string
    {
        $parsed = self::parse($raw, self::QUANTITY_SCALE, self::MAX_SCALED_QUANTITY);
        if (self::scaled($parsed, self::QUANTITY_SCALE) <= 0) {
            throw new InvalidIncome(InvalidIncome::AMOUNT);
        }

        return $parsed;
    }

    public static function parsePrice(string $raw): string
    {
        return self::parse($raw, self::QUANTITY_SCALE, self::MAX_SCALED_QUANTITY);
    }

    public static function parseRate(string $raw): string
    {
        $parsed = self::parse($raw, self::MONEY_SCALE, 10000);
        if (self::cents($parsed) > 10000) {
            throw new InvalidIncome(InvalidIncome::AMOUNT);
        }

        return $parsed;
    }

    public static function line(string $quantity, string $unitPrice, string $taxRate, string $withholdingRate): LineValues
    {
        $quantity = self::parseQuantity($quantity);
        $unitPrice = self::parsePrice($unitPrice);
        $taxRate = self::parseRate($taxRate);
        $withholdingRate = self::parseRate($withholdingRate);
        $baseCents = self::divRound(
            self::scaled($quantity, self::QUANTITY_SCALE) * self::scaled($unitPrice, self::QUANTITY_SCALE),
            1_000_000,
        );
        $taxCents = self::divRound($baseCents * self::cents($taxRate), 10_000);
        $withholdingCents = self::divRound($baseCents * self::cents($withholdingRate), 10_000);
        $totalCents = $baseCents + $taxCents - $withholdingCents;
        if ($totalCents < 0 || $totalCents > self::MAX_MONEY_CENTS || $baseCents > self::MAX_MONEY_CENTS) {
            throw new InvalidIncome(InvalidIncome::AMOUNT);
        }

        return new LineValues(
            $quantity,
            $unitPrice,
            $taxRate,
            $withholdingRate,
            self::fromCents($baseCents),
            self::fromCents($taxCents),
            self::fromCents($withholdingCents),
            self::fromCents($totalCents),
        );
    }

    public static function add(string $left, string $right): string
    {
        $sum = self::cents($left) + self::cents($right);
        if ($sum > self::MAX_MONEY_CENTS) {
            throw new InvalidIncome(InvalidIncome::AMOUNT);
        }

        return self::fromCents($sum);
    }

    public static function subtract(string $left, string $right): string
    {
        $difference = self::cents($left) - self::cents($right);
        if ($difference < 0) {
            throw new InvalidIncome(InvalidIncome::AMOUNT);
        }

        return self::fromCents($difference);
    }

    public static function compare(string $left, string $right): int
    {
        return self::cents($left) <=> self::cents($right);
    }

    public static function cents(string $amount): int
    {
        $negative = str_starts_with($amount, '-');
        $amount = ltrim($amount, '-');
        [$whole, $fraction] = explode('.', $amount);

        return ($negative ? -1 : 1) * ((int) $whole * 100 + (int) $fraction);
    }

    public static function plain(string $amount): string
    {
        if (str_contains($amount, '.')) {
            $amount = rtrim(rtrim($amount, '0'), '.');
        }

        return '' === $amount ? '0' : $amount;
    }

    public static function fromCents(int $cents): string
    {
        $negative = $cents < 0;
        $cents = abs($cents);

        return ($negative ? '-' : '').intdiv($cents, 100).'.'.str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT);
    }

    private static function parse(string $raw, int $scale, int $maxScaled): string
    {
        $raw = str_replace([' ', '€'], '', trim($raw));
        if (str_contains($raw, ',') && str_contains($raw, '.')) {
            $raw = str_replace('.', '', $raw);
            $raw = str_replace(',', '.', $raw);
        } elseif (str_contains($raw, ',')) {
            $raw = str_replace(',', '.', $raw);
        }
        if (1 !== preg_match('/^\d+(\.\d+)?$/', $raw)) {
            throw new InvalidIncome(InvalidIncome::AMOUNT);
        }
        [$whole, $fraction] = array_pad(explode('.', $raw, 2), 2, '');
        if (strlen($fraction) > $scale) {
            throw new InvalidIncome(InvalidIncome::AMOUNT);
        }
        $fraction = str_pad($fraction, $scale, '0');
        $whole = ltrim($whole, '0');
        if ('' === $whole) {
            $whole = '0';
        }
        if (self::scaled($whole.'.'.$fraction, $scale) > $maxScaled) {
            throw new InvalidIncome(InvalidIncome::AMOUNT);
        }

        return $whole.'.'.$fraction;
    }

    private static function scaled(string $amount, int $scale): int
    {
        [$whole, $fraction] = explode('.', $amount);

        return (int) $whole * (10 ** $scale) + (int) $fraction;
    }

    private static function divRound(int $numerator, int $divisor): int
    {
        $negative = $numerator < 0;
        if ($negative) {
            $numerator = -$numerator;
        }
        $quotient = intdiv($numerator, $divisor);
        $remainder = $numerator % $divisor;
        if ($remainder * 2 >= $divisor) {
            ++$quotient;
        }

        return $negative ? -$quotient : $quotient;
    }
}
