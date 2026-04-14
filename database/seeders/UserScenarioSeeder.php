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
            ['name' => 'System Admin', 'email' => 'admin@example.com', 'role' => 'Admin', 'peranan' => 'superadmin', 'aktif' => true, 'masjid' => null, 'verified' => true],
            ['name' => 'Operations Admin', 'email' => 'ops.admin@example.com', 'role' => 'Admin', 'peranan' => 'admin', 'aktif' => true, 'masjid' => null, 'verified' => true],

            ['name' => 'Operations Manager', 'email' => 'manager@example.com', 'role' => 'Manager', 'peranan' => 'admin', 'aktif' => true, 'masjid' => 'Masjid Al-Hidayah Putrajaya', 'verified' => true],
            ['name' => 'Programme Manager', 'email' => 'program.manager@example.com', 'role' => 'Manager', 'peranan' => 'admin', 'aktif' => true, 'masjid' => 'Masjid An-Nur Cyberjaya', 'verified' => true],

            ['name' => 'Procurement Officer', 'email' => 'procurement.officer@finance.gov.my', 'role' => 'FinanceOfficer', 'peranan' => 'staff', 'aktif' => true, 'masjid' => 'Masjid Al-Hidayah Putrajaya', 'verified' => true],
            ['name' => 'Pegawai Akaun', 'email' => 'pegawai.akaun@finance.gov.my', 'role' => 'FinanceOfficer', 'peranan' => 'staff', 'aktif' => true, 'masjid' => 'Masjid Al-Hidayah Putrajaya', 'verified' => true],
            ['name' => 'Bank Officer', 'email' => 'bank.officer@finance.gov.my', 'role' => 'FinanceOfficer', 'peranan' => 'staff', 'aktif' => true, 'masjid' => 'Masjid An-Nur Cyberjaya', 'verified' => true],
            ['name' => 'Finance Clerk', 'email' => 'finance.clerk@example.com', 'role' => 'FinanceOfficer', 'peranan' => 'staff', 'aktif' => false, 'masjid' => 'Masjid Al-Falah Shah Alam', 'verified' => true],

            ['name' => 'Internal Auditor', 'email' => 'audit.internal@example.com', 'role' => 'Auditor', 'peranan' => 'staff', 'aktif' => true, 'masjid' => null, 'verified' => true],
            ['name' => 'External Auditor', 'email' => 'audit.external@example.com', 'role' => 'Auditor', 'peranan' => 'staff', 'aktif' => true, 'masjid' => null, 'verified' => true],

            ['name' => 'Imam Hidayah', 'email' => 'imam.hidayah@example.com', 'role' => 'MasjidOfficer', 'peranan' => 'staff', 'aktif' => true, 'masjid' => 'Masjid Al-Hidayah Putrajaya', 'verified' => true],
            ['name' => 'Bilal An-Nur', 'email' => 'bilal.annur@example.com', 'role' => 'MasjidOfficer', 'peranan' => 'staff', 'aktif' => true, 'masjid' => 'Masjid An-Nur Cyberjaya', 'verified' => true],
            ['name' => 'Setiausaha Falah', 'email' => 'setiausaha.falah@example.com', 'role' => 'MasjidOfficer', 'peranan' => 'staff', 'aktif' => true, 'masjid' => 'Masjid Al-Falah Shah Alam', 'verified' => false],

            ['name' => 'Committee Member One', 'email' => 'committee.one@example.com', 'role' => 'User', 'peranan' => 'staff', 'aktif' => true, 'masjid' => 'Masjid Al-Hidayah Putrajaya', 'verified' => true],
            ['name' => 'Committee Member Two', 'email' => 'committee.two@example.com', 'role' => 'User', 'peranan' => 'staff', 'aktif' => true, 'masjid' => 'Masjid An-Nur Cyberjaya', 'verified' => true],
            ['name' => 'Volunteer Pending Verification', 'email' => 'volunteer.pending@example.com', 'role' => 'User', 'peranan' => 'staff', 'aktif' => true, 'masjid' => 'Masjid Al-Falah Shah Alam', 'verified' => false],

            ['name' => 'No Role Edge Case', 'email' => 'edge.norole@example.com', 'role' => null, 'peranan' => 'staff', 'aktif' => true, 'masjid' => 'Masjid Al-Hidayah Putrajaya', 'verified' => true],
            ['name' => 'Dormant Inactive User', 'email' => 'edge.inactive@example.com', 'role' => 'User', 'peranan' => 'staff', 'aktif' => false, 'masjid' => 'Masjid An-Nur Cyberjaya', 'verified' => true],
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
                    'telegram_notifications' => str_contains($user->email, 'finance.gov.my'),
                    'telegram_chat_id' => str_contains($user->email, 'finance.gov.my') ? (string) (100000 + $user->id) : null,
                    'fcm_token' => $user->aktif ? 'fcm-token-'.$user->id : null,
                    'notification_types' => in_array($userData['role'], ['Admin', 'Manager', 'FinanceOfficer', 'Auditor'], true)
                        ? ['finance', 'masjid', 'user', 'system']
                        : ['finance', 'system'],
                ]
            );
        }
    }
}
