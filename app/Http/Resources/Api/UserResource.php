<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'peranan' => $this->peranan,
            'aktif' => $this->aktif,
            'email_verified_at' => $this->email_verified_at,
            'two_factor_enabled' => $this->hasTwoFactorEnabled(),
            'masjid_id' => $this->id_masjid,
            'masjid' => new MasjidResource($this->whenLoaded('masjid')),
            'roles' => $this->getRoleNames(),
            'permissions' => $this->getAllPermissions()->pluck('name'),
            'signature_path' => $this->signature_path,
            'signature_url' => $this->signature_path ? Storage::disk('public')->url($this->signature_path) : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
