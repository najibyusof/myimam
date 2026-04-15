<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class LaporanDermaExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function headings(): array
    {
        $jenisPaparan = $this->data['filters']['jenis_paparan'] ?? 'ringkasan_sumber';

        if ($jenisPaparan === 'ringkasan_bulan') {
            return ['Bulan', 'Jumlah Derma (RM)', 'Bil. Rekod'];
        } elseif ($jenisPaparan === 'senarai_transaksi') {
            return ['Tarikh', 'Sumber', 'No. Resit', 'Jumlah (RM)', 'Catatan'];
        }

        return ['Sumber Hasil', 'Jumlah Derma (RM)', 'Bil. Rekod'];
    }

    public function collection()
    {
        $jenisPaparan = $this->data['filters']['jenis_paparan'] ?? 'ringkasan_sumber';
        $rows = collect();

        if ($jenisPaparan === 'ringkasan_bulan') {
            foreach ($this->data['ringkasan_bulan'] as $row) {
                $rows->push([
                    $row['bulan'],
                    $row['jumlah'],
                    $row['bil_rekod'],
                ]);
            }
            $rows->push(['JUMLAH KESELURUHAN', $this->data['jumlah_keseluruhan'], $this->data['ringkasan_bulan']->sum('bil_rekod')]);
        } elseif ($jenisPaparan === 'senarai_transaksi') {
            foreach ($this->data['senarai_rows'] as $row) {
                $rows->push([
                    $row['tarikh'],
                    $row['sumber'],
                    $row['no_resit'],
                    $row['jumlah'],
                    $row['catatan'],
                ]);
            }
            $rows->push(['', 'JUMLAH KESELURUHAN', '', $this->data['jumlah_keseluruhan'], '']);
        } else {
            foreach ($this->data['rows'] as $row) {
                $rows->push([
                    $row['sumber'],
                    $row['jumlah'],
                    $row['bil_rekod'],
                ]);
            }
            $rows->push(['JUMLAH KESELURUHAN', $this->data['jumlah_keseluruhan'], $this->data['rows']->sum('bil_rekod')]);
        }

        return $rows;
    }
}
