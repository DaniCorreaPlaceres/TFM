<?php

declare(strict_types=1);

namespace App\Identity;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class RoutedPasswordResetLink implements PasswordResetLinkFactory
{
    public function __construct(private readonly UrlGeneratorInterface $urls)
    {
    }

    public function url(string $token): string
    {
        return $this->urls->generate('account_password_reset', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
