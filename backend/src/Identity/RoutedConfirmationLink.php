<?php

declare(strict_types=1);

namespace App\Identity;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class RoutedConfirmationLink implements ConfirmationLinkFactory
{
    public function __construct(private readonly UrlGeneratorInterface $urls)
    {
    }

    public function url(string $token): string
    {
        return $this->urls->generate('account_confirm', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
