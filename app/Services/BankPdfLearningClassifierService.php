<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class BankPdfLearningClassifierService
{
    private const CACHE_PREFIX = 'bank_pdf_learning_rules:';
    private const MAX_RULES = 200;

    /**
     * @return array{type: string, score: int, source: string, akaun_id: ?int, kategori_id: ?int}
     */
    public function suggest(int $masjidId, string $description, string $fallbackType): array
    {
        $normalized = $this->normalize($description);
        if ($normalized === '') {
            return [
                'type' => $fallbackType,
                'score' => 0,
                'source' => 'fallback',
                'akaun_id' => null,
                'kategori_id' => null,
            ];
        }

        $best = [
            'type' => $fallbackType,
            'score' => 0,
            'source' => 'fallback',
            'akaun_id' => null,
            'kategori_id' => null,
        ];

        foreach ($this->builtinRules() as $rule) {
            if (!str_contains($normalized, $rule['pattern'])) {
                continue;
            }

            if ($rule['score'] <= $best['score']) {
                continue;
            }

            $best = [
                'type' => $rule['type'],
                'score' => $rule['score'],
                'source' => 'builtin',
                'akaun_id' => null,
                'kategori_id' => null,
            ];
        }

        foreach ($this->getRules($masjidId) as $rule) {
            if (!str_contains($normalized, (string) ($rule['pattern'] ?? ''))) {
                continue;
            }

            $score = (int) ($rule['weight'] ?? 0);
            if ($score <= $best['score']) {
                continue;
            }

            $best = [
                'type' => (string) ($rule['type'] ?? $fallbackType),
                'score' => $score,
                'source' => 'learned',
                'akaun_id' => isset($rule['akaun_id']) ? (int) $rule['akaun_id'] : null,
                'kategori_id' => isset($rule['kategori_id']) ? (int) $rule['kategori_id'] : null,
            ];
        }

        return $best;
    }

    public function learnFromConfirmedImport(
        int $masjidId,
        string $description,
        string $type,
        ?int $akaunId,
        ?int $kategoriId,
        bool $wasUserOverride
    ): void {
        $normalized = $this->normalize($description);
        if ($normalized === '' || $type === 'abaikan') {
            return;
        }

        $patterns = $this->extractPatterns($normalized);
        if (empty($patterns)) {
            return;
        }

        $rules = $this->getRules($masjidId);

        foreach ($patterns as $pattern) {
            $found = false;

            foreach ($rules as &$rule) {
                if (($rule['pattern'] ?? '') !== $pattern || ($rule['type'] ?? '') !== $type) {
                    continue;
                }

                $rule['weight'] = (int) ($rule['weight'] ?? 0) + ($wasUserOverride ? 3 : 1);
                $rule['akaun_id'] = $akaunId;
                $rule['kategori_id'] = $kategoriId;
                $rule['updated_at'] = now()->toDateTimeString();
                $found = true;
                break;
            }
            unset($rule);

            if ($found) {
                continue;
            }

            $rules[] = [
                'pattern' => $pattern,
                'type' => $type,
                'akaun_id' => $akaunId,
                'kategori_id' => $kategoriId,
                'weight' => $wasUserOverride ? 5 : 2,
                'updated_at' => now()->toDateTimeString(),
            ];
        }

        usort($rules, fn (array $a, array $b): int => (int) ($b['weight'] ?? 0) <=> (int) ($a['weight'] ?? 0));
        $rules = array_slice($rules, 0, self::MAX_RULES);

        Cache::forever($this->cacheKey($masjidId), $rules);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getRules(int $masjidId): array
    {
        $rules = Cache::get($this->cacheKey($masjidId), []);

        return is_array($rules) ? $rules : [];
    }

    private function cacheKey(int $masjidId): string
    {
        return self::CACHE_PREFIX . $masjidId;
    }

    private function normalize(string $text): string
    {
        return Str::of($text)
            ->upper()
            ->replaceMatches('/[^A-Z0-9\s]+/', ' ')
            ->replaceMatches('/\s+/', ' ')
            ->trim()
            ->toString();
    }

    /**
     * @return array<int, string>
     */
    private function extractPatterns(string $normalized): array
    {
        $tokens = array_values(array_filter(explode(' ', $normalized), fn (string $token): bool => strlen($token) >= 3));
        if (empty($tokens)) {
            return [];
        }

        $patterns = [];

        $firstTwo = array_slice($tokens, 0, 2);
        if (count($firstTwo) === 2) {
            $patterns[] = implode(' ', $firstTwo);
        }

        $firstThree = array_slice($tokens, 0, 3);
        if (count($firstThree) === 3) {
            $patterns[] = implode(' ', $firstThree);
        }

        $patterns[] = implode(' ', array_slice($tokens, 0, min(4, count($tokens))));

        return array_values(array_unique(array_filter($patterns)));
    }

    /**
     * @return array<int, array{pattern: string, type: string, score: int}>
     */
    private function builtinRules(): array
    {
        return [
            ['pattern' => 'FUND TFR TO', 'type' => 'hasil', 'score' => 50],
            ['pattern' => 'DEP', 'type' => 'hasil', 'score' => 45],
            ['pattern' => 'CREDIT', 'type' => 'hasil', 'score' => 45],
            ['pattern' => 'PAYMENT', 'type' => 'belanja', 'score' => 50],
            ['pattern' => 'FPX', 'type' => 'belanja', 'score' => 48],
            ['pattern' => 'BILL', 'type' => 'belanja', 'score' => 48],
            ['pattern' => 'TRANSFER FROM', 'type' => 'belanja', 'score' => 42],
        ];
    }
}
