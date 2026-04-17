<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

class AiPageGeneratorService
{
    private const ALLOWED_COMPONENTS = ['hero', 'text', 'image', 'button', 'grid', 'card', 'login_form'];

    private const ALLOWED_SECTION_VARIANTS = ['hero-saas', 'stats-bar', 'features-grid', 'cta-banner', 'footer-simple'];

    private const PROMPT_TEMPLATE = <<<'PROMPT'
Generate a JSON layout for a CMS page builder for a mosque management system.

STRICT RULES:
- Output ONLY valid JSON (no markdown, no explanations)
- The JSON must be a valid JSON array of section objects
- Each object must have EXACTLY three fields: "type", "variant", and "props"
- Always use "type": "section"
- Do NOT add any other fields (no id, no children, no footer, no nav, no header, no sidebar)
- Do NOT use any variant that is not in the allowed list below
- If you are unsure whether a variant is allowed, omit it

ALLOWED section variants (ONLY these, nothing else):
hero-saas, stats-bar, features-grid, cta-banner, footer-simple

DO NOT USE these types/variants (they will cause errors):
footer, header, nav, navbar, navigation, sidebar, divider, spacer, container, wrapper

Section prop schemas:
- hero-saas: { title, subtitle, primary_cta, secondary_cta, badge, image }
    - primary_cta: { text, link }
    - secondary_cta: { text, link }
- stats-bar: { items: [{ value, label }] }
- features-grid: { title, subtitle, items: [{ icon, title, desc }] }
- cta-banner: { title, subtitle, button: { text, link } }
- footer-simple: { brand, links: [{ text, link }] }

Design requirements:
- Modern, clean, mosque-friendly layout
- Content in Malay language
- User-focused and practical

User requirement:
{description}

Respond with ONLY the JSON array, starting with [ and ending with ]. No other text.
PROMPT;

    public function generateLayout(string $type, string $description): array
    {
        if (! $this->isValidType($type)) {
            throw new InvalidArgumentException("Invalid page type: {$type}");
        }

        $aiProvider = config('services.ai.provider', 'openai');
        $hasOpenAi = ! empty(config('services.openai.api_key'));
        $hasAnthropic = ! empty(config('services.anthropic.api_key'));

        // Fallback to template-based generation if no API key is configured
        if (($aiProvider === 'openai' && ! $hasOpenAi) || ($aiProvider === 'anthropic' && ! $hasAnthropic)) {
            return $this->generateFallbackLayout($type);
        }

        $prompt = str_replace('{description}', $description, self::PROMPT_TEMPLATE);

        $response = match ($aiProvider) {
            'openai' => $this->callOpenAi($prompt),
            'anthropic' => $this->callAnthropic($prompt),
            default => throw new InvalidArgumentException("Unsupported AI provider: {$aiProvider}"),
        };

        return $this->validateAndParseJson($response);
    }

