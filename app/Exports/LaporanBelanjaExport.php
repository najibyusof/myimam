<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class LaporanBelanjaExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function headings(): array
    {
        $jenisPaparan = $this->data['filters']['jenis_paparan'] ?? 'ringkasan_kategori';

        if ($jenisPaparan === 'ringkasan_bulan') {
            return ['Bulan', 'Jumlah Belanja (RM)', 'Bil. Rekod'];
        }

        if ($jenisPaparan === 'senarai_transaksi') {
            return ['Tarikh', 'Kategori', 'Akaun', 'Penerima', 'Amaun (RM)', 'Status', 'Catatan'];
        }

        return ['Kategori', 'Jumlah Belanja (RM)', 'Bil. Rekod'];
    }

    public function collection(): Collection
    {
        $jenisPaparan = $this->data['filters']['jenis_paparan'] ?? 'ringkasan_kategori';
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

            return $rows;
        }

        if ($jenisPaparan === 'senarai_transaksi') {
            foreach ($this->data['senarai_rows'] as $row) {
                $rows->push([
                    $row['tarikh'],
                    $row['kategori'],
                    $row['akaun'],
                    $row['penerima'],
                    $row['amaun'],
                    $row['status'],
                    $row['catatan'],
                ]);
            }

            $rows->push(['', '', '', 'JUMLAH KESELURUHAN', $this->data['jumlah_keseluruhan'], '', '']);

            return $rows;
        }

        foreach ($this->data['rows'] as $row) {
            $rows->push([
                $row['kategori'],
                $row['jumlah'],
                $row['bil_rekod'],
            ]);
        }

        $rows->push(['JUMLAH KESELURUHAN', $this->data['jumlah_keseluruhan'], $this->data['rows']->sum('bil_rekod')]);

        return $rows;
    }
}
