<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\HasMalaySampleData;
use App\Models\Masjid;
use App\Models\NotificationPreference;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class UserSeeder extends Seeder
{
    use HasMalaySampleData;

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->ensureRolesExist();
        fake()->seed(20260414);

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

        // Sync Superadmin role (canonical name is 'Superadmin' — level 1)
        $superAdmin->syncRoles(['Superadmin']);

        // Grant all permissions directly so superadmin is never blocked by
        // permission checks even before RolesAndPermissionsSeeder runs.
        $allPermissions = Permission::all();
        $superAdmin->syncPermissions($allPermissions);

        $this->upsertNotificationPreference($superAdmin);

        $tenantBlueprint = [
            [
                'masjid_code' => 'alfalah',
                'label' => 'Al-Falah',
                'users' => [
                    ['name' => 'Ahmad bin Ismail', 'role' => 'Admin', 'peranan' => 'admin'],
                    ['name' => 'Muhammad bin Rahman', 'role' => 'Bendahari', 'peranan' => 'admin'],
                    ['name' => 'Azman bin Zulkifli', 'role' => 'Bendahari', 'peranan' => 'admin'],
                    ['name' => 'Siti binti Ahmad', 'role' => 'AJK', 'peranan' => 'staff'],
                    ['name' => 'Nurul binti Ali', 'role' => 'AJK', 'peranan' => 'staff'],
                    ['name' => 'Hafiz bin Hakim', 'role' => 'Auditor', 'peranan' => 'staff'],
                ],
            ],
            [
                'masjid_code' => 'arrahman',
                'label' => 'Ar-Rahman',
                'users' => [
                    ['name' => 'Faizal bin Azman', 'role' => 'Admin', 'peranan' => 'admin'],
                    ['name' => 'Ali bin Hafiz', 'role' => 'Bendahari', 'peranan' => 'admin'],
                    ['name' => 'Aisyah binti Rahman', 'role' => 'AJK', 'peranan' => 'staff'],
                    ['name' => 'Fatimah binti Ismail', 'role' => 'AJK', 'peranan' => 'staff'],
                    ['name' => 'Liyana binti Ahmad', 'role' => 'AJK', 'peranan' => 'staff'],
                    ['name' => 'Hakim bin Muhammad', 'role' => 'Auditor', 'peranan' => 'staff'],
                ],
            ],
            [
                'masjid_code' => 'annur',
                'label' => 'An-Nur',
                'users' => [
                    ['name' => 'Zulkifli bin Ali', 'role' => 'Admin', 'peranan' => 'admin'],
                    ['name' => 'Rahman bin Ismail', 'role' => 'Bendahari', 'peranan' => 'admin'],
                    ['name' => 'Maznah binti Hakim', 'role' => 'AJK', 'peranan' => 'staff'],
                    ['name' => 'Salmah binti Faizal', 'role' => 'AJK', 'peranan' => 'staff'],
                    ['name' => 'Rohana binti Ahmad', 'role' => 'Auditor', 'peranan' => 'staff'],
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
                $generatedName = $userData['name'] ?? $this->generateMalayName();
                $generatedEmail = $this->resolveTenantEmail($masjid, $tenant['masjid_code'], $generatedName);

                $user = $masjid->users()->updateOrCreate(
                    ['email' => $generatedEmail],
                    [
                        'name' => $generatedName,
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
        // Ensure Superadmin role exists at level 1 (system-reserved, immutable).
        // Uses App\Models\Role so level + masjid_id fields are populated correctly.
        Role::firstOrCreate(
            ['name' => 'Superadmin', 'guard_name' => 'web'],
            ['level' => 1, 'masjid_id' => null]
        );

        // Ensure Admin role exists at level 2 (tenant administrator).
        Role::firstOrCreate(
            ['name' => 'Admin', 'guard_name' => 'web'],
            ['level' => 2, 'masjid_id' => null]
        );

        // Ensure standard level-3 user roles exist.
        foreach (['Bendahari', 'AJK', 'Auditor'] as $roleName) {
            Role::firstOrCreate(
                ['name' => $roleName, 'guard_name' => 'web'],
                ['level' => 3, 'masjid_id' => null]
            );
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

    private function resolveTenantEmail(Masjid $masjid, string $masjidCode, string $name): string
    {
        $existingEmail = $masjid->users()
            ->where('name', $name)
            ->value('email');

        if ($existingEmail) {
            return $existingEmail;
        }

        $base = Str::slug($name, '.') . '.' . $masjidCode;
        $candidate = $base . '@masjid.com';
        $counter = 2;

        while (User::query()->where('email', $candidate)->exists()) {
            $candidate = $base . $counter . '@masjid.com';
            $counter++;
        }

        return $candidate;
    }
}
