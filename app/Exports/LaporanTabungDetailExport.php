<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class LaporanTabungDetailExport implements FromCollection, ShouldAutoSize
{
    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function collection(): Collection
    {
        $rows = collect();

        $rows->push(['DETAIL TABUNG KHAS']);
        $rows->push(['Nama Tabung', $this->data['tabung']->nama_tabung]);
        $rows->push(['Tempoh', $this->data['tempoh_label']]);
        $rows->push([]);

        $rows->push(['RINGKASAN']);
        $rows->push(['Baki Awal', $this->data['baki_awal']]);
        $rows->push(['Jumlah Masuk', $this->data['jumlah_masuk']]);
        $rows->push(['Jumlah Keluar', $this->data['jumlah_keluar']]);
        $rows->push(['Baki Akhir', $this->data['baki_akhir']]);
        $rows->push([]);

        $rows->push(['TIMELINE BAKI BERJALAN']);
        $rows->push(['Tarikh', 'Jenis', 'Rujukan', 'Butiran', 'Masuk', 'Keluar', 'Baki Berjalan']);
        foreach ($this->data['timeline_rows'] as $row) {
            $rows->push([
                $row['tarikh'],
                $row['jenis'],
                $row['rujukan'],
                $row['butiran'],
                $row['masuk'],
                $row['keluar'],
                $row['baki_berjalan'],
            ]);
        }
        $rows->push([]);

        $rows->push(['TRANSAKSI MASUK']);
        $rows->push(['Tarikh', 'Sumber Hasil', 'Akaun', 'Catatan', 'Tunai', 'Online', 'Jumlah']);
        foreach ($this->data['transaksi_masuk'] as $row) {
            $rows->push([
                $row['tarikh'],
                $row['sumber_hasil'],
                $row['akaun'],
                $row['catatan'],
                $row['tunai'],
                $row['online'],
                $row['jumlah'],
            ]);
        }
        $rows->push(['', '', '', '', '', 'Jumlah Masuk', $this->data['jumlah_masuk']]);
        $rows->push([]);

        $rows->push(['TRANSAKSI KELUAR']);
        $rows->push(['Tarikh', 'Kategori', 'Penerima', 'Akaun', 'Catatan', 'Amaun']);
        foreach ($this->data['transaksi_keluar'] as $row) {
            $rows->push([
                $row['tarikh'],
                $row['kategori'],
                $row['penerima'],
                $row['akaun'],
                $row['catatan'],
                $row['amaun'],
            ]);
        }
        $rows->push(['', '', '', '', 'Jumlah Keluar', $this->data['jumlah_keluar']]);

        return $rows;
    }
}
