<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MasjidResource extends JsonResource
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
            'nama' => $this->nama,
            'alamat' => $this->alamat,
            'bandar' => $this->bandar,
            'negeri' => $this->negeri,
            'poskod' => $this->poskod,
            'no_telefon' => $this->no_telefon,
            'emel' => $this->emel,
            'kapasiti_solat' => $this->kapasiti_solat,
            'imam' => $this->imam,
            'status' => $this->status,
            'tahun_ditubuhkan' => $this->tahun_ditubuhkan,
            'koordinat_lat' => $this->koordinat_lat,
            'koordinat_long' => $this->koordinat_long,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
