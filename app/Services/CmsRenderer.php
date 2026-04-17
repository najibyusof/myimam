<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\View;

class CmsRenderer
{
    /**
     * @param  array<string, mixed>|null  $layout
     * @param  array<string, mixed>  $context
     */
    public function render(?array $layout, array $context = []): string
    {
        $components = Arr::get($layout ?? [], 'components', []);
        if (! is_array($components) || empty($components)) {
            return '';
        }

        $html = '';

        foreach ($components as $component) {
            $html .= $this->renderComponent($component, $context);
        }

        return $html;
    }

    /**
     * @param  array<string, mixed>  $component
     * @param  array<string, mixed>  $context
     */
    private function renderComponent(array $component, array $context): string
    {
        $type = strtolower((string) Arr::get($component, 'type', ''));
        if ($type === '') {
            return '';
        }

        if ($type === 'section') {
            return $this->renderSection($component, $context);
        }

        $props = Arr::get($component, 'props', []);
        $props = is_array($props) ? $props : [];

        $children = Arr::get($component, 'children', []);
        if (is_array($children) && ! empty($children)) {
            $props['_children_html'] = $this->render(['components' => $children], $context);
        }

        $viewMap = [
            'hero' => 'cms.components.hero',
            'text' => 'cms.components.text',
            'image' => 'cms.components.image',
            'button' => 'cms.components.button',
            'card' => 'cms.components.card',
            'grid' => 'cms.components.grid',
            'form' => 'cms.components.form',
            'login_form' => 'cms.components.login-form',
        ];

        $viewName = $viewMap[$type] ?? null;
        if (! $viewName || ! View::exists($viewName)) {
            return '';
        }

        return view($viewName, [
            'props' => $props,
            'context' => $context,
        ])->render();
    }

    /**
     * @param  array<string, mixed>  $component
     * @param  array<string, mixed>  $context
     */
    private function renderSection(array $component, array $context): string
    {
        $variant = strtolower((string) Arr::get($component, 'variant', ''));
        if ($variant === '') {
            return '';
        }

        $props = Arr::get($component, 'props', []);
        $props = is_array($props) ? $props : [];

        $viewName = match ($variant) {
            'hero-saas' => 'cms.sections.hero-saas',
            'stats-bar' => 'cms.sections.stats-bar',
            'features-grid' => 'cms.sections.features-grid',
            'cta-banner' => 'cms.sections.cta-banner',
            'footer-simple' => 'cms.sections.footer-simple',
            default => null,
        };

        if (! $viewName || ! View::exists($viewName)) {
            return '';
        }

        return view($viewName, [
            'props' => $props,
            'context' => $context,
        ])->render();
    }
}
