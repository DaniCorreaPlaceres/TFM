<?php

declare(strict_types=1);

namespace App\Tests\Identity;

use App\Entity\Account;
use App\Identity\ConfirmEmail;
use App\Identity\RegisterAccount;
use Symfony\Component\Clock\MockClock;

final class RegistrationFixture
{
    public function __construct(
        public InMemoryAccountRepository $accounts,
        public ConfigurablePwnedPasswordList $passwords,
        public RecordingAccountMailer $mailer,
        public MockClock $clock,
        private RegisterAccount $register,
        private ConfirmEmail $confirm,
    ) {
    }

    public function register(string $email, string $password): Account
    {
        return ($this->register)($email, $password);
    }

    public function confirm(string $token): Account
    {
        return ($this->confirm)($token);
    }
}
