<?php

namespace Database\Factories;

use App\Models\Masjid;
use App\Models\Akaun;
use App\Models\SumberHasil;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class HasilFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id_masjid' => Masjid::factory(),
            'tarikh' => now(),
            'no_resit' => $this->faker->unique()->numerify('RST-###'),
            'id_akaun' => Akaun::factory(),
            'id_sumber_hasil' => SumberHasil::factory(),
            'amaun_tunai' => $this->faker->numberBetween(100, 5000),
            'amaun_online' => $this->faker->numberBetween(0, 2000),
            'jumlah' => $this->faker->numberBetween(100, 5000),
            'created_by' => User::factory(),
        ];
    }
}
