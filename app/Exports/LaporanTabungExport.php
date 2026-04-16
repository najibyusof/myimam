<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class LaporanTabungExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function headings(): array
    {
        return [
            'Nama Tabung',
            'Masuk Tempoh (RM)',
            'Keluar Tempoh (RM)',
            'Baki Terkumpul (RM)',
        ];
    }

    public function collection(): Collection
    {
        $rows = collect();

        foreach ($this->data['rows'] as $row) {
            $rows->push([
                $row['nama_tabung'],
                $row['masuk_tempoh'],
                $row['keluar_tempoh'],
                $row['baki_terkumpul'],
            ]);
        }

        $rows->push([
            'JUMLAH KESELURUHAN',
            $this->data['total_masuk'],
            $this->data['total_keluar'],
            $this->data['total_baki'],
        ]);

        return $rows;
    }
}
