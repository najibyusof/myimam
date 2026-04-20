<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class HasilImportErrorExport implements FromArray, WithHeadings, ShouldAutoSize
{
    /**
     * @param array<int, array<string, mixed>> $rows
     */
    public function __construct(private readonly array $rows) {}

    public function headings(): array
    {
        return ['row', 'tarikh', 'sumber', 'amaun', 'akaun', 'catatan', 'tabung_khas', 'ralat'];
    }

    public function array(): array
    {
        return collect($this->rows)->map(function (array $row): array {
            $data = $row['data'] ?? [];

            return [
                (int) ($row['row_number'] ?? 0),
                (string) ($data['tarikh'] ?? ''),
                (string) ($data['sumber'] ?? ''),
                (string) ($data['amaun'] ?? ''),
                (string) ($data['akaun'] ?? ''),
                (string) ($data['catatan'] ?? ''),
                (string) ($data['tabung_khas'] ?? ''),
                implode('; ', $row['errors'] ?? []),
            ];
        })->values()->all();
    }
}
