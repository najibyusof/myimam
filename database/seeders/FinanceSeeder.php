<?php

namespace Database\Seeders;

use App\Models\Akaun;
use App\Models\BaucarBayaran;
use App\Models\Belanja;
use App\Models\Hasil;
use App\Models\KategoriBelanja;
use App\Models\Masjid;
use App\Models\Notification;
use App\Models\PindahanAkaun;
use App\Models\ProgramMasjid;
use App\Models\RunningNo;
use App\Models\SumberHasil;
use App\Models\TabungKhas;
use App\Models\User;
use Illuminate\Database\Seeder;

class FinanceSeeder extends Seeder
{
    public function run(): void
    {
        $faker = fake('ms_MY');
        $faker->seed(20260414);

        $masjids = Masjid::query()
            ->whereIn('code', ['alfalah', 'arrahman', 'annur'])
            ->orderBy('id')
            ->get();

        foreach ($masjids as $masjid) {
            $financeTeam = $this->resolveUsers($masjid);
            if (! $financeTeam) {
                continue;
            }

            [$admin, $creator] = $financeTeam;

            $akaun = $this->seedAkaun($masjid, $faker);
            $sources = $this->seedSumberHasil($masjid);
            $categories = $this->seedKategoriBelanja($masjid);
            $funds = $this->seedTabungKhas($masjid);
            $programs = $this->seedProgram($masjid);

            $hasilCount = $this->seedHasil($masjid, $creator, $akaun, $sources, $funds, $programs, $faker);
            $vouchers = $this->seedVouchers($masjid, $creator, $admin, $akaun, $faker);
            $belanjaCount = $this->seedBelanja($masjid, $creator, $admin, $akaun, $categories, $funds, $programs, $vouchers, $faker);
            $this->seedPindahanAkaun($masjid, $creator, $akaun, $faker);
            $this->seedRunningNo($masjid, $hasilCount, $belanjaCount);
            $this->seedNotifications($masjid, $creator, $admin);
        }
    }

    private function resolveUsers(Masjid $masjid): ?array
    {
        $admin = $masjid->users()
            ->whereHas('roles', fn ($q) => $q->where('name', 'Admin'))
            ->orderBy('id')
            ->first();

        $creator = $masjid->users()
            ->whereHas('roles', fn ($q) => $q->where('name', 'Bendahari'))
            ->orderBy('id')
            ->first() ?? $admin;

        if (! $admin || ! $creator) {
            return null;
        }

        return [$admin, $creator];
    }

    private function seedAkaun(Masjid $masjid, $faker): array
    {
        $cash = $masjid->akaun()->updateOrCreate(
            ['nama_akaun' => 'Tunai Utama'],
            [
                'jenis' => 'tunai',
                'no_akaun' => null,
                'nama_bank' => null,
                'status_aktif' => true,
            ]
        );

        $bank = $masjid->akaun()->updateOrCreate(
            ['nama_akaun' => 'Bank Operasi'],
            [
                'jenis' => 'bank',
                'no_akaun' => (string) $faker->numerify('12##########'),
                'nama_bank' => $faker->randomElement(['Maybank Islamic', 'Bank Islam', 'CIMB Islamic']),
                'status_aktif' => true,
            ]
        );

        return ['cash' => $cash, 'bank' => $bank];
    }

    private function seedSumberHasil(Masjid $masjid): array
    {
        return [
            'jumaat' => $masjid->sumberHasil()->updateOrCreate(
                ['kod' => 'DERMA-JMT'],
                ['nama_sumber' => 'Derma Jumaat', 'jenis' => 'derma', 'aktif' => true]
            ),
            'online' => $masjid->sumberHasil()->updateOrCreate(
                ['kod' => 'ONLINE'],
                ['nama_sumber' => 'Sumbangan Online', 'jenis' => 'online', 'aktif' => true]
            ),
            'dewan' => $masjid->sumberHasil()->updateOrCreate(
                ['kod' => 'SEWA'],
                ['nama_sumber' => 'Sewaan Dewan', 'jenis' => 'sewaan', 'aktif' => true]
            ),
        ];
    }

    private function seedKategoriBelanja(Masjid $masjid): array
    {
        return [
            'utiliti' => $masjid->kategoriBelanja()->updateOrCreate(
                ['kod' => 'UTIL'],
                ['nama_kategori' => 'Utiliti', 'aktif' => true]
            ),
            'selenggara' => $masjid->kategoriBelanja()->updateOrCreate(
                ['kod' => 'SELENGGARA'],
                ['nama_kategori' => 'Penyelenggaraan', 'aktif' => true]
            ),
            'gaji' => $masjid->kategoriBelanja()->updateOrCreate(
                ['kod' => 'GAJI'],
                ['nama_kategori' => 'Gaji', 'aktif' => true]
            ),
            'program' => $masjid->kategoriBelanja()->updateOrCreate(
                ['kod' => 'PROGRAM'],
                ['nama_kategori' => 'Program', 'aktif' => true]
            ),
        ];
    }

