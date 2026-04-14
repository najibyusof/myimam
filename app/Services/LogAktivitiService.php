<?php

namespace App\Services;

use App\Models\LogAktiviti;
use Illuminate\Http\Request;

class LogAktivitiService
{
    /**
     * Known jenis (type) constants used across modules.
     */
    public const JENIS_LOGIN_OK   = 'LOGIN_OK';
    public const JENIS_LOGIN_FAIL = 'LOGIN_FAIL';
    public const JENIS_CREATE     = 'CREATE';
    public const JENIS_UPDATE     = 'UPDATE';
    public const JENIS_DELETE     = 'DELETE';
    public const JENIS_VIEW       = 'VIEW';
    public const JENIS_APPROVE    = 'APPROVE';
    public const JENIS_EXPORT     = 'EXPORT';

    public static function allJenis(): array
    {
        return [
            self::JENIS_LOGIN_OK,
            self::JENIS_LOGIN_FAIL,
            self::JENIS_CREATE,
            self::JENIS_UPDATE,
            self::JENIS_DELETE,
            self::JENIS_VIEW,
            self::JENIS_APPROVE,
            self::JENIS_EXPORT,
        ];
    }

    /**
     * Record an activity log entry.
     *
     * @param  string       $jenis    Action type (use self::JENIS_* constants)
     * @param  string|null  $modul    Module name (e.g. 'Hasil', 'Belanja')
     * @param  string|null  $aksi     Short description of the action
     * @param  array|null   $options  Additional fields: rujukan_id, butiran, data_lama, data_baru
     * @param  Request|null $request  HTTP request (for ip/user_agent capture)
     */
    public function record(
        string $jenis,
        ?string $modul = null,
        ?string $aksi = null,
        array $options = [],
        ?Request $request = null
    ): LogAktiviti {
        $actor = auth()->user();

        return LogAktiviti::query()->create([
            'id_masjid'  => $actor?->id_masjid,
            'id_user'    => $actor?->id,
            'jenis'      => strtoupper($jenis),
            'modul'      => $modul,
            'aksi'       => $aksi,
            'rujukan_id' => $options['rujukan_id'] ?? null,
            'butiran'    => $options['butiran'] ?? null,
            'data_lama'  => $options['data_lama'] ?? null,
            'data_baru'  => $options['data_baru'] ?? null,
            'ip'         => $request?->ip(),
            'user_agent' => $request?->userAgent() ? substr($request->userAgent(), 0, 255) : null,
        ]);
    }
}
