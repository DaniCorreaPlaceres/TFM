<?php

declare(strict_types=1);

namespace App\Income;

final readonly class CsvTable
{
    /**
     * @param list<array{row: int, fecha: string, numero: string, contacto: string, nif: string, descripcion: string, cantidad: string, precio: string, iva: string, retencion: string}> $rows
     * @param list<array{row: int, message: string}>                                                                                                                                     $errors
     */
    public function __construct(
        public array $rows,
        public array $errors,
        public bool $blocked,
    ) {
    }
}
