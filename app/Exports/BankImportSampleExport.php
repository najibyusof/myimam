<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BankImportSampleExport implements FromArray, WithHeadings, ShouldAutoSize
{
    public function headings(): array
    {
        return ['tarikh', 'description', 'akaun', 'debit', 'credit', 'balance'];
    }

    public function array(): array
    {
        return [
            ['01/01/2026', 'DERMA Program Jumaat', 'Tunai Utama', '0.00', '350.00', '1350.00'],
            ['02/01/2026', 'BAYAR BIL ELEKTRIK', 'Maybank 1234', '220.00', '0.00', '1130.00'],
            ['03/01/2026', 'SUMBANGAN Orang Ramai', 'Tunai Utama', '0.00', '150.00', '1280.00'],
            ['04/01/2026', 'BAYAR UTILITI AIR', 'Maybank 1234', '95.50', '0.00', '1184.50'],
        ];
    }
}
