<?php

declare(strict_types=1);

namespace App\Identity;

use App\Entity\Account;
use Symfony\Component\Clock\ClockInterface;

final class ConfirmEmail
{
    public function __construct(
        private readonly AccountRepository $accounts,
        private readonly EmailConfirmationRepository $confirmations,
        private readonly RegistrationStore $store,
        private readonly ClockInterface $clock,
    ) {
    }

    public function __invoke(string $token): Account
    {
        $confirmation = $this->confirmations->findByTokenHash(hash('sha256', $token));
        $now = $this->clock->now();
        if (null === $confirmation || !$confirmation->isUsableAt($now)) {
            throw new InvalidConfirmation();
        }

        $confirmation->use($now);
        $account = $confirmation->account();
        $account->verify($now);
        $this->store->commit(function () use ($account, $confirmation): void {
            $this->accounts->save($account);
            $this->confirmations->save($confirmation);
        });

        return $account;
    }
}
