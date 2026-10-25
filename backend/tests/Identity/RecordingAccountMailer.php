<?php

declare(strict_types=1);

namespace App\Tests\Identity;

use App\Identity\AccountMailer;

final class RecordingAccountMailer implements AccountMailer
{
    /** @var list<array{kind: string, to: string, url: string}> */
    public static array $messages = [];

    public static function reset(): void
    {
        self::$messages = [];
    }

    public function sendConfirmation(string $to, string $url): void
    {
        self::$messages[] = ['kind' => 'confirmation', 'to' => $to, 'url' => $url];
    }

    public function sendPasswordReset(string $to, string $url): void
    {
        self::$messages[] = ['kind' => 'reset', 'to' => $to, 'url' => $url];
    }
}
