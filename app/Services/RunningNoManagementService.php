<?php

namespace App\Services;

use App\Models\RunningNo;
use Illuminate\Support\Facades\DB;

class RunningNoManagementService
{
    /**
     * Atomically increment the counter and return the formatted transaction number.
     * Creates the counter row for the period if it does not yet exist.
     */
    public function generate(int $idMasjid, string $prefix, int $tahun, int $bulan): string
    {
        $prefix = strtoupper(trim($prefix));

        return DB::transaction(function () use ($idMasjid, $prefix, $tahun, $bulan): string {
            $record = RunningNo::query()
                ->forPeriod($idMasjid, $prefix, $tahun, $bulan)
                ->lockForUpdate()
                ->first();

            if ($record === null) {
                RunningNo::query()->create([
                    'id_masjid' => $idMasjid,
                    'prefix'    => $prefix,
                    'tahun'     => $tahun,
                    'bulan'     => $bulan,
                    'last_no'   => 1,
                ]);

                return $this->format($prefix, $tahun, $bulan, 1);
            }

            $nextNo = $record->last_no + 1;

            RunningNo::query()
                ->forPeriod($idMasjid, $prefix, $tahun, $bulan)
                ->update(['last_no' => $nextNo]);

            return $this->format($prefix, $tahun, $bulan, $nextNo);
        });
    }

    /**
     * Manually override the last_no value (admin correction).
     */
    public function resetCounter(RunningNo $runningNo, int $lastNo): RunningNo
    {
        RunningNo::query()
            ->forPeriod($runningNo->id_masjid, $runningNo->prefix, $runningNo->tahun, $runningNo->bulan)
            ->update(['last_no' => $lastNo]);

        return RunningNo::query()
            ->forPeriod($runningNo->id_masjid, $runningNo->prefix, $runningNo->tahun, $runningNo->bulan)
            ->firstOrFail();
    }

    /**
     * Format a transaction reference number.
     * Example: RMT-2604-001
     */
    public function format(string $prefix, int $tahun, int $bulan, int $no): string
    {
        $yy = substr((string) $tahun, -2);
        $mm = str_pad((string) $bulan, 2, '0', STR_PAD_LEFT);

        return sprintf('%s-%s%s-%03d', strtoupper($prefix), $yy, $mm, $no);
    }
}
