<?php

namespace Database\Factories;

use App\Models\Masjid;
use Illuminate\Database\Eloquent\Factories\Factory;

class SumberHasilFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id_masjid' => Masjid::factory(),
            'kod' => $this->faker->unique()->bothify('SUM##'),
            'nama_sumber' => $this->faker->words(3, true),
            'jenis' => 'derma',
            'aktif' => true,
        ];
    }
}
