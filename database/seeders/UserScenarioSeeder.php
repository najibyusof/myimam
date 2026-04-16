<?php

namespace Database\Seeders;

use App\Models\Masjid;
use App\Models\NotificationPreference;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class UserScenarioSeeder extends Seeder
{
    public function run(): void
    {
        $masjids = Masjid::query()->orderBy('id')->get()->keyBy('nama');

        $users = [
            ['name' => 'Muhammad bin Ahmad', 'email' => 'muhammad.bin.ahmad@masjid.com', 'role' => 'Superadmin', 'peranan' => 'superadmin', 'aktif' => true, 'masjid' => null, 'verified' => true],
            ['name' => 'Azman bin Ismail', 'email' => 'azman.bin.ismail@masjid.com', 'role' => 'Admin', 'peranan' => 'admin', 'aktif' => true, 'masjid' => null, 'verified' => true],

            ['name' => 'Rahman bin Zulkifli', 'email' => 'rahman.bin.zulkifli@masjid.com', 'role' => 'Manager', 'peranan' => 'admin', 'aktif' => true, 'masjid' => 'Masjid Al-Hidayah Putrajaya', 'verified' => true],
            ['name' => 'Hafiz bin Faizal', 'email' => 'hafiz.bin.faizal@masjid.com', 'role' => 'Manager', 'peranan' => 'admin', 'aktif' => true, 'masjid' => 'Masjid An-Nur Cyberjaya', 'verified' => true],

            ['name' => 'Ali bin Hakim', 'email' => 'ali.bin.hakim@masjid.com', 'role' => 'FinanceOfficer', 'peranan' => 'staff', 'aktif' => true, 'masjid' => 'Masjid Al-Hidayah Putrajaya', 'verified' => true],
            ['name' => 'Siti binti Ahmad', 'email' => 'siti.binti.ahmad@masjid.com', 'role' => 'FinanceOfficer', 'peranan' => 'staff', 'aktif' => true, 'masjid' => 'Masjid Al-Hidayah Putrajaya', 'verified' => true],
            ['name' => 'Nurul binti Ismail', 'email' => 'nurul.binti.ismail@masjid.com', 'role' => 'FinanceOfficer', 'peranan' => 'staff', 'aktif' => true, 'masjid' => 'Masjid An-Nur Cyberjaya', 'verified' => true],
            ['name' => 'Fatimah binti Rahman', 'email' => 'fatimah.binti.rahman@masjid.com', 'role' => 'FinanceOfficer', 'peranan' => 'staff', 'aktif' => false, 'masjid' => 'Masjid Al-Falah Shah Alam', 'verified' => true],

            ['name' => 'Azman bin Ali', 'email' => 'azman.bin.ali@masjid.com', 'role' => 'Auditor', 'peranan' => 'staff', 'aktif' => true, 'masjid' => null, 'verified' => true],
            ['name' => 'Aisyah binti Muhammad', 'email' => 'aisyah.binti.muhammad@masjid.com', 'role' => 'Auditor', 'peranan' => 'staff', 'aktif' => true, 'masjid' => null, 'verified' => true],

            ['name' => 'Hakim bin Azman', 'email' => 'hakim.bin.azman@masjid.com', 'role' => 'MasjidOfficer', 'peranan' => 'staff', 'aktif' => true, 'masjid' => 'Masjid Al-Hidayah Putrajaya', 'verified' => true],
            ['name' => 'Liyana binti Zulkifli', 'email' => 'liyana.binti.zulkifli@masjid.com', 'role' => 'MasjidOfficer', 'peranan' => 'staff', 'aktif' => true, 'masjid' => 'Masjid An-Nur Cyberjaya', 'verified' => true],
            ['name' => 'Rohana binti Ismail', 'email' => 'rohana.binti.ismail@masjid.com', 'role' => 'MasjidOfficer', 'peranan' => 'staff', 'aktif' => true, 'masjid' => 'Masjid Al-Falah Shah Alam', 'verified' => false],

            ['name' => 'Maznah binti Hakim', 'email' => 'maznah.binti.hakim@masjid.com', 'role' => 'User', 'peranan' => 'staff', 'aktif' => true, 'masjid' => 'Masjid Al-Hidayah Putrajaya', 'verified' => true],
            ['name' => 'Hajar binti Faizal', 'email' => 'hajar.binti.faizal@masjid.com', 'role' => 'User', 'peranan' => 'staff', 'aktif' => true, 'masjid' => 'Masjid An-Nur Cyberjaya', 'verified' => true],
            ['name' => 'Salmah binti Rahman', 'email' => 'salmah.binti.rahman@masjid.com', 'role' => 'User', 'peranan' => 'staff', 'aktif' => true, 'masjid' => 'Masjid Al-Falah Shah Alam', 'verified' => false],

            ['name' => 'Zainab binti Ahmad', 'email' => 'zainab.binti.ahmad@masjid.com', 'role' => null, 'peranan' => 'staff', 'aktif' => true, 'masjid' => 'Masjid Al-Hidayah Putrajaya', 'verified' => true],
            ['name' => 'Faizal bin Muhammad', 'email' => 'faizal.bin.muhammad@masjid.com', 'role' => 'User', 'peranan' => 'staff', 'aktif' => false, 'masjid' => 'Masjid An-Nur Cyberjaya', 'verified' => true],
        ];

        foreach ($users as $userData) {
            $masjidId = $userData['masjid'] ? optional($masjids->get($userData['masjid']))->id : null;

            $user = User::query()->updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => 'password',
                    'peranan' => $userData['peranan'],
                    'aktif' => $userData['aktif'],
                    'id_masjid' => $masjidId,
                    'email_verified_at' => $userData['verified'] ? now() : null,
                ]
            );

            if ($userData['role']) {
                $role = Role::findByName($userData['role'], 'web');
                $user->syncRoles([$role]);
            } else {
                $user->syncRoles([]);
            }

            NotificationPreference::query()->updateOrCreate(
                ['id_user' => $user->id],
                [
                    'email_notifications' => true,
                    'sms_notifications' => false,
                    'push_notifications' => $user->aktif,
                    'telegram_notifications' => ($userData['role'] ?? null) === 'FinanceOfficer',
                    'telegram_chat_id' => ($userData['role'] ?? null) === 'FinanceOfficer' ? (string) (100000 + $user->id) : null,
                    'fcm_token' => $user->aktif ? 'fcm-token-' . $user->id : null,
                    'notification_types' => in_array($userData['role'], ['Admin', 'Manager', 'FinanceOfficer', 'Auditor'], true)
                        ? ['finance', 'masjid', 'user', 'system']
                        : ['finance', 'system'],
                ]
            );
        }
    }
}