    private function seedTabungKhas(Masjid $masjid): array
    {
        return [
            'pembangunan' => $masjid->tabungKhas()->updateOrCreate(
                ['nama_tabung' => 'Tabung Pembangunan'],
                ['aktif' => true]
            ),
            'kebajikan' => $masjid->tabungKhas()->updateOrCreate(
                ['nama_tabung' => 'Tabung Kebajikan'],
                ['aktif' => true]
            ),
        ];
    }

    private function seedProgram(Masjid $masjid): array
    {
        return [
            'kuliah' => $masjid->programMasjid()->updateOrCreate(
                ['nama_program' => 'Kuliah Mingguan'],
                ['aktif' => true]
            ),
            'ramadan' => $masjid->programMasjid()->updateOrCreate(
                ['nama_program' => 'Program Ramadan'],
                ['aktif' => true]
            ),
        ];
    }

    private function seedHasil(
        Masjid $masjid,
        User $creator,
        array $akaun,
        array $sources,
        array $funds,
        array $programs,
        $faker
    ): int {
        $records = 0;

        $friday = now()->startOfWeek()->addDays(4);
        for ($i = 0; $i < 12; $i++) {
            $date = $friday->copy()->subWeeks($i);

            if ($date->lt(now()->subMonths(3)->startOfDay())) {
                continue;
            }

            $cashAmount = (float) $faker->numberBetween(3200, 9600);
            $onlineAmount = (float) $faker->numberBetween(400, 2000);

            $masjid->hasil()->updateOrCreate(
                ['no_resit' => sprintf('HSL-%s-JMT-%s', strtoupper($masjid->code), $date->format('Ymd'))],
                [
                    'tarikh' => $date->toDateString(),
                    'id_akaun' => $akaun['cash']->id,
                    'id_sumber_hasil' => $sources['jumaat']->id,
                    'amaun_tunai' => $cashAmount,
                    'amaun_online' => $onlineAmount,
                    'jumlah' => $cashAmount + $onlineAmount,
                    'id_tabung_khas' => $funds['kebajikan']->id,
                    'id_program' => null,
                    'jenis_jumaat' => 'biasa',
                    'catatan' => 'Kutipan Jumaat mingguan.',
                    'created_by' => $creator->id,
                ]
            );

            $records++;
        }

        for ($i = 1; $i <= 12; $i++) {
            $date = now()->subDays($faker->numberBetween(1, 90))->startOfDay();
            $source = $i % 3 === 0 ? $sources['dewan'] : $sources['online'];
            $isOnline = $source->kod === 'ONLINE';
            $amount = (float) $faker->numberBetween(200, 4500);

            $masjid->hasil()->updateOrCreate(
                ['no_resit' => sprintf('HSL-%s-OTR-%02d', strtoupper($masjid->code), $i)],
                [
                    'tarikh' => $date->toDateString(),
                    'id_akaun' => $isOnline ? $akaun['bank']->id : $akaun['cash']->id,
                    'id_sumber_hasil' => $source->id,
                    'amaun_tunai' => $isOnline ? 0 : $amount,
                    'amaun_online' => $isOnline ? $amount : 0,
                    'jumlah' => $amount,
                    'id_tabung_khas' => $funds['pembangunan']->id,
                    'id_program' => $i % 4 === 0 ? $programs['ramadan']->id : null,
                    'jenis_jumaat' => null,
                    'catatan' => $isOnline ? 'Sumbangan online portal.' : 'Sewaan dewan komuniti.',
                    'created_by' => $creator->id,
                ]
            );

            $records++;
        }

        return $records;
    }

    private function seedVouchers(Masjid $masjid, User $creator, User $approver, array $akaun, $faker): array
    {
        $vouchers = [];

        for ($i = 1; $i <= 6; $i++) {
            $status = $i <= 3 ? 'DRAF' : 'LULUS';
            $date = now()->subDays($faker->numberBetween(1, 60));

            $voucher = $masjid->baucarBayaran()->updateOrCreate(
                ['no_baucar' => sprintf('BV-%s-%s-%03d', strtoupper($masjid->code), now()->format('Ym'), $i)],
                [
                    'tarikh' => $date->toDateString(),
                    'id_akaun' => $i % 2 === 0 ? $akaun['bank']->id : $akaun['cash']->id,
                    'kaedah' => $i % 2 === 0 ? 'bank' : 'tunai',
                    'no_rujukan' => $i % 2 === 0 ? strtoupper($faker->bothify('IBG-######')) : null,
                    'jumlah' => 0,
                    'catatan' => 'Baucar bayaran untuk operasi masjid.',
                    'status' => $status,
                    'created_by' => $creator->id,
                    'dilulus_oleh' => $status === 'LULUS' ? $approver->id : null,
                    'tarikh_lulus' => $status === 'LULUS' ? $date->copy()->addDay() : null,
                ]
            );

            $vouchers[] = $voucher;
        }

        return $vouchers;
    }

