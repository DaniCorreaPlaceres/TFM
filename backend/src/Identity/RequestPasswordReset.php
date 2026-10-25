<?php

declare(strict_types=1);

namespace App\Identity;

use App\Entity\PasswordReset;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class RequestPasswordReset
{
    public function __construct(
        private readonly AccountRepository $accounts,
        private readonly PasswordResetRepository $resets,
        private readonly RegistrationStore $store,
        private readonly AccountMailer $mailer,
        private readonly PasswordResetLinkFactory $links,
        private readonly ClockInterface $clock,
        #[Autowire(service: 'App\Identity\ResetAttemptLimiter')]
        private readonly AttemptLimiter $attempts,
    ) {
    }

    public function __invoke(string $email): void
    {
        $email = mb_strtolower(trim($email));
        if (strlen($email) > 180 || false === filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        if (!$this->attempts->accept($email)) {
            return;
        }

        $account = $this->accounts->findByEmail($email);
        if (null === $account || !$account->allowsFiscalAccess()) {
            return;
        }

        $now = $this->clock->now();
        $token = bin2hex(random_bytes(32));
        $reset = PasswordReset::issue($account, hash('sha256', $token), $now->modify('+30 minutes'));
        $this->store->commit(function () use ($account, $reset, $email, $token, $now): void {
            foreach ($this->resets->findByAccount($account) as $previous) {
                if ($previous->isUsableAt($now)) {
                    $previous->use($now);
                    $this->resets->save($previous);
                }
            }
            $this->resets->save($reset);
            $this->mailer->sendPasswordReset($email, $this->links->url($token));
        });
    }
}
