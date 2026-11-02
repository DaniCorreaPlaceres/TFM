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

    public function sendPasswordReset(string $to, string $url): void
    {
        $message = (new Email())
            ->from($this->from)
            ->to($to)
            ->subject('Recupera tu contraseña en Aselto')
            ->text("Para elegir una contraseña nueva, abre este enlace en los próximos 30 minutos:\n\n".$url."\n\nSi no has pedido este cambio, ignora este mensaje.\n");

        $this->mailer->send($message);
    }

    public function sendAdvisoryInvitation(string $to, string $firmName, string $url): void
    {
        $message = (new Email())
            ->from($this->from)
            ->to($to)
            ->subject('Invitación a una asesoría en Aselto')
            ->text('Te han invitado a la asesoría '.$firmName.".\n\nSi creas la cuenta con este correo, al confirmarla quedas en la asesoría. Si ya la tienes, entra con ese correo. El enlace caduca a los 7 días:\n\n".$url."\n\nEl segundo factor no es obligatorio. Si no esperabas esta invitación, ignora este mensaje.\n");

        $this->mailer->send($message);
    }

    public function sendAdvisoryClientInvitation(string $to, string $partyName, string $url): void
    {
        $message = (new Email())
            ->from($this->from)
            ->to($to)
            ->subject('Invitación para vincular una asesoría en Aselto')
            ->text('Te han invitado a vincular '.$partyName.".\n\nEntra con este correo y abre el enlace en los próximos 7 días. Si todavía no tienes la asesoría o la entidad, créala antes de aceptar:\n\n".$url."\n\nEl segundo factor no es obligatorio. Si no esperabas esta invitación, ignora este mensaje.\n");

        $this->mailer->send($message);
    }
}
