<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class HasilImportPreview implements ToCollection, WithHeadingRow, WithCalculatedFormulas
{
    /**
     * @var array<int, array<string, mixed>>
     */
    private array $rows = [];

    public function collection(Collection $collection): void
    {
        $this->rows = $collection
            ->map(function (Collection|array $row): array {
                $data = $row instanceof Collection ? $row->toArray() : $row;

                return [
                    'tarikh' => $data['tarikh'] ?? null,
                    'sumber' => $data['sumber'] ?? null,
                    'amaun' => $data['amaun'] ?? null,
                    'akaun' => $data['akaun'] ?? null,
                    'catatan' => $data['catatan'] ?? null,
                    'tabung_khas' => $data['tabung_khas'] ?? null,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function rows(): array
    {
        return $this->rows;
    }
}