    private function generateFallbackLayout(string $type): array
    {
        $layouts = [
            'landing' => [
                [
                    'type' => 'section',
                    'variant' => 'hero-saas',
                    'props' => [
                        'badge' => 'SaaS Kewangan Masjid',
                        'title' => 'Platform Kewangan Masjid Berbilang Cawangan',
                        'subtitle' => 'Urus kutipan, belanja, pelaporan, dan pemantauan cawangan dalam satu platform.',
                        'primary_cta' => ['text' => 'Daftar Masjid Anda', 'link' => '/login'],
                        'secondary_cta' => ['text' => 'Lihat Demo', 'link' => '/login'],
                        'image' => '/cms/defaults/landing-premium.svg',
                    ],
                ],
                [
                    'type' => 'section',
                    'variant' => 'stats-bar',
                    'props' => [
                        'items' => [
                            ['value' => '100+', 'label' => 'Masjid'],
                            ['value' => '50K+', 'label' => 'Transaksi'],
                            ['value' => '10K+', 'label' => 'Laporan'],
                            ['value' => '99.9%', 'label' => 'Ketersediaan'],
                        ],
                    ],
                ],
                [
                    'type' => 'section',
                    'variant' => 'features-grid',
                    'props' => [
                        'title' => 'Ciri Utama Untuk Operasi Masjid Moden',
                        'subtitle' => 'Modul yang direka untuk ketelusan, kelajuan, dan kawalan penuh.',
                        'items' => [
                            ['icon' => 'chart-bar', 'title' => 'Dashboard Masa Nyata', 'desc' => 'Pantau aliran kewangan harian setiap cawangan.'],
                            ['icon' => 'shield-check', 'title' => 'Akses Berperanan', 'desc' => 'Kawal capaian berdasarkan peranan pentadbiran.'],
                            ['icon' => 'document-check', 'title' => 'Jejak Audit Lengkap', 'desc' => 'Semua perubahan direkod untuk semakan telus.'],
                        ],
                    ],
                ],
                [
                    'type' => 'section',
                    'variant' => 'cta-banner',
                    'props' => [
                        'title' => 'Mulakan Pendigitalan Kewangan Masjid Hari Ini',
                        'subtitle' => 'Tingkatkan keyakinan jemaah dengan pelaporan yang teratur dan telus.',
                        'button' => ['text' => 'Daftar Masjid Anda', 'link' => '/login'],
                    ],
                ],
                [
                    'type' => 'section',
                    'variant' => 'footer-simple',
                    'props' => [
                        'brand' => 'MyImam',
                        'links' => [
                            ['text' => 'Tentang', 'link' => '#'],
                            ['text' => 'Ciri-Ciri', 'link' => '#'],
                            ['text' => 'Hubungi', 'link' => '#'],
                        ],
                    ],
                ],
            ],
            'login' => [
                ['type' => 'hero', 'props' => ['title' => 'Log Masuk Portal Masjid', 'subtitle' => 'Akses maklumat penting dan urus aktiviti anda.', 'button_text' => '', 'button_link' => ''], 'children' => []],
                ['type' => 'login_form', 'props' => ['title' => 'Log Masuk', 'subtitle' => 'Sila masukkan kelayakan anda.'], 'children' => []],
                ['type' => 'text', 'props' => ['text' => 'Untuk bantuan, sila hubungi pentadbir masjid.'], 'children' => []],
            ],
            'about' => [
                ['type' => 'hero', 'props' => ['title' => 'Tentang Masjid Kami', 'subtitle' => 'Melayani komuniti Muslim sejak bergenerasi.', 'button_text' => 'Hubungi Kami', 'button_link' => '#'], 'children' => []],
                ['type' => 'text', 'props' => ['text' => 'Masjid kami telah menjadi pusat kegiatan keagamaan dan komuniti selama bertahun-tahun. Kami berkomitmen untuk menyediakan persekitaran yang kondusif bagi semua umat Islam.'], 'children' => []],
                ['type' => 'grid', 'props' => ['columns' => 2, 'items' => "Visi: Menjadi masjid terpilih di kawasan ini\nMisi: Melayani komuniti dengan ikhlas dan profesional"], 'children' => []],
                ['type' => 'card', 'props' => ['title' => 'Program Utama', 'text' => 'Kuliah agama, kelas Al-Quran, program kebajikan, dan pelbagai aktiviti masyarakat.'], 'children' => []],
                ['type' => 'text', 'props' => ['text' => 'Untuk maklumat lanjut, hubungi kami di talian 03-XXXX XXXX atau emel ke admin@masjid.my'], 'children' => []],
            ],
            'donation' => [
                ['type' => 'hero', 'props' => ['title' => 'Kempen Derma Masjid', 'subtitle' => 'Setiap sumbangan anda memberi manfaat kepada komuniti.', 'button_text' => 'Derma Sekarang', 'button_link' => '/login'], 'children' => []],
                ['type' => 'text', 'props' => ['text' => 'Dengan sokongan anda, kami dapat meneruskan program-program yang bermakna untuk komuniti.'], 'children' => []],
                ['type' => 'grid', 'props' => ['columns' => 3, 'items' => "Pembinaan & Naik Taraf\nProgram Pendidikan\nBantuan Kebajikan"], 'children' => []],
                ['type' => 'card', 'props' => ['title' => 'Cara Menyumbang', 'text' => 'Log masuk ke portal untuk membuat sumbangan secara dalam talian dengan selamat.'], 'children' => [
                    ['type' => 'button', 'props' => ['button_text' => 'Log Masuk & Derma', 'button_link' => '/login'], 'children' => []],
                ]],
            ],
        ];

        return ['components' => $layouts[$type] ?? $layouts['landing']];
    }

