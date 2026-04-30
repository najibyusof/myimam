<?php

namespace Database\Seeders;

use App\Models\Akaun;
use App\Models\Belanja;
use App\Models\Hasil;
use App\Models\KategoriBelanja;
use App\Models\LogAktiviti;
use App\Models\Masjid;
use App\Models\NotificationLog;
use App\Models\ProgramMasjid;
use App\Models\PindahanAkaun;
use App\Models\SumberHasil;
use App\Models\TabungKhas;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Seeder;

class WorkflowScenarioSeeder extends Seeder
{
    public function run(): void
    {
        $masjids = Masjid::query()->orderBy('id')->get();

        foreach ($masjids as $index => $masjid) {
            try {
                $this->seedRevenueWorkflow($masjid, $index);
                $this->seedExpenseWorkflow($masjid, $index);
                $this->seedTransferWorkflow($masjid, $index);
                $this->seedActivityAndNotificationWorkflow($masjid, $index);
            } catch (ModelNotFoundException) {
                // Skip tenants that do not use the legacy workflow fixture names.
                continue;
            }
        }
    }

    private function seedRevenueWorkflow(Masjid $masjid, int $index): void
    {
        $cashAccount = Akaun::query()->where('id_masjid', $masjid->id)->where('nama_akaun', 'Tunai Utama')->firstOrFail();
        $bankAccount = Akaun::query()->where('id_masjid', $masjid->id)->where('nama_akaun', 'Bank Operasi')->firstOrFail();
        $jumaatSource = SumberHasil::query()->where('id_masjid', $masjid->id)->where('kod', 'SUMB-JMT')->firstOrFail();
        $onlineSource = SumberHasil::query()->where('id_masjid', $masjid->id)->where('kod', 'DERMA-IND')->firstOrFail();
        $fund = TabungKhas::query()->where('id_masjid', $masjid->id)->where('nama_tabung', 'Tabung Operasi Masjid')->firstOrFail();
        $financeOfficer = User::query()->where('id_masjid', $masjid->id)
            ->whereHas('roles', fn($query) => $query->where('name', 'FinanceOfficer'))
            ->orderBy('id')
            ->firstOrFail();

        foreach (range(0, 11) as $weekOffset) {
            $date = now()->startOfWeek()->subWeeks($weekOffset)->addDays(4);
            $jumaatAmount = 2800 + ($index * 450) + ($weekOffset * 55);
            $onlineAmount = 900 + ($index * 150) + ($weekOffset * 40);

            Hasil::query()->updateOrCreate(
                ['id_masjid' => $masjid->id, 'no_resit' => sprintf('JMT-%02d-%s', $masjid->id, $date->format('Ymd'))],
                [
                    'tarikh' => $date->toDateString(),
                    'id_akaun' => $cashAccount->id,
                    'id_sumber_hasil' => $jumaatSource->id,
                    'amaun_tunai' => $jumaatAmount,
                    'amaun_online' => 0,
                    'jumlah' => $jumaatAmount,
                    'id_tabung_khas' => null,
                    'id_program' => null,
                    'jenis_jumaat' => 'biasa',
                    'catatan' => 'Sumbangan selepas solat Jumaat',
                    'created_by' => $financeOfficer->id,
                ]
            );

            Hasil::query()->updateOrCreate(
                ['id_masjid' => $masjid->id, 'no_resit' => sprintf('ONL-%02d-%s', $masjid->id, $date->format('Ymd'))],
                [
                    'tarikh' => $date->toDateString(),
                    'id_akaun' => $bankAccount->id,
                    'id_sumber_hasil' => $onlineSource->id,
                    'amaun_tunai' => 0,
                    'amaun_online' => $onlineAmount,
                    'jumlah' => $onlineAmount,
                    'id_tabung_khas' => $fund->id,
                    'id_program' => null,
                    'jenis_jumaat' => null,
                    'catatan' => 'Derma daripada jemaah melalui pindahan online',
                    'created_by' => $financeOfficer->id,
                ]
            );
        }
    }

