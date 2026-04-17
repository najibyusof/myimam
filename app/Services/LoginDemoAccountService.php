<?php

namespace App\Services;

use App\Models\Masjid;
use App\Models\User;

class LoginDemoAccountService
{
    /**
     * @return array{accounts: array<int, array<string, string>>, copy_payload: string}
     */
    public function forLoginPage(?Masjid $masjid): array
    {
        $roles = ['Superadmin', 'Admin', 'Bendahari', 'AJK', 'Auditor'];
        $accounts = [];

        foreach ($roles as $roleName) {
            $user = User::query()
                ->active()
                ->whereHas('roles', function ($query) use ($roleName): void {
                    $query->where('name', $roleName);
                })
                ->with('masjid:id,nama')
                ->when($roleName === 'Superadmin', function ($query): void {
                    $query->whereNull('id_masjid');
                })
                ->when($roleName !== 'Superadmin' && $masjid, function ($query) use ($masjid): void {
                    $query->where('id_masjid', $masjid->id);
                })
                ->orderBy('id')
                ->first();

            if (! $user) {
                continue;
            }

            $accounts[] = [
                'role' => $roleName,
                'label' => $this->buildLabel($roleName, $user->masjid?->nama),
                'email' => (string) $user->email,
                'password_hint' => __('auth.password'),
            ];
        }

        $copyPayload = collect($accounts)
            ->map(fn(array $account): string => sprintf('%s: %s / %s', $account['label'], $account['email'], $account['password_hint']))
            ->implode("\n");

        return [
            'accounts' => $accounts,
            'copy_payload' => $copyPayload,
        ];
    }

    private function buildLabel(string $roleName, ?string $masjidName): string
    {
        if ($roleName === 'Superadmin') {
            return __('auth.system_admin');
        }

        if (! empty($masjidName)) {
            return sprintf('%s - %s', $masjidName, $roleName);
        }

        return $roleName;
    }
}
