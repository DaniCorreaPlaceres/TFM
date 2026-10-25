<?php

declare(strict_types=1);

namespace App\Identity;

final class InvalidConfirmation extends \DomainException
{
    public function __construct()
    {
        parent::__construct('El enlace no es válido o ha caducado.');
    }
}