    private function seedExpenseWorkflow(Masjid $masjid, int $index): void
    {
        $bankAccount    = Akaun::query()->where('id_masjid', $masjid->id)->where('nama_akaun', 'Bank Operasi')->firstOrFail();
        $programAccount = Akaun::query()->where('id_masjid', $masjid->id)->where('nama_akaun', 'Bank Program Komuniti')->firstOrFail();
        $utilities      = KategoriBelanja::query()->where('id_masjid', $masjid->id)->where('kod', 'UTIL')->firstOrFail();
        $programmeCategory = KategoriBelanja::query()->where('id_masjid', $masjid->id)->where('kod', 'PENCERAMAH')->firstOrFail();
        $fund           = TabungKhas::query()->where('id_masjid', $masjid->id)->where('nama_tabung', 'Wakaf Bangunan Masjid')->firstOrFail();
        $program        = ProgramMasjid::query()->where('id_masjid', $masjid->id)->where('nama_program', 'Iftar Jamaie')->firstOrFail();

        $creator    = User::query()->where('id_masjid', $masjid->id)
            ->whereHas('roles', fn($q) => $q->where('name', 'FinanceOfficer'))
            ->orderBy('id')->firstOrFail();

        $bendahari  = User::query()->where('id_masjid', $masjid->id)
            ->whereHas('roles', fn($q) => $q->where('name', 'Bendahari'))
            ->orderBy('id')->first();

        $pengerusi  = User::query()->where('id_masjid', $masjid->id)
            ->whereHas('roles', fn($q) => $q->where('name', 'Pengerusi'))
            ->orderBy('id')->first();

        // --- Scenario 1: FULLY APPROVED & LOCKED (Pengerusi signed) ---
        $sig1 = strtoupper(substr(hash_hmac('sha256', 'bendahari|seeded|' . $masjid->id, (string) config('app.key')), 0, 24));
        $sig2 = strtoupper(substr(hash_hmac('sha256', 'pengerusi|seeded|' . $masjid->id, (string) config('app.key')), 0, 24));

        $baucarNo1 = 'BV-' . now()->year . '-' . str_pad((string) ($masjid->id * 100 + 1), 6, '0', STR_PAD_LEFT);

        $locked = Belanja::query()->updateOrCreate(
            [
                'id_masjid'            => $masjid->id,
                'penerima'             => 'Tenaga Nasional Berhad',
                'id_kategori_belanja'  => $utilities->id,
                'tarikh'               => now()->subDays(15)->toDateString(),
            ],
            [
                'id_akaun'             => $bankAccount->id,
                'amaun'                => 1800 + ($index * 150),
                'catatan'              => 'Bil utiliti bulanan masjid - diluluskan sepenuhnya',
                'bukti_fail'           => 'bukti/utiliti-' . $masjid->id . '.pdf',
                'created_by'           => $creator->id,
                'no_baucar'            => $baucarNo1,
                'status'               => 'LULUS',
                'approval_step'        => 2,
                'bendahari_lulus_oleh' => $bendahari?->id,
                'bendahari_lulus_pada' => now()->subDays(13),
                'bendahari_signature'  => $sig1,
                'pengerusi_lulus_oleh' => $pengerusi?->id,
                'pengerusi_lulus_pada' => now()->subDays(12),
                'pengerusi_signature'  => $sig2,
                'dilulus_oleh'         => $pengerusi?->id,
                'tarikh_lulus'         => now()->subDays(12),
                'is_baucar_locked'     => true,
                'locked_at'            => now()->subDays(12),
                'locked_by'            => $pengerusi?->id,
                'is_deleted'           => false,
            ]
        );

        // --- Scenario 2: PENDING PENGERUSI (Bendahari approved, Pengerusi not yet) ---
        $sig3 = strtoupper(substr(hash_hmac('sha256', 'bendahari|pending|' . $masjid->id, (string) config('app.key')), 0, 24));

        $baucarNo2 = 'BV-' . now()->year . '-' . str_pad((string) ($masjid->id * 100 + 2), 6, '0', STR_PAD_LEFT);

        Belanja::query()->updateOrCreate(
            [
                'id_masjid'           => $masjid->id,
                'penerima'            => 'Ustaz Ahmad bin Ismail',
                'id_kategori_belanja' => $programmeCategory->id,
                'tarikh'              => now()->subDays(4)->toDateString(),
            ],
            [
                'id_akaun'             => $programAccount->id,
                'amaun'                => 2200 + ($index * 200),
                'catatan'              => 'Honorarium penceramah majlis - menunggu kelulusan Pengerusi',
                'bukti_fail'           => null,
                'created_by'           => $creator->id,
                'no_baucar'            => $baucarNo2,
                'id_tabung_khas'       => $fund->id,
                'id_program'           => $program->id,
                'status'               => 'DRAF',
                'approval_step'        => 1,
                'bendahari_lulus_oleh' => $bendahari?->id,
                'bendahari_lulus_pada' => now()->subDays(2),
                'bendahari_signature'  => $sig3,
                'pengerusi_lulus_oleh' => null,
                'pengerusi_lulus_pada' => null,
                'pengerusi_signature'  => null,
                'dilulus_oleh'         => null,
                'tarikh_lulus'         => null,
                'is_baucar_locked'     => false,
                'is_deleted'           => false,
            ]
        );

        // --- Scenario 3: DRAFT — new, not yet submitted to Bendahari ---
        $baucarNo3 = 'BV-' . now()->year . '-' . str_pad((string) ($masjid->id * 100 + 3), 6, '0', STR_PAD_LEFT);

        Belanja::query()->updateOrCreate(
            [
                'id_masjid'           => $masjid->id,
                'penerima'            => 'Syarikat Penyelenggaraan Maju Sdn Bhd',
                'id_kategori_belanja' => $utilities->id,
                'tarikh'              => now()->subDays(1)->toDateString(),
            ],
            [
                'id_akaun'             => $bankAccount->id,
                'amaun'                => 650 + ($index * 50),
                'catatan'              => 'Kerja-kerja penyelenggaraan elektrik dan paip - draf baru',
                'bukti_fail'           => null,
                'created_by'           => $creator->id,
                'no_baucar'            => $baucarNo3,
                'status'               => 'DRAF',
                'approval_step'        => 0,
                'bendahari_lulus_oleh' => null,
                'bendahari_lulus_pada' => null,
                'bendahari_signature'  => null,
                'pengerusi_lulus_oleh' => null,
                'pengerusi_lulus_pada' => null,
                'pengerusi_signature'  => null,
                'dilulus_oleh'         => null,
                'tarikh_lulus'         => null,
                'is_baucar_locked'     => false,
                'is_deleted'           => false,
            ]
        );

        // --- Scenario 4: REJECTED ---
        Belanja::query()->updateOrCreate(
            [
                'id_masjid'           => $masjid->id,
                'penerima'            => 'Percetakan Al-Amin',
                'id_kategori_belanja' => $programmeCategory->id,
                'tarikh'              => now()->subDays(20)->toDateString(),
            ],
            [
                'id_akaun'             => $bankAccount->id,
                'amaun'                => 480 + ($index * 30),
                'catatan'              => 'Cetakan brosur program - ditolak, perlu semak semula',
                'bukti_fail'           => null,
                'created_by'           => $creator->id,
                'status'               => 'DRAF',
                'approval_step'        => 0,
                'ditolak_oleh'         => $bendahari?->id,
                'tarikh_tolak'         => now()->subDays(18),
                'catatan_tolak'        => 'Jumlah tidak sepadan dengan sebut harga. Sila semak semula.',
                'bendahari_lulus_oleh' => null,
                'bendahari_lulus_pada' => null,
                'bendahari_signature'  => null,
                'dilulus_oleh'         => null,
                'tarikh_lulus'         => null,
                'is_baucar_locked'     => false,
                'is_deleted'           => false,
            ]
        );

        // --- Scenario 5: SOFT DELETED ---
        Belanja::query()->updateOrCreate(
            [
                'id_masjid'           => $masjid->id,
                'penerima'            => 'Syarikat Maju Jaya',
                'id_kategori_belanja' => $programmeCategory->id,
                'tarikh'              => now()->subDays(18)->toDateString(),
            ],
            [
                'id_akaun'         => $bankAccount->id,
                'amaun'            => 750.00,
                'catatan'          => 'Contoh rekod untuk kes padam logik',
                'bukti_fail'       => null,
                'created_by'       => $creator->id,
                'status'           => 'DRAF',
                'approval_step'    => 0,
                'is_deleted'       => true,
                'deleted_by'       => $creator->id,
                'deleted_at'       => now()->subDays(17),
                'dilulus_oleh'     => null,
                'tarikh_lulus'     => null,
                'is_baucar_locked' => false,
            ]
        );

        // Log approval events for the locked baucar
        if ($locked->id && $bendahari && $pengerusi) {
            LogAktiviti::query()->updateOrCreate(
                [
                    'id_masjid'  => $masjid->id,
                    'id_user'    => $bendahari->id,
                    'modul'      => 'Baucar',
                    'aksi'       => 'Lulus Bendahari',
                    'rujukan_id' => $locked->id,
                ],
                [
                    'jenis'      => 'APPROVE',
                    'butiran'    => 'Baucar ' . $baucarNo1 . ' telah disemak dan diluluskan oleh Bendahari.',
                    'data_baru'  => ['approval_step' => 1, 'scenario' => 'seeded'],
                    'ip'         => '127.0.0.1',
                    'user_agent' => 'Seeder/WorkflowScenarioSeeder',
                    'created_at' => now()->subDays(13),
                ]
            );

            LogAktiviti::query()->updateOrCreate(
                [
                    'id_masjid'  => $masjid->id,
                    'id_user'    => $pengerusi->id,
                    'modul'      => 'Baucar',
                    'aksi'       => 'Lulus Pengerusi',
                    'rujukan_id' => $locked->id,
                ],
                [
                    'jenis'      => 'APPROVE',
                    'butiran'    => 'Baucar ' . $baucarNo1 . ' telah diluluskan oleh Pengerusi dan dikunci.',
                    'data_baru'  => ['approval_step' => 2, 'is_locked' => true, 'scenario' => 'seeded'],
                    'ip'         => '127.0.0.1',
                    'user_agent' => 'Seeder/WorkflowScenarioSeeder',
                    'created_at' => now()->subDays(12),
                ]
            );
        }
    }

