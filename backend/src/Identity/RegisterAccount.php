<?php

declare(strict_types=1);

namespace App\Identity;

use App\Entity\Account;
use App\Entity\EmailConfirmation;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;

final class RegisterAccount
{
    public function __construct(
        private readonly AccountRepository $accounts,
        private readonly EmailConfirmationRepository $confirmations,
        private readonly RegistrationStore $store,
        private readonly PasswordHasherInterface $passwords,
        private readonly PasswordPolicy $policy,
        private readonly AccountMailer $mailer,
        private readonly ConfirmationLinkFactory $links,
        private readonly ClockInterface $clock,
    ) {
    }

    public function __invoke(string $email, string $password): Account
    {
        $email = mb_strtolower(trim($email));
        if (strlen($email) > 180 || false === filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidRegistration(InvalidRegistration::EMAIL);
        }

        $this->policy->assertAcceptable($password);

        if (null !== $this->accounts->findByEmail($email)) {
            throw new InvalidRegistration(InvalidRegistration::DUPLICATE);
        }

        $now = $this->clock->now();
        $account = Account::register($email, $this->passwords->hash($password), $now);
        $token = bin2hex(random_bytes(32));
        $confirmation = EmailConfirmation::issue($account, hash('sha256', $token), $now->modify('+30 minutes'));

        $this->store->commit(function () use ($account, $confirmation, $email, $token): void {
            $this->accounts->save($account);
            $this->confirmations->save($confirmation);
            $this->mailer->sendConfirmation($email, $this->links->url($token));
        });

        return $account;
    }
}
