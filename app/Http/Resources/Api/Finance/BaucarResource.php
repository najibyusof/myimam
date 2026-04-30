<?php

namespace App\Http\Resources\Api\Finance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class BaucarResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var \App\Models\Belanja $this */
        $baucarNo = $this->resolveBaucarNo();

        return [
            'id'        => $this->id,
            'baucar_no' => $baucarNo,
            'tarikh'    => $this->tarikh?->toDateString(),
            'akaun'     => $this->whenLoaded('akaun', fn() => [
                'id'   => $this->akaun->id,
                'nama' => $this->akaun->nama_akaun,
            ]),
            'kategori' => $this->whenLoaded('kategoriBelanja', fn() => [
                'id'   => $this->kategoriBelanja->id,
                'nama' => $this->kategoriBelanja->nama_kategori,
            ]),
            'penerima'      => $this->penerima,
            'catatan'       => $this->catatan,
            'amaun'         => (float) $this->amaun,
            'status'        => $this->resolveBaucarStatus(),
            'approval_step' => (int) $this->approval_step,
            'is_locked'     => (bool) $this->is_baucar_locked,

            // Approval chain
            'bendahari' => $this->when($this->approval_step >= 1, fn() => [
                'nama'      => $this->whenLoaded('bendahariLulusOleh', fn() => $this->bendahariLulusOleh?->name),
                'lulus_pada' => $this->bendahari_lulus_pada?->toIso8601String(),
                'signature'  => $this->bendahari_signature,
            ]),
            'pengerusi' => $this->when($this->approval_step >= 2, fn() => [
                'nama'      => $this->whenLoaded('pengerusiLulusOleh', fn() => $this->pengerusiLulusOleh?->name),
                'lulus_pada' => $this->pengerusi_lulus_pada?->toIso8601String(),
                'signature'  => $this->pengerusi_signature,
            ]),

            // Rejection info (when present)
            'tolakan' => $this->when(!empty($this->catatan_tolak), fn() => [
                'catatan'      => $this->catatan_tolak,
                'tarikh'       => $this->tarikh_tolak?->toIso8601String(),
                'ditolak_oleh' => $this->whenLoaded('ditolakOleh', fn() => $this->ditolakOleh?->name),
            ]),

            // Metadata
            'id_masjid'  => $this->id_masjid,
            'created_by' => $this->whenLoaded('createdBy', fn() => $this->createdBy?->name),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),

            // Links (HATEOAS-lite for mobile clients)
            'links' => [
                'show' => route('api.finance.baucar.show', ['id' => $this->id]),
                'pdf'  => route('baucar.pdf', ['belanja_id' => $this->id]),
            ],
        ];
    }

    /**
     * Derive baucar number without side-effects (read-only; does not persist).
     * The controller uses the full mutating version when needed.
     */
    private function resolveBaucarNo(): string
    {
        if ($this->no_baucar) {
            return $this->no_baucar;
        }

        $year = (int) ($this->tarikh?->format('Y') ?? now()->year);

        return 'BV-' . $year . '-' . str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Map internal step/status to a human-friendly API status string.
     */
    private function resolveBaucarStatus(): string
    {
        if ($this->is_baucar_locked) {
            return 'approved';
        }

        if ((int) $this->approval_step === 1) {
            return 'pending-pengerusi';
        }

        if (!empty($this->catatan_tolak) && $this->approval_step === 0) {
            return 'rejected';
        }

        return 'draft';
    }
}