    private function callOpenAi(string $prompt): string
    {
        $apiKey = config('services.openai.api_key');
        if (! $apiKey) {
            throw new InvalidArgumentException('OpenAI API key not configured.');
        }

        $response = Http::withToken($apiKey)
            ->timeout(30)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => config('services.openai.model', 'gpt-3.5-turbo'),
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a JSON generator for CMS page layouts. Always respond with ONLY valid JSON.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.7,
                'max_tokens' => 2000,
            ]);

        if (! $response->successful()) {
            throw new InvalidArgumentException('OpenAI API error: ' . $response->body());
        }

        $data = $response->json();
        return $data['choices'][0]['message']['content'] ?? '';
    }

    private function callAnthropic(string $prompt): string
    {
        $apiKey = config('services.anthropic.api_key');
        if (! $apiKey) {
            throw new InvalidArgumentException('Anthropic API key not configured.');
        }

        $response = Http::withToken($apiKey)
            ->timeout(30)
            ->post('https://api.anthropic.com/v1/messages', [
                'model' => config('services.anthropic.model', 'claude-3-haiku-20240307'),
                'max_tokens' => 2000,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

        if (! $response->successful()) {
            throw new InvalidArgumentException('Anthropic API error: ' . $response->body());
        }

        $data = $response->json();
        return $data['content'][0]['text'] ?? '';
    }

    private function validateAndParseJson(string $response): array
    {
        $cleaned = trim($response);

        // Remove markdown code blocks if present
        if (str_starts_with($cleaned, '```')) {
            $cleaned = preg_replace('/^```json?\s*/', '', $cleaned);
            $cleaned = preg_replace('/```\s*$/', '', $cleaned);
            $cleaned = trim($cleaned);
        }

        $components = json_decode($cleaned, true, flags: JSON_THROW_ON_ERROR);

        if (! is_array($components)) {
            throw new InvalidArgumentException('Generated output is not an array.');
        }

        // Validate each component — skip unknown types instead of aborting
        $validated = [];
        $skipped = [];
        foreach ($components as $component) {
            try {
                $validated[] = $this->validateComponent($component);
            } catch (InvalidArgumentException $e) {
                $skipped[] = $component['type'] ?? '?';
            }
        }

        if (empty($validated)) {
            throw new InvalidArgumentException(
                'AI returned no usable components. Skipped types: ' . implode(', ', $skipped) . '.'
            );
        }

        return ['components' => $validated];
    }

    private function validateComponent(array $component): array
    {
        if (! isset($component['type'], $component['props'])) {
            throw new InvalidArgumentException('Component missing type or props.');
        }

        $type = strtolower((string) $component['type']);
        $props = is_array($component['props']) ? $component['props'] : [];

        // New section-based schema
        if ($type === 'section') {
            $variant = strtolower((string) ($component['variant'] ?? ''));
            if (! in_array($variant, self::ALLOWED_SECTION_VARIANTS, true)) {
                throw new InvalidArgumentException("Invalid section variant: {$variant}");
            }

            return [
                'type' => 'section',
                'variant' => $variant,
                'props' => $props,
            ];
        }

        // Backward-compatible legacy component schema
        if (! in_array($type, self::ALLOWED_COMPONENTS, true)) {
            throw new InvalidArgumentException("Invalid component type: {$type}");
        }

        return [
            'type' => $type,
            'props' => $props,
            'children' => [],
        ];
    }

    private function isValidType(string $type): bool
    {
        return in_array($type, ['landing', 'login', 'about', 'donation'], true);
    }

    public function isFallbackMode(): bool
    {
        $provider = config('services.ai.provider', 'openai');
        return match ($provider) {
            'openai' => empty(config('services.openai.api_key')),
            'anthropic' => empty(config('services.anthropic.api_key')),
            default => true,
        };
    }

    public function getPageTypeDescriptions(): array
    {
        return [
            'landing' => 'Landing Page',
            'login' => 'Login Page',
            'about' => 'About Masjid',
            'donation' => 'Donation Campaign',
        ];
    }

    public function getPresetPrompts(): array
    {
        return [
            'landing_modern' => [
                'label' => 'Landing Page Masjid Moden',
                'type' => 'landing',
                'description' => 'Landing page untuk masjid moden dengan info derma, program, dan pengumuman. Sertakan hero besar, grid program, dan call-to-action untuk login.',
            ],
            'login_minimal' => [
                'label' => 'Halaman Login Minimal',
                'type' => 'login',
                'description' => 'Halaman login yang bersih dan minimal dengan hero singkat dan form login. Tekankan keamanan dan kemudahan akses.',
            ],
            'donation_ramadan' => [
                'label' => 'Kempen Derma Ramadan',
                'type' => 'donation',
                'description' => 'Halaman kempen derma Ramadan dengan hero motivasi, grid tujuan derma, info program Ramadan, dan tombol donate/login.',
            ],
            'about_mosque' => [
                'label' => 'Halaman Tentang Masjid',
                'type' => 'about',
                'description' => 'Halaman tentang masjid dengan sejarah, visi-misi, program utama, dan informasi kontak. Gunakan text blocks, grid, dan cards untuk organisasi.',
            ],
        ];
    }
}
