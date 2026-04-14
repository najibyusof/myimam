<?php

namespace Database\Seeders;

use App\Models\Masjid;
use App\Models\NotificationPreference;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $this->ensureRolesExist();
        $faker = fake('ms_MY');
        $faker->seed(20260414);

        $superAdmin = User::query()->updateOrCreate(
            ['email' => 'superadmin@imam.com'],
            [
                'name' => 'Super Admin Sistem',
                'id_masjid' => null,
                'password' => Hash::make('password'),
                'peranan' => 'superadmin',
                'aktif' => true,
                'email_verified_at' => now(),
            ]
        );
        $superAdmin->syncRoles(['Admin']);
        $this->upsertNotificationPreference($superAdmin);

        $tenantBlueprint = [
            [
                'masjid_code' => 'alfalah',
                'domain' => 'alfalah.com',
                'label' => 'Al-Falah',
                'users' => [
                    ['name' => 'Admin Al-Falah', 'email' => 'admin@alfalah.com', 'role' => 'Admin', 'peranan' => 'admin'],
                    ['name' => 'Bendahari Utama Al-Falah', 'email' => 'bendahari@alfalah.com', 'role' => 'Bendahari', 'peranan' => 'admin'],
                    ['name' => 'Bendahari Kedua Al-Falah', 'email' => 'bendahari2@alfalah.com', 'role' => 'Bendahari', 'peranan' => 'admin'],
                    ['name' => null, 'email' => 'ajk.kewangan@alfalah.com', 'role' => 'AJK', 'peranan' => 'staff'],
                    ['name' => null, 'email' => 'ajk.program@alfalah.com', 'role' => 'AJK', 'peranan' => 'staff'],
                    ['name' => null, 'email' => 'auditor@alfalah.com', 'role' => 'Auditor', 'peranan' => 'staff'],
                ],
            ],
            [
                'masjid_code' => 'arrahman',
                'domain' => 'rahman.com',
                'label' => 'Ar-Rahman',
                'users' => [
                    ['name' => 'Admin Ar-Rahman', 'email' => 'admin@rahman.com', 'role' => 'Admin', 'peranan' => 'admin'],
                    ['name' => 'Bendahari Ar-Rahman', 'email' => 'bendahari@rahman.com', 'role' => 'Bendahari', 'peranan' => 'admin'],
                    ['name' => null, 'email' => 'ajk.operasi@rahman.com', 'role' => 'AJK', 'peranan' => 'staff'],
                    ['name' => null, 'email' => 'ajk.komuniti@rahman.com', 'role' => 'AJK', 'peranan' => 'staff'],
                    ['name' => null, 'email' => 'ajk.dakwah@rahman.com', 'role' => 'AJK', 'peranan' => 'staff'],
                    ['name' => null, 'email' => 'auditor@rahman.com', 'role' => 'Auditor', 'peranan' => 'staff'],
                ],
            ],
            [
                'masjid_code' => 'annur',
                'domain' => 'annur.com',
                'label' => 'An-Nur',
                'users' => [
                    ['name' => 'Admin An-Nur', 'email' => 'admin@annur.com', 'role' => 'Admin', 'peranan' => 'admin'],
                    ['name' => 'Bendahari An-Nur', 'email' => 'bendahari@annur.com', 'role' => 'Bendahari', 'peranan' => 'admin'],
                    ['name' => null, 'email' => 'ajk.data@annur.com', 'role' => 'AJK', 'peranan' => 'staff'],
                    ['name' => null, 'email' => 'ajk.remaja@annur.com', 'role' => 'AJK', 'peranan' => 'staff'],
                    ['name' => null, 'email' => 'auditor@annur.com', 'role' => 'Auditor', 'peranan' => 'staff'],
                ],
            ],
        ];

        foreach ($tenantBlueprint as $tenant) {
            $masjid = Masjid::query()->where('code', $tenant['masjid_code'])->first();

            if (! $masjid) {
                continue;
            }

            // Backfill creator relationship now that superadmin exists.
            if ($masjid->created_by === null) {
                $masjid->update(['created_by' => $superAdmin->id]);
            }

            foreach ($tenant['users'] as $userData) {
                $user = $masjid->users()->updateOrCreate(
                    ['email' => $userData['email']],
                    [
                        'name' => $userData['name'] ?? $faker->name(),
                        'password' => Hash::make('password'),
                        'peranan' => $userData['peranan'],
                        'aktif' => true,
                        'email_verified_at' => now(),
                    ]
                );

                $user->syncRoles([$userData['role']]);
                $this->upsertNotificationPreference($user);
            }
        }
    }

    private function ensureRolesExist(): void
    {
        foreach (['Admin', 'Bendahari', 'AJK', 'Auditor'] as $roleName) {
            Role::findOrCreate($roleName, 'web');
        }
    }

    private function upsertNotificationPreference(User $user): void
    {
        NotificationPreference::query()->updateOrCreate(
            ['id_user' => $user->id],
            [
                'email_notifications' => true,
                'sms_notifications' => false,
                'push_notifications' => true,
                'telegram_notifications' => false,
                'telegram_chat_id' => null,
                'fcm_token' => null,
                'notification_types' => ['finance', 'system'],
            ]
        );
    }
}
