<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MasjidFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nama' => $this->faker->name() . ' Masjid',
            'code' => $this->faker->unique()->bothify('MJ##??'),
            'alamat' => $this->faker->address(),
            'daerah' => $this->faker->city(),
            'negeri' => 'Selangor',
            'no_pendaftaran' => $this->faker->unique()->numerify('REG-#####'),
            'tarikh_daftar' => now(),
            'status' => 'active',
            'subscription_status' => 'active',
            'subscription_expiry' => now()->addYear(),
        ];
    }
}
