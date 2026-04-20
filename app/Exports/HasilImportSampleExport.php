<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class HasilImportSampleExport implements FromArray, WithHeadings, ShouldAutoSize
{
    public function headings(): array
    {
        return ['tarikh', 'sumber', 'amaun', 'akaun', 'catatan', 'tabung_khas'];
    }

    public function array(): array
    {
        return [
            ['01/01/2026', 'Derma Individu', '100.00', 'Tunai', 'Sumbangan jemaah', 'Tabung Umum'],
            ['05/01/2026', 'Sumbangan Jumaat', '280.00', 'Tunai Utama', 'Kutipan selepas solat', 'Tabung Operasi Masjid'],
            ['09/01/2026', 'Wakaf Pembinaan', '500.00', 'Bank Operasi', 'Wakaf bina pagar', 'Wakaf Bangunan Masjid'],
            ['12/01/2026', 'Derma Individu', '75.50', 'Tunai', 'Sumbangan program remaja', ''],
        ];
    }
}
