<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class LaporanJumaatExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(private readonly array $data) {}

    public function headings(): array
    {
        return ['Bulan', 'Jumlah Kutipan (RM)', 'Bil. Rekod'];
    }

    public function collection(): Collection
    {
        $rows = collect($this->data['rows'] ?? [])->map(function (array $row): array {
            return [
                'Bulan' => (string) ($row['bulan'] ?? ''),
                'Jumlah Kutipan (RM)' => number_format((float) ($row['jumlah'] ?? 0), 2, '.', ','),
                'Bil. Rekod' => (int) ($row['bil_rekod'] ?? 0),
            ];
        });

        $rows->push([
            'Bulan' => 'Jumlah Setahun',
            'Jumlah Kutipan (RM)' => number_format((float) ($this->data['jumlah_setahun'] ?? 0), 2, '.', ','),
            'Bil. Rekod' => (int) $rows->sum('Bil. Rekod'),
        ]);

        return $rows;
    }
}
