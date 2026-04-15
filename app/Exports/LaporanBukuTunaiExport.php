<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class LaporanBukuTunaiExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    /**
     * @param array<string, mixed> $laporan
     */
    public function __construct(private readonly array $laporan) {}

    public function headings(): array
    {
        return ['Tarikh', 'Butiran', 'Masuk (RM)', 'Keluar (RM)', 'Baki (RM)'];
    }

    public function collection(): Collection
    {
        $rows = collect([
            [
                'Tarikh' => '-',
                'Butiran' => 'Baki Awal',
                'Masuk (RM)' => number_format(0, 2, '.', ','),
                'Keluar (RM)' => number_format(0, 2, '.', ','),
                'Baki (RM)' => number_format((float) ($this->laporan['ringkasan']['baki_awal'] ?? 0), 2, '.', ','),
            ],
        ]);

        $transactionRows = collect($this->laporan['rows'] ?? [])->map(function (array $row): array {
            return [
                'Tarikh' => (string) ($row['tarikh'] ?? '-'),
                'Butiran' => (string) ($row['butiran'] ?? ''),
                'Masuk (RM)' => number_format((float) ($row['masuk'] ?? 0), 2, '.', ','),
                'Keluar (RM)' => number_format((float) ($row['keluar'] ?? 0), 2, '.', ','),
                'Baki (RM)' => number_format((float) ($row['baki'] ?? 0), 2, '.', ','),
            ];
        });

        $summary = [
            'Tarikh' => '-',
            'Butiran' => 'Ringkasan Tempoh',
            'Masuk (RM)' => number_format((float) ($this->laporan['ringkasan']['jumlah_masuk'] ?? 0), 2, '.', ','),
            'Keluar (RM)' => number_format((float) ($this->laporan['ringkasan']['jumlah_keluar'] ?? 0), 2, '.', ','),
            'Baki (RM)' => number_format((float) ($this->laporan['ringkasan']['baki_akhir'] ?? 0), 2, '.', ','),
        ];

        return $rows
            ->concat($transactionRows)
            ->push($summary)
            ->values();
    }
}
