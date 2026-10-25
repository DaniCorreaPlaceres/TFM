<?php

declare(strict_types=1);

namespace App\Identity;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

final class SymfonyAccountMailer implements AccountMailer
{
    public function __construct(
        private readonly MailerInterface $mailer,
        #[Autowire('%app.mailer_from%')]
        private readonly string $from,
    ) {
    }

    public function sendConfirmation(string $to, string $url): void
    {
        $message = (new Email())
            ->from($this->from)
            ->to($to)
            ->subject('Confirma tu correo en Aselto')
            ->text("Para confirmar tu correo, abre este enlace en los próximos 30 minutos:\n\n".$url."\n\nSi no has creado la cuenta, ignora este mensaje.\n");

        $this->mailer->send($message);
    }
}
