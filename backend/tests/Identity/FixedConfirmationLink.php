<?php

declare(strict_types=1);

namespace App\Tests\Identity;

use App\Identity\ConfirmationLinkFactory;

final class FixedConfirmationLink implements ConfirmationLinkFactory
{
    public function url(string $token): string
    {
        return 'https://example.test/registro/confirmar/'.$token;
    }
}
