<?php

declare(strict_types=1);

namespace App\Identity;

use App\Entity\Account;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;

final class AuthenticateAccount
{
    private ?string $dummyHash = null;

    public function __construct(
        private readonly AccountRepository $accounts,
        private readonly PasswordHasherInterface $passwords,
        #[Autowire(service: 'App\Identity\LoginAttemptLimiter')]
        private readonly AttemptLimiter $attempts,
    ) {
    }

    public function __invoke(string $email, #[\SensitiveParameter] string $password): Account
    {
        $email = mb_strtolower(trim($email));
        if ($this->attempts->isBlocked($email)) {
            throw new InvalidLogin(InvalidLogin::TOO_MANY);
        }

        $account = $this->accounts->findByEmail($email);
        $matches = $this->passwords->verify($account?->passwordHash() ?? $this->dummyHash(), $password);
        if (null === $account || !$matches) {
            $this->attempts->recordFailure($email);
            throw new InvalidLogin(InvalidLogin::WRONG);
        }

        if (!$account->allowsFiscalAccess()) {
            throw new InvalidLogin(InvalidLogin::UNVERIFIED);
        }

        return $account;
    }

    private function dummyHash(): string
    {
        return $this->dummyHash ??= $this->passwords->hash('cuenta-inexistente');
    }
}
