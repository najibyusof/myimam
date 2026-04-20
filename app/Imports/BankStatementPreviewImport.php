<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class BankStatementPreviewImport implements ToCollection, WithHeadingRow, WithCalculatedFormulas
{
    /**
     * @var array<int, array<string, mixed>>
     */
    private array $rows = [];

    public function collection(Collection $collection): void
    {
        $this->rows = $collection->map(function (Collection|array $row): array {
            $data = $row instanceof Collection ? $row->toArray() : $row;

            $normalized = [];
            foreach ($data as $key => $value) {
                $normalized[$this->normalizeHeading((string) $key)] = $value;
            }

            return [
                'tarikh' => $this->pick($normalized, ['tarikh', 'date', 'transactiondate', 'posteddate', 'valuedate']),
                'description' => $this->pick($normalized, ['description', 'keterangan', 'butiran', 'detail', 'details', 'memo', 'narration', 'reference']),
                'akaun' => $this->pick($normalized, ['akaun', 'account', 'accountname', 'accountno', 'noakaun', 'bankaccount', 'accountnumber']),
                'debit' => $this->pick($normalized, ['debit', 'withdrawal', 'out', 'keluar', 'debitrm']),
                'credit' => $this->pick($normalized, ['credit', 'deposit', 'in', 'masuk', 'creditrm']),
                'balance' => $this->pick($normalized, ['balance', 'baki']),
            ];
        })->values()->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function rows(): array
    {
        return $this->rows;
    }

    /**
     * @param array<string, mixed> $normalized
     * @param array<int, string> $keys
     */
    private function pick(array $normalized, array $keys): mixed
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $normalized)) {
                return $normalized[$key];
            }
        }

        return null;
    }

    private function normalizeHeading(string $value): string
    {
        return Str::of($value)
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/i', '')
            ->toString();
    }
}
