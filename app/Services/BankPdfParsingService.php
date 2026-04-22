<?php

namespace App\Services;

use Carbon\Carbon;
use Smalot\PdfParser\Parser;

class BankPdfParsingService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function parse(string $filePath): array
    {
        $parser = new Parser();
        $pdf = $parser->parseFile($filePath);
        $text = $pdf->getText();

        $lines = $this->cleanLines($text);
        $entries = $this->groupTransactionLines($lines);

        $rows = [];

        foreach ($entries as $index => $entry) {
            $rows[] = $this->normalizeRow($entry, $index + 1);
        }

        return $rows;
    }

    /**
     * @return array<int, string>
     */
    private function cleanLines(string $text): array
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);

        $lines = preg_split('/\n+/', $text) ?: [];

        return collect($lines)
            ->map(fn (string $line): string => trim(preg_replace('/\s+/', ' ', $line) ?? ''))
            ->filter(fn (string $line): bool => $line !== '')
            ->reject(function (string $line): bool {
                $lower = strtolower($line);

                if (preg_match('/^page\s+\d+(\s+of\s+\d+)?$/i', $line)) {
                    return true;
                }

                if (preg_match('/^date\s+description\s+amount\s+balance$/i', $line)) {
                    return true;
                }

                if (preg_match('/^(opening|closing)\s+balance/i', $line)) {
                    return true;
                }

                return str_contains($lower, 'maybank')
                    || str_contains($lower, 'statement')
                    || str_contains($lower, 'account no')
                    || str_contains($lower, 'm2u')
                    || str_contains($lower, 'this is a computer generated');
            })
            ->values()
            ->all();
    }

    /**
     * @param array<int, string> $lines
     * @return array<int, string>
     */
    private function groupTransactionLines(array $lines): array
    {
        $entries = [];
        $current = null;

        foreach ($lines as $line) {
            if ($this->startsWithDate($line)) {
                if ($current !== null) {
                    $entries[] = trim($current);
                }

                $current = $line;
                continue;
            }

            if ($current !== null) {
                $current .= ' ' . $line;
            }
        }

        if ($current !== null) {
            $entries[] = trim($current);
        }

        return $entries;
    }

    private function startsWithDate(string $line): bool
    {
        return (bool) preg_match('/^\d{2}\/\d{2}\/\d{2}\b/', $line);
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeRow(string $entry, int $rowNumber): array
    {
        $base = [
            'row_number' => $rowNumber,
            'raw' => $entry,
            'tarikh' => null,
            'keterangan' => null,
            'jumlah' => null,
            'type_auto' => 'abaikan',
            'valid' => false,
            'errors' => [],
        ];

        if (!preg_match('/^(?<date>\d{2}\/\d{2}\/(?:\d{2}|\d{4}))\s+(?<rest>.+)$/', $entry, $header)) {
            $base['errors'] = ['Format baris transaksi tidak dikenali.'];
            return $base;
        }

        $rest = trim((string) ($header['rest'] ?? ''));
        // Match from the end of the row so multiline descriptions containing numbers are handled safely.
        if (!preg_match('/^(?<description>.+?)\s+(?<amount>\d[\d,]*\.\d{2}[+-])(?:\s+(?<balance>\d[\d,]*\.\d{2}(?:[+-])?))?$/u', $rest, $tail)) {
            $base['errors'] = ['Amaun transaksi tidak dijumpai pada hujung baris.'];
            return $base;
        }

        $isoDate = $this->normalizeDate((string) ($header['date'] ?? ''));
        $description = trim((string) ($tail['description'] ?? ''));
        $amountWithSign = (string) ($tail['amount'] ?? '');
        $amountValue = $this->parseAmount($amountWithSign);
        $sign = substr($amountWithSign, -1);

        $autoType = $this->classifyTransaction($description, $sign);

        $errors = [];
        if ($isoDate === null) {
            $errors[] = 'Tarikh tidak sah.';
        }

        if ($description === '') {
            $errors[] = 'Keterangan wajib diisi.';
        }

        if ($amountValue === null || $amountValue <= 0) {
            $errors[] = 'Amaun mesti lebih besar daripada 0.';
        }

        $base['tarikh'] = $isoDate;
        $base['keterangan'] = $description;
        $base['jumlah'] = $amountValue;
        $base['type_auto'] = $autoType;
        $base['valid'] = count($errors) === 0;
        $base['errors'] = $errors;

        return $base;
    }

    private function normalizeDate(string $date): ?string
    {
        $date = trim($date);
        if (!preg_match('/^\d{2}\/\d{2}\/(\d{2}|\d{4})$/', $date)) {
            return null;
        }

        [$day, $month, $year] = explode('/', $date);
        $year = (int) $year;
        if (strlen($year . '') === 2) {
            $year = $year >= 70 ? 1900 + $year : 2000 + $year;
        }

        try {
            return Carbon::createFromDate($year, (int) $month, (int) $day)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    private function parseAmount(string $amountWithSign): ?float
    {
        $amountWithSign = trim($amountWithSign);
        if (!preg_match('/^(?<value>\d[\d,]*\.\d{2})(?<sign>[+-])$/', $amountWithSign, $matches)) {
            return null;
        }

        $value = str_replace(',', '', (string) $matches['value']);

        if (!is_numeric($value)) {
            return null;
        }

        return (float) $value;
    }

    private function classifyTransaction(string $description, string $sign): string
    {
        $upper = strtoupper($description);

        $hasilKeywords = [
            'FUND TFR TO',
            'DEP',
            'CREDIT',
        ];

        foreach ($hasilKeywords as $keyword) {
            if (str_contains($upper, $keyword)) {
                return 'hasil';
            }
        }

        $belanjaKeywords = [
            'PAYMENT',
            'FPX',
            'BILL',
            'TRANSFER FROM',
        ];

        foreach ($belanjaKeywords as $keyword) {
            if (str_contains($upper, $keyword)) {
                return 'belanja';
            }
        }

        return $sign === '+' ? 'hasil' : 'belanja';
    }
}
