<?php

declare(strict_types=1);

namespace App\Identity;

final class PwnedPasswordLookupFailed extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('No se pudo consultar la lista de contraseñas filtradas.');
    }
}
