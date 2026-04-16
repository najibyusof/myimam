<?php

namespace Database\Seeders;

use App\Models\Akaun;
use App\Models\BaucarBayaran;
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
        $jumaatSource = SumberHasil::query()->where('id_masjid', $masjid->id)->where('kod', 'DERMA-JMT')->firstOrFail();
        $onlineSource = SumberHasil::query()->where('id_masjid', $masjid->id)->where('kod', 'ONLINE')->firstOrFail();
        $fund = TabungKhas::query()->where('id_masjid', $masjid->id)->where('nama_tabung', 'Tabung Operasi')->firstOrFail();
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
                    'catatan' => 'Kutipan Jumaat mingguan',
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
                    'catatan' => 'Sumbangan online automatik',
                    'created_by' => $financeOfficer->id,
                ]
            );
        }
    }

    private function seedExpenseWorkflow(Masjid $masjid, int $index): void
    {
        $bankAccount = Akaun::query()->where('id_masjid', $masjid->id)->where('nama_akaun', 'Bank Operasi')->firstOrFail();
        $programAccount = Akaun::query()->where('id_masjid', $masjid->id)->where('nama_akaun', 'Bank Program Komuniti')->firstOrFail();
        $utilities = KategoriBelanja::query()->where('id_masjid', $masjid->id)->where('kod', 'UTIL')->firstOrFail();
        $programmeCategory = KategoriBelanja::query()->where('id_masjid', $masjid->id)->where('kod', 'PROGRAM')->firstOrFail();
        $fund = TabungKhas::query()->where('id_masjid', $masjid->id)->where('nama_tabung', 'Dana Kecemasan')->firstOrFail();
        $program = ProgramMasjid::query()->where('id_masjid', $masjid->id)->where('nama_program', 'Iftar Jamaie')->firstOrFail();

        $creator = User::query()->where('id_masjid', $masjid->id)
            ->whereHas('roles', fn($query) => $query->where('name', 'FinanceOfficer'))
            ->orderBy('id')
            ->firstOrFail();
        $approver = User::query()->whereHas('roles', fn($query) => $query->where('name', 'Manager'))->orderBy('id')->first()
            ?? User::query()->whereHas('roles', fn($query) => $query->where('name', 'Admin'))->orderBy('id')->firstOrFail();

        $approvedVoucher = BaucarBayaran::query()->updateOrCreate(
            ['id_masjid' => $masjid->id, 'no_baucar' => sprintf('BV-%02d-%s-001', $masjid->id, now()->format('Ym'))],
            [
                'tarikh' => now()->subDays(10)->toDateString(),
                'id_akaun' => $bankAccount->id,
                'kaedah' => 'bank',
                'no_rujukan' => 'IBG-' . $masjid->id . '-001',
                'jumlah' => 3600 + ($index * 450),
                'catatan' => 'Pembayaran utiliti dan vendor kebersihan bulanan',
                'status' => 'LULUS',
                'created_by' => $creator->id,
                'dilulus_oleh' => $approver->id,
                'tarikh_lulus' => now()->subDays(9),
            ]
        );

        Belanja::query()->updateOrCreate(
            [
                'id_masjid' => $masjid->id,
                'tarikh' => now()->subDays(10)->toDateString(),
                'id_akaun' => $bankAccount->id,
                'id_kategori_belanja' => $utilities->id,
                'amaun' => 1800 + ($index * 150),
                'penerima' => 'Tenaga Nasional Berhad',
            ],
            [
                'id_tabung_khas' => null,
                'id_program' => null,
                'catatan' => 'Bil utiliti bulanan',
                'bukti_fail' => 'bukti/utiliti-' . $masjid->id . '.pdf',
                'created_by' => $creator->id,
                'status' => 'LULUS',
                'id_baucar' => $approvedVoucher->id,
                'is_deleted' => false,
                'deleted_by' => null,
                'deleted_at' => null,
                'dilulus_oleh' => $approver->id,
                'tarikh_lulus' => now()->subDays(9),
            ]
        );

        $draftVoucher = BaucarBayaran::query()->updateOrCreate(
            ['id_masjid' => $masjid->id, 'no_baucar' => sprintf('BV-%02d-%s-099', $masjid->id, now()->format('Ym'))],
            [
                'tarikh' => now()->subDays(2)->toDateString(),
                'id_akaun' => $programAccount->id,
                'kaedah' => 'tunai',
                'no_rujukan' => null,
                'jumlah' => 2200 + ($index * 200),
                'catatan' => 'Draf baucar untuk program komuniti hujung minggu',
                'status' => 'DRAF',
                'created_by' => $creator->id,
                'dilulus_oleh' => null,
                'tarikh_lulus' => null,
            ]
        );

        Belanja::query()->updateOrCreate(
            [
                'id_masjid' => $masjid->id,
                'tarikh' => now()->subDays(2)->toDateString(),
                'id_akaun' => $programAccount->id,
                'id_kategori_belanja' => $programmeCategory->id,
                'amaun' => 2200 + ($index * 200),
                'penerima' => 'Pembekal Makanan Komuniti',
            ],
            [
                'id_tabung_khas' => $fund->id,
                'id_program' => $program->id,
                'catatan' => 'Belanja draft menunggu semakan akhir',
                'bukti_fail' => null,
                'created_by' => $creator->id,
                'status' => 'DRAF',
                'id_baucar' => $draftVoucher->id,
                'is_deleted' => false,
                'deleted_by' => null,
                'deleted_at' => null,
                'dilulus_oleh' => null,
                'tarikh_lulus' => null,
            ]
        );

        Belanja::query()->updateOrCreate(
            [
                'id_masjid' => $masjid->id,
                'tarikh' => now()->subDays(18)->toDateString(),
                'id_akaun' => $bankAccount->id,
                'id_kategori_belanja' => $programmeCategory->id,
                'amaun' => 750.00,
                'penerima' => 'Duplicate Catering Entry',
            ],
            [
                'id_tabung_khas' => $fund->id,
                'id_program' => $program->id,
                'catatan' => 'Dibuat sebagai kes tepi untuk rekod padam logik',
                'bukti_fail' => null,
                'created_by' => $creator->id,
                'status' => 'DRAF',
                'id_baucar' => null,
                'is_deleted' => true,
                'deleted_by' => $creator->id,
                'deleted_at' => now()->subDays(17),
                'dilulus_oleh' => null,
                'tarikh_lulus' => null,
            ]
        );
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
                'catatan' => 'Pemindahan baki untuk sokongan program komuniti',
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
            ['jenis' => 'CREATE', 'modul' => 'Hasil', 'aksi' => 'rekod kutipan Jumaat', 'butiran' => 'Kutipan mingguan berjaya direkodkan', 'user_id' => $financeOfficer->id, 'created_at' => now()->subHours(18)],
            ['jenis' => 'UPDATE', 'modul' => 'Belanja', 'aksi' => 'semakan draft baucar', 'butiran' => 'Draf baucar komuniti dikemaskini untuk semakan', 'user_id' => $financeOfficer->id, 'created_at' => now()->subHours(9)],
            ['jenis' => 'UPDATE', 'modul' => 'Baucar', 'aksi' => 'lulus pembayaran', 'butiran' => 'Baucar operasi telah diluluskan pengurus', 'user_id' => $manager->id, 'created_at' => now()->subHours(4)],
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
            ['suffix' => '001', 'user' => $financeOfficer, 'channel' => 'email', 'subject' => 'Budget approval sent', 'message' => 'Budget approval message delivered successfully.', 'status' => 'sent', 'error' => null, 'retry' => 0, 'sent_at' => now()->subHours(3)],
            ['suffix' => '002', 'user' => $financeOfficer, 'channel' => 'telegram', 'subject' => 'Failed payout alert', 'message' => 'Telegram delivery failed for payout alert.', 'status' => 'failed', 'error' => 'Telegram chat not reachable', 'retry' => 2, 'sent_at' => null],
            ['suffix' => '003', 'user' => $manager, 'channel' => 'database', 'subject' => 'Draft voucher pending', 'message' => 'A draft voucher still requires managerial action.', 'status' => 'pending', 'error' => null, 'retry' => 0, 'sent_at' => null],
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
