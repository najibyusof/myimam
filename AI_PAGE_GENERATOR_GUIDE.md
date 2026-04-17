# AI Page Generator - Implementation Guide

## Overview
The AI Page Generator is a non-invasive modal interface integrated into the CMS Builder that allows admins to generate page layouts using natural language descriptions. Generated components are automatically inserted into the builder canvas and can be further customized.

## Architecture

### Backend Components

#### 1. AiPageGeneratorService (`app/Services/AiPageGeneratorService.php`)
Handles AI-powered layout generation with validation and multi-provider support.

**Key Methods:**
- `generateLayout(string $type, string $description): array` - Routes to appropriate AI provider
- `validateAndParseJson(string $response): array` - Parses and validates AI response JSON
- `validateComponent(array $component): array` - Enforces allowed types and required fields
- `getPresetPrompts(): array` - Returns 4 predefined preset templates

**Supported Component Types:**
- `hero` - Full-width header with title, subtitle, button
- `text` - Paragraph content
- `image` - Image with optional caption
- `button` - CTA button
- `grid` - Multi-column layout
- `card` - Content card with optional children
- `login_form` - Pre-built login form

**Configuration:**
```php
// config/services.php
'ai' => [
    'provider' => 'openai', // 'openai' or 'anthropic'
]
```

#### 2. AiPageGeneratorController (`app/Http/Controllers/Admin/AiPageGeneratorController.php`)
Provides API endpoints for AI generation and preset retrieval.

**Endpoints:**
- `POST /admin/cms/builder/generate` - Generate layout from description
  - Request: `{ type: string, description: string }`
  - Response: `{ success: boolean, layout: {...}, message: string }`
  - Returns: 200 OK with layout or 422 with error message

- `GET /admin/cms/builder/presets` - Fetch available preset templates
  - Response: `{ presets: [...] }`
  - Returns: 200 OK with array of presets

**Authorization:**
Both endpoints require `cms.manage` permission via middleware.

### Frontend Components

#### Modal UI (`resources/views/admin/cms/builder.blade.php`)
Located around line 512-560.

**Form Fields:**
1. Page Type Selector - Dropdown with: landing, login, about, donation
2. Description Textarea - Natural language input (10-1000 chars)
3. Preset Buttons - Quick-select templates
4. Error Display - User-friendly error messages
5. Loading State - Shows "Memproses..." during generation

**Actions:**
- `@click="openAiModal()"` - Opens modal (toolbar button ~line 137)
- `@click="closeAiModal()"` - Closes modal (Cancel button)
- `@click="generatePage()"` - Sends request to backend
- `@click="selectPreset(preset)"` - Pre-fills form from template

#### Alpine.js State & Methods
All logic in `cmsBuilder()` function (~line 587+).

**State Variables:**
```javascript
aiModalOpen: false,           // Modal visibility
aiPageType: 'landing',        // Selected page type
aiDescription: '',            // User input description
aiLoading: false,             // Generation in progress
aiError: '',                  // Error message display
aiPresets: [],                // Loaded preset templates
generateUrl,                  // API endpoint URL
presetsUrl,                   // Presets endpoint URL
```

**Methods:**

1. **openAiModal()**
   - Sets `aiModalOpen = true`
   - Clears error messages
   - Loads presets via `fetchPresets()`

2. **closeAiModal()**
   - Sets `aiModalOpen = false`
   - Clears form fields

3. **generatePage()**
   - Validates description length (≥10 chars)
   - POST to generateUrl with type & description
   - Parses JSON response
   - Maps components via `hydrateComponent()`
   - Updates canvas with new components
   - Captures history for undo/redo
   - Reinitializes Sortable for drag-drop
   - Closes modal on success

4. **selectPreset(preset)**
   - Auto-fills page type from preset
   - Auto-fills description from preset
   - Ready for generate() call

5. **fetchPresets()**
   - GET from presetsUrl
   - Populates `aiPresets` array
   - Called on modal init()

## Data Flow

```
User clicks "Jana Halaman AI" button
    ↓
openAiModal() called
    ↓
fetchPresets() loads templates
    ↓
User enters description (or selects preset)
    ↓
User clicks "Jana Rekabentuk"
    ↓
generatePage() validates & POSTs to backend
    ↓
AiPageGeneratorController::generate() receives request
    ↓
AiPageGeneratorService generates layout JSON
    ↓
JSON validated & returned to frontend
    ↓
Components inserted into this.components
    ↓
Canvas rendered with new components
    ↓
Modal closes
```

## API Request/Response Examples

### Generate Request
```json
POST /admin/cms/builder/generate
Content-Type: application/json

{
  "type": "landing",
  "description": "Modern mosque website with donation section and prayer times widget"
}
```

