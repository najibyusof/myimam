<?php

return [
    'management_title' => 'Pengurusan Hasil',
    'management_subtitle' => 'Urus transaksi hasil mengikut tempoh, akaun, dan kutipan Jumaat.',
    'add' => 'Tambah Hasil',
    'add_jumaat' => 'Tambah Kutipan Jumaat',
    'add_title' => 'Tambah Transaksi Hasil',
    'add_subtitle' => 'Rekod kutipan atau sumbangan masuk biasa (bukan kutipan Jumaat) mengikut tarikh, akaun, sumber hasil, dan tabung khas jika berkaitan.',
    'add_jumaat_title' => 'Tambah Kutipan Jumaat',
    'add_jumaat_subtitle' => 'Rekod khusus kutipan Jumaat mengikut tarikh, akaun, sumber hasil, dan tabung khas jika berkaitan.',
    'edit_title' => 'Kemaskini Transaksi Hasil',
    'edit_subtitle' => 'Laraskan butiran transaksi hasil biasa (bukan kutipan Jumaat).',
    'edit_jumaat_title' => 'Kemaskini Kutipan Jumaat',
    'edit_jumaat_subtitle' => 'Laraskan butiran transaksi khusus kutipan Jumaat.',

    'badge' => [
        'regular' => 'Hasil Biasa',
        'jumaat' => 'Kutipan Jumaat',
    ],

    'guard' => [
        'permission_denied_generic' => 'Anda tidak mempunyai kebenaran untuk mengakses halaman ini.',
        'permission_denied_create_jumaat' => 'Anda tidak mempunyai kebenaran untuk menambah Kutipan Jumaat.',
        'permission_denied_update_jumaat' => 'Anda tidak mempunyai kebenaran untuk mengemaskini Kutipan Jumaat.',
        'use_jumaat_edit' => 'Rekod ini adalah Kutipan Jumaat. Sila gunakan halaman kemaskini Kutipan Jumaat.',
        'not_jumaat_record' => 'Rekod ini bukan Kutipan Jumaat. Sila gunakan halaman kemaskini hasil biasa.',
        'missing_masjid_for_jumaat' => 'Masjid diperlukan untuk merekod kutipan Jumaat.',
        'missing_jumaat_source' => 'Tiada sumber hasil aktif untuk kutipan Jumaat. Sila tambah sumber hasil terlebih dahulu.',
    ],

    'stats' => [
        'total_transactions' => 'Jumlah Transaksi',
        'total_amount' => 'Jumlah Amaun',
        'jumaat_collection' => 'Kutipan Jumaat',
    ],

    'filters' => [
        'all_accounts' => 'Semua akaun',
        'all_collections' => 'Semua kutipan',
        'jumaat_only' => 'Jumaat sahaja',
        'non_jumaat' => 'Bukan Jumaat',
        'filter' => 'Tapis',
        'reset' => 'Reset',
    ],

    'table' => [
        'date' => 'Tarikh',
        'amount' => 'Amaun',
        'account' => 'Akaun',
        'source' => 'Sumber Hasil',
        'fund' => 'Tabung Khas',
        'jumaat' => 'Jumaat',
        'actions' => 'Tindakan',
        'yes' => 'Ya',
        'no' => 'Tidak',
        'edit' => 'Ubah',
        'delete' => 'Padam',
        'empty' => 'Tiada transaksi hasil dijumpai.',
    ],

    'form' => [
        'masjid' => 'Masjid',
        'select_masjid' => 'Pilih masjid',
        'date' => 'Tarikh',
        'amount' => 'Amaun',
        'account' => 'Akaun',
        'select_account' => 'Pilih akaun',
        'source' => 'Sumber Hasil',
        'select_source' => 'Pilih sumber hasil',
        'fund_optional' => 'Tabung Khas (Opsyenal)',
        'no_fund' => 'Tiada tabung khas',
        'jumaat_toggle' => 'Tandakan sebagai kutipan Jumaat',
        'regular_mode_notice' => 'Transaksi ini akan disimpan sebagai hasil biasa (bukan kutipan Jumaat).',
        'jumaat_mode_notice' => 'Transaksi ini akan disimpan sebagai kutipan Jumaat.',
        'notes_optional' => 'Catatan (Opsyenal)',
        'save' => 'Simpan',
        'back' => 'Kembali',
    ],

    'confirm_delete' => 'Padam transaksi hasil ini?',
];