    private function seedBelanja(
        Masjid $masjid,
        User $creator,
        User $approver,
        array $akaun,
        array $categories,
        array $funds,
        array $programs,
        array $vouchers,
        $faker
    ): int {
        $count = 20;
        $categoryList = array_values($categories);
        $voucherTotals = [];

        for ($i = 1; $i <= $count; $i++) {
            $voucher = $vouchers[($i - 1) % count($vouchers)];
            $status = $voucher->status;
            $amount = (float) $faker->numberBetween(120, 3000);
            $date = now()->subDays($faker->numberBetween(1, 90));
            $category = $categoryList[$i % count($categoryList)];

            $belanja = $masjid->belanja()->updateOrCreate(
                [
                    'tarikh' => $date->toDateString(),
                    'id_akaun' => $i % 2 === 0 ? $akaun['bank']->id : $akaun['cash']->id,
                    'id_kategori_belanja' => $category->id,
                    'amaun' => $amount,
                    'penerima' => $faker->company(),
                ],
                [
                    'id_tabung_khas' => $i % 2 === 0 ? $funds['pembangunan']->id : $funds['kebajikan']->id,
                    'id_program' => $category->kod === 'PROGRAM' ? $programs['kuliah']->id : null,
                    'catatan' => 'Perbelanjaan operasi dan aktiviti masjid.',
                    'bukti_fail' => null,
                    'created_by' => $creator->id,
                    'status' => $status,
                    'id_baucar' => $voucher->id,
                    'is_deleted' => false,
                    'deleted_by' => null,
                    'deleted_at' => null,
                    'dilulus_oleh' => $status === 'LULUS' ? $approver->id : null,
                    'tarikh_lulus' => $status === 'LULUS' ? $date->copy()->addDay() : null,
                ]
            );

            $voucherTotals[$voucher->id] = ($voucherTotals[$voucher->id] ?? 0) + (float) $belanja->amaun;
        }

        foreach ($vouchers as $voucher) {
            $voucher->update([
                'jumlah' => round((float) ($voucherTotals[$voucher->id] ?? 0), 2),
            ]);
        }

        return $count;
    }

    private function seedPindahanAkaun(Masjid $masjid, User $creator, array $akaun, $faker): void
    {
        $count = $faker->numberBetween(3, 5);

        for ($i = 1; $i <= $count; $i++) {
            $from = $i % 2 === 0 ? $akaun['bank'] : $akaun['cash'];
            $to = $i % 2 === 0 ? $akaun['cash'] : $akaun['bank'];
            $date = now()->subDays($faker->numberBetween(1, 60));

            $masjid->pindahanAkaun()->updateOrCreate(
                [
                    'tarikh' => $date->toDateString(),
                    'dari_akaun_id' => $from->id,
                    'ke_akaun_id' => $to->id,
                ],
                [
                    'amaun' => (float) $faker->numberBetween(500, 5000),
                    'catatan' => 'Pindahan dalaman untuk imbangan tunai dan bank.',
                    'created_by' => $creator->id,
                ]
            );
        }
    }

    private function seedRunningNo(Masjid $masjid, int $hasilCount, int $belanjaCount): void
    {
        $month = (int) now()->format('n');
        $year = (int) now()->format('Y');

        $masjid->runningNo()->updateOrCreate(
            ['prefix' => 'RESIT', 'tahun' => $year, 'bulan' => $month],
            ['last_no' => max($hasilCount, 30)]
        );

        $masjid->runningNo()->updateOrCreate(
            ['prefix' => 'BAUCAR', 'tahun' => $year, 'bulan' => $month],
            ['last_no' => max((int) ceil($belanjaCount / 2), 10)]
        );

        $masjid->runningNo()->updateOrCreate(
            ['prefix' => 'TRF', 'tahun' => $year, 'bulan' => $month],
            ['last_no' => 5]
        );
    }

    private function seedNotifications(Masjid $masjid, User $financeUser, User $admin): void
    {
        Notification::query()->updateOrCreate(
            [
                'notifiable_type' => User::class,
                'notifiable_id' => $financeUser->id,
                'type' => 'finance.new-income',
            ],
            [
                'data' => [
                    'title' => 'Rekod hasil baru',
                    'message' => 'Kutipan Jumaat baharu telah direkodkan untuk '.$masjid->nama.'.',
                    'masjid_id' => $masjid->id,
                ],
                'read_at' => null,
            ]
        );

        Notification::query()->updateOrCreate(
            [
                'notifiable_type' => User::class,
                'notifiable_id' => $admin->id,
                'type' => 'finance.expense-approved',
            ],
            [
                'data' => [
                    'title' => 'Belanja memerlukan semakan',
                    'message' => 'Beberapa baucar belanja telah disediakan dan menunggu tindakan.',
                    'masjid_id' => $masjid->id,
                ],
                'read_at' => now()->subHour(),
            ]
        );
    }
}
