<?php

declare(strict_types=1);

namespace App\Income;

final class IncomeCsv
{
    private const array COLUMNS = ['fecha', 'numero', 'contacto', 'nif', 'descripcion', 'cantidad', 'precio', 'iva', 'retencion'];

    public function read(string $contents): CsvTable
    {
        if (str_starts_with($contents, "\xEF\xBB\xBF")) {
            $contents = substr($contents, 3);
        }
        if ('' === trim($contents)) {
            return $this->blocked(1, 'El archivo está vacío.');
        }
        $delimiter = $this->delimiter($contents);
        $handle = fopen('php://temp', 'r+');
        if (!is_resource($handle)) {
            return $this->blocked(1, 'El archivo está vacío.');
        }
        fwrite($handle, $contents);
        rewind($handle);
        $header = fgetcsv($handle, null, $delimiter, '"', '');
        if (!is_array($header)) {
            fclose($handle);

            return $this->blocked(1, 'El archivo está vacío.');
        }
        $indexes = [];
        foreach ($header as $position => $cell) {
            if (!is_string($cell)) {
                continue;
            }
            $name = $this->header($cell);
            if ('' === $name) {
                continue;
            }
            if (isset($indexes[$name])) {
                fclose($handle);

                return $this->blocked(1, 'La columna '.$name.' está repetida.');
            }
            $indexes[$name] = (int) $position;
        }
        $missing = [];
        foreach (self::COLUMNS as $column) {
            if (!isset($indexes[$column])) {
                $missing[] = $column;
            }
        }
        if ([] !== $missing) {
            fclose($handle);

            return $this->blocked(1, 'Faltan las columnas '.implode(', ', $missing).'.');
        }
        $rows = [];
        $number = 1;
        while (false !== ($record = fgetcsv($handle, null, $delimiter, '"', ''))) {
            ++$number;
            if ($this->emptyRecord($record)) {
                continue;
            }
            if (count($rows) >= 200) {
                fclose($handle);

                return $this->blocked($number, 'El archivo supera las 200 filas.');
            }
            $rows[] = [
                'row' => $number,
                'fecha' => $this->cell($record, $indexes['fecha']),
                'numero' => $this->cell($record, $indexes['numero']),
                'contacto' => $this->cell($record, $indexes['contacto']),
                'nif' => $this->cell($record, $indexes['nif']),
                'descripcion' => $this->cell($record, $indexes['descripcion']),
                'cantidad' => $this->cell($record, $indexes['cantidad']),
                'precio' => $this->cell($record, $indexes['precio']),
                'iva' => $this->cell($record, $indexes['iva']),
                'retencion' => $this->cell($record, $indexes['retencion']),
            ];
        }
        fclose($handle);
        if ([] === $rows) {
            return $this->blocked(1, 'El archivo no tiene filas.');
        }

        return new CsvTable($rows, [], false);
    }

    private function blocked(int $row, string $message): CsvTable
    {
        return new CsvTable([], [['row' => $row, 'message' => $message]], true);
    }

    private function delimiter(string $contents): string
    {
        $line = strtok($contents, "\r\n");
        if (!is_string($line)) {
            $line = '';
        }

        return substr_count($line, ';') >= substr_count($line, ',') && str_contains($line, ';') ? ';' : ',';
    }

    private function header(string $cell): string
    {
        $cell = mb_strtolower(trim($cell));

        return strtr($cell, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n']);
    }

    /**
     * @param array<int, string|null> $record
     */
    private function cell(array $record, int $index): string
    {
        return trim($record[$index] ?? '');
    }

    /**
     * @param array<int, string|null> $record
     */
    private function emptyRecord(array $record): bool
    {
        foreach ($record as $cell) {
            if (is_string($cell) && '' !== trim($cell)) {
                return false;
            }
        }

        return true;
    }
}