### Generate Response
```json
{
  "success": true,
  "layout": {
    "components": [
      {
        "type": "hero",
        "props": {
          "title": "Selamat Datang ke Portal Masjid",
          "subtitle": "Maklumat Solat, Aktiviti & Komuniti",
          "button_text": "Pelajari Lebih Lanjut",
          "button_link": "/about"
        },
        "children": []
      },
      {
        "type": "card",
        "props": {
          "title": "Kempen Derma Tahunan",
          "text": "Bantu kami mengembangkan kemudahan masjid"
        },
        "children": [
          {
            "type": "button",
            "props": {
              "button_text": "Sumbang Sekarang",
              "button_link": "/donate"
            }
          }
        ]
      }
    ]
  },
  "message": "Rekabentuk halaman berjaya dijana."
}
```

### Presets Response
```json
{
  "presets": [
    {
      "id": "landing_modern",
      "label": "Landing Page Masjid Moden",
      "type": "landing",
      "description": "Landing page dengan hero, program grid, dan CTA derma"
    },
    {
      "id": "login_minimal",
      "label": "Laman Log Masuk Ringkas",
      "type": "login",
      "description": "Login page minimal dengan form dan footer"
    },
    ...
  ]
}
```

## Testing

### Unit Tests
Test component validation logic:
```bash
php artisan test tests/Feature/AiPageGeneratorTest.php::test_ai_page_generator_service_validates_components
```

### Authorization Tests
```bash
php artisan test tests/Feature/AiPageGeneratorTest.php::test_admin_cannot_access_ai_without_permission
```

### Validation Tests
```bash
php artisan test tests/Feature/AiPageGeneratorTest.php::test_generate_rejects_invalid_page_type
php artisan test tests/Feature/AiPageGeneratorTest.php::test_generate_rejects_short_description
```

### Run All AI Tests
```bash
php artisan test tests/Feature/AiPageGeneratorTest.php
```

## Error Handling

### Frontend Errors
- Invalid description length → "Penerangan mestilah sekurang-kurangnya 10 aksara."
- API response error → Shows error message from backend
- Network error → Generic error message displayed

### Backend Validation
- Invalid page type → 422 Unprocessable Entity
- Description too short → 422 Unprocessable Entity
- Missing required fields → 422 Unprocessable Entity
- Permission denied → 403 Forbidden
- AI provider error → 422 with error message

## Configuration

### Environment Variables
```bash
# For OpenAI provider
OPENAI_API_KEY=sk-...

# For Anthropic provider
ANTHROPIC_API_KEY=sk-ant-...
```

### Config File
```php
// config/services.php
'ai' => [
    'provider' => env('AI_PROVIDER', 'openai'),
    'models' => [
        'openai' => 'gpt-4-turbo',      // or 'gpt-3.5-turbo'
        'anthropic' => 'claude-3-sonnet-20240229',
    ],
],
```

## Integration Notes

### Multi-Tenant Support
- AI generation respects current tenant context
- Generated pages are scoped to the current `id_masjid`
- Service automatically injects tenant context (if available)

### Backward Compatibility
- Manual editing fully functional alongside AI generation
- No breaking changes to existing builder functionality
- Existing components work with AI-generated layouts

### Performance
- Preset fetching cached in frontend state
- No unnecessary API calls (presets loaded once on modal open)
- Generated components inserted efficiently via array operations
- Sortable reinitialization only when needed

## Future Enhancements

Potential improvements:
1. **Custom Prompt Templates** - Allow users to create reusable generation prompts
2. **Component Preview** - Show preview of components before insertion
3. **Layout Variations** - Generate multiple layout options for selection
4. **Component Suggestions** - AI suggests next component based on current layout
5. **Copy from Library** - Generate from popular mosque website templates
6. **Component Refinement** - Edit AI-generated props with AI assistance

## Troubleshooting

### Modal not appearing
- Check browser console for errors
- Verify Alpine.js loaded correctly
- Confirm permission `cms.manage` granted

### Generation fails with API error
- Verify API key configured correctly
- Check API rate limits
- Confirm internet connectivity

### Generated components not appearing
- Check browser console for fetch errors
- Verify component hydration in builder
- Ensure Sortable reinitialized

### Presets not loading
- Check network tab in DevTools
- Verify `/admin/cms/builder/presets` returns valid JSON
- Confirm permission `cms.manage` granted

## Support & Documentation

For more information:
- See `IMPLEMENTATION_COMPLETE.md` for feature overview
- Check `API_DOCUMENTATION.md` for endpoint details
- Review test file: `tests/Feature/AiPageGeneratorTest.php`
