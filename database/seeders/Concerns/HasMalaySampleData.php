<?php

namespace Database\Seeders\Concerns;

use Illuminate\Support\Str;

trait HasMalaySampleData
{
    protected array $namaLelaki = [
        'Ahmad',
        'Muhammad',
        'Ali',
        'Hafiz',
        'Zulkifli',
        'Ismail',
        'Rahman',
        'Azman',
        'Faizal',
        'Hakim',
    ];

    protected array $namaPerempuan = [
        'Siti',
        'Aisyah',
        'Nurul',
        'Fatimah',
        'Zainab',
        'Hajar',
        'Salmah',
        'Rohana',
        'Maznah',
        'Liyana',
    ];

    protected array $bin = ['bin', 'binti'];

    protected function generateMalayName(): string
    {
        $male = fake()->boolean();

        if ($male) {
            return fake()->randomElement($this->namaLelaki) . ' ' .
                fake()->randomElement($this->bin) . ' ' .
                fake()->randomElement($this->namaLelaki);
        }

        return fake()->randomElement($this->namaPerempuan) . ' ' .
            fake()->randomElement($this->bin) . ' ' .
            fake()->randomElement($this->namaLelaki);
    }

    protected function buildMasjidEmail(string $name): string
    {
        return Str::slug($name, '.') . '@masjid.com';
    }

    protected function buildUniqueMasjidEmail(string $name, array &$usedEmails): string
    {
        $base = Str::slug($name, '.');
        $candidate = $base . '@masjid.com';
        $counter = 2;

        while (in_array($candidate, $usedEmails, true)) {
            $candidate = $base . $counter . '@masjid.com';
            $counter++;
        }

        $usedEmails[] = $candidate;

        return $candidate;
    }
}
