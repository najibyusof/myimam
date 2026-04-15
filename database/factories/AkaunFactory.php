<?php

namespace Database\Factories;

use App\Models\Masjid;
use Illuminate\Database\Eloquent\Factories\Factory;

class AkaunFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id_masjid' => Masjid::factory(),
            'nama_akaun' => $this->faker->words(2, true),
            'jenis' => $this->faker->randomElement(['tunai', 'bank']),
            'no_akaun' => $this->faker->unique()->numerify('ACC-###-###'),
            'nama_bank' => $this->faker->company() . ' Bank',
            'status_aktif' => true,
        ];
    }
}