    private function seedTransferWorkflow(Masjid $masjid, int $index): void
    {
        $from = Akaun::query()->where('id_masjid', $masjid->id)->where('nama_akaun', 'Bank Operasi')->firstOrFail();
        $to = Akaun::query()->where('id_masjid', $masjid->id)->where('nama_akaun', 'Bank Program Komuniti')->firstOrFail();
        $creator = User::query()->where('id_masjid', $masjid->id)
            ->whereHas('roles', fn($query) => $query->where('name', 'FinanceOfficer'))
            ->orderBy('id')
            ->firstOrFail();

        PindahanAkaun::query()->updateOrCreate(
            [
                'id_masjid' => $masjid->id,
                'tarikh' => now()->subDays(6)->toDateString(),
                'dari_akaun_id' => $from->id,
                'ke_akaun_id' => $to->id,
            ],
            [
                'amaun' => 5000 + ($index * 500),
                'catatan' => 'Pemindahan baki untuk sokongan aktiviti masjid',
                'created_by' => $creator->id,
            ]
        );
    }

    private function seedActivityAndNotificationWorkflow(Masjid $masjid, int $index): void
    {
        $financeOfficer = User::query()->where('id_masjid', $masjid->id)
            ->whereHas('roles', fn($query) => $query->where('name', 'FinanceOfficer'))
            ->orderBy('id')
            ->firstOrFail();
        $manager = User::query()->whereHas('roles', fn($query) => $query->where('name', 'Manager'))->orderBy('id')->first()
            ?? User::query()->whereHas('roles', fn($query) => $query->where('name', 'Admin'))->orderBy('id')->firstOrFail();

        $activities = [
            ['jenis' => 'CREATE',  'modul' => 'Hasil',   'aksi' => 'rekod kutipan Jumaat',    'butiran' => 'Kutipan mingguan berjaya direkodkan',                          'user_id' => $financeOfficer->id, 'created_at' => now()->subHours(18)],
            ['jenis' => 'UPDATE',  'modul' => 'Belanja',  'aksi' => 'semakan draft baucar',    'butiran' => 'Draf baucar komuniti dikemaskini untuk semakan',                'user_id' => $financeOfficer->id, 'created_at' => now()->subHours(9)],
            ['jenis' => 'APPROVE', 'modul' => 'Baucar',   'aksi' => 'Lulus Bendahari',         'butiran' => 'Baucar diluluskan oleh Bendahari — menunggu kelulusan Pengerusi', 'user_id' => $manager->id,        'created_at' => now()->subHours(6)],
            ['jenis' => 'APPROVE', 'modul' => 'Baucar',   'aksi' => 'Lulus Pengerusi',         'butiran' => 'Baucar diluluskan oleh Pengerusi dan dikunci sebagai rasmi.',    'user_id' => $manager->id,        'created_at' => now()->subHours(4)],
            ['jenis' => 'UPDATE',  'modul' => 'Profil',   'aksi' => 'Tandatangan Dimuat Naik', 'butiran' => 'Tandatangan digital dimuat naik ke profil pengguna.',            'user_id' => $manager->id,        'created_at' => now()->subHours(2)],
        ];

        foreach ($activities as $activity) {
            LogAktiviti::query()->updateOrCreate(
                [
                    'id_masjid' => $masjid->id,
                    'id_user' => $activity['user_id'],
                    'modul' => $activity['modul'],
                    'aksi' => $activity['aksi'],
                    'created_at' => $activity['created_at'],
                ],
                [
                    'jenis' => $activity['jenis'],
                    'butiran' => $activity['butiran'],
                    'rujukan_id' => null,
                    'data_lama' => null,
                    'data_baru' => ['scenario' => 'seeded-workflow', 'masjid' => $masjid->nama],
                    'ip' => '127.0.0.1',
                    'user_agent' => 'Seeder/WorkflowScenarioSeeder',
                ]
            );
        }

        $logs = [
            ['suffix' => '001', 'user' => $financeOfficer, 'channel' => 'email', 'subject' => 'Permohonan kelulusan bajet dihantar', 'message' => 'Notifikasi kelulusan bajet berjaya dihantar.', 'status' => 'sent', 'error' => null, 'retry' => 0, 'sent_at' => now()->subHours(3)],
            ['suffix' => '002', 'user' => $financeOfficer, 'channel' => 'telegram', 'subject' => 'Amaran transaksi gagal', 'message' => 'Penghantaran notifikasi Telegram gagal untuk transaksi pembayaran.', 'status' => 'failed', 'error' => 'Saluran Telegram tidak dapat dihubungi', 'retry' => 2, 'sent_at' => null],
            ['suffix' => '003', 'user' => $manager, 'channel' => 'database', 'subject' => 'Draf baucar menunggu tindakan', 'message' => 'Draf baucar masih menunggu tindakan pengurus.', 'status' => 'pending', 'error' => null, 'retry' => 0, 'sent_at' => null],
        ];

        foreach ($logs as $log) {
            NotificationLog::query()->updateOrCreate(
                [
                    'notification_id' => sprintf('00000000-0000-0000-0000-%012d', ($index + 1) * 100 + (int) $log['suffix']),
                    'channel' => $log['channel'],
                    'notifiable_type' => $log['user']::class,
                    'notifiable_id' => $log['user']->id,
                ],
                [
                    'subject' => $log['subject'],
                    'message' => $log['message'],
                    'status' => $log['status'],
                    'error_message' => $log['error'],
                    'retry_count' => $log['retry'],
                    'sent_at' => $log['sent_at'],
                ]
            );
        }
    }
}
