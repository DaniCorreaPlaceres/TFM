<?php

declare(strict_types=1);

namespace App\Identity;

use App\Entity\PasswordReset;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;

final class ResetPassword
{
    public function __construct(
        private readonly PasswordResetRepository $resets,
        private readonly AccountRepository $accounts,
        private readonly RegistrationStore $store,
        private readonly PasswordHasherInterface $passwords,
        private readonly PasswordPolicy $policy,
        private readonly ClockInterface $clock,
    ) {
    }

    public function usable(string $token): bool
    {
        return null !== $this->findUsable($token);
    }

    public function __invoke(string $token, #[\SensitiveParameter] string $password): void
    {
        $reset = $this->findUsable($token);
        if (null === $reset) {
            throw new InvalidPasswordReset();
        }

        $this->policy->assertAcceptable($password);
        $now = $this->clock->now();
        $account = $reset->account();
        $account->changePassword($this->passwords->hash($password));
        $reset->use($now);
        $this->store->commit(function () use ($account, $reset): void {
            $this->accounts->save($account);
            $this->resets->save($reset);
        });
    }

    private function findUsable(string $token): ?PasswordReset
    {
        $reset = $this->resets->findByTokenHash(hash('sha256', $token));
        if (null === $reset || !$reset->isUsableAt($this->clock->now())) {
            return null;
        }

        return $reset;
    }
}
