# Premium UI Component Library - Integration Guide

## Directory Structure

All UI components are located in:

```
resources/views/components/
├── stat-card.blade.php         # KPI cards with metrics
├── summary-card.blade.php      # Summary cards with lists
├── data-table.blade.php        # Responsive data tables
├── button.blade.php            # Versatile buttons
├── form-input.blade.php        # Text inputs, textarea, select
├── checkbox.blade.php          # Checkbox inputs
├── radio.blade.php             # Radio buttons
├── alert.blade.php             # Alert messages
├── badge.blade.php             # Status badges
├── progress-bar.blade.php      # Progress indicators
├── breadcrumb.blade.php        # Navigation breadcrumbs
└── modal-form.blade.php        # Modal dialogs
```

## Quick Integration

### Step 1: Use the Layout

Extend `app-premium` layout in your views:

```blade
@extends('layouts.app-premium')

@section('content')
    <!-- Your content here -->
@endsection
```

### Step 2: Include Components

Use components with `@include()` or component syntax:

```blade
<!-- Component syntax (auto-loaded) -->
<x-stat-card title="Total Users" value="2,543" color="blue" />

<!-- Or include syntax -->
@include('components.stat-card', [
    'title' => 'Total Users',
    'value' => 2543,
    'color' => 'blue'
])
```

---

## Component Reference

### 1. Stat Cards (Metrics Display)

Display key performance indicators with optional trend arrows.

**File:** `components/stat-card.blade.php`

**Example:**

```blade
<x-stat-card
    title="Total Budget"
    value="RM 1.2M"
    subtitle="Fiscal Year 2024"
    :trend="['direction' => 'up', 'value' => 15]"
    color="blue"
    icon="<svg>...</svg>"
/>
```

**Available Props:**

- `title` - Card title
- `value` - Main metric value
- `subtitle` - Supporting text
- `trend` - Optional trend: `['direction' => 'up'|'down', 'value' => number]`
- `color` - `blue`, `green`, `red`, `purple`, `indigo`
- `icon` - SVG icon (optional)

---

### 2. Summary Cards (Information Cards)

Display structured list of information with header and optional action.

**File:** `components/summary-card.blade.php`

**Example:**

```blade
@include('components.summary-card', [
    'title' => 'Budget Overview',
    'color' => 'indigo',
    'items' => [
        ['label' => 'Total', 'value' => 'RM 1.2M'],
        ['label' => 'Spent', 'value' => 'RM 523K'],
    ],
    'action' => ['label' => 'View Details', 'url' => '#']
])
```

---

### 3. Data Tables

Responsive table with search, filtering, and row actions.

**File:** `components/data-table.blade.php`

**Example:**

```blade
@include('components.data-table', [
    'columns' => [
        ['key' => 'name', 'label' => 'Name', 'type' => 'avatar'],
        ['key' => 'email', 'label' => 'Email'],
        ['key' => 'status', 'label' => 'Status', 'type' => 'badge'],
        ['key' => 'date', 'label' => 'Date', 'type' => 'date'],
    ],
    'rows' => $users,
    'actions' => [
        ['type' => 'link', 'label' => 'Edit', 'url' => fn($row) => route('users.edit', $row['id'])],
    ]
])
```

**Column Types:**

- `avatar` - Shows initials in circle
- `badge` - Auto-colored status
- `link` - Clickable link
- `date` - Formatted datetime
- (default) - Plain text

---

### 4. Buttons

Multiple button styles for different use cases.

**File:** `components/button.blade.php`

**Examples:**

```blade
<!-- Primary button -->
<x-button type="primary" size="md">Click Me</x-button>

<!-- Danger button -->
<x-button type="danger" size="lg">Delete</x-button>

<!-- Link button -->
<x-button type="link" href="{{ route('home') }}">Go Home</x-button>

<!-- With icon -->
<x-button type="primary" icon="<svg>...</svg>" iconPosition="left">
    Download
</x-button>

<!-- Loading state -->
<x-button type="primary" :loading="$isLoading">Submit</x-button>
```

**Available Types:**

- `primary` - Blue, main action
- `secondary` - Gray, secondary action
- `danger` - Red, destructive
- `success` - Green, positive
- `warning` - Yellow, cautionary
- `ghost` - Transparent
- `link` - Link style

**Sizes:** `sm`, `md`, `lg`, `xl`

---

### 5. Form Inputs

Text input, textarea, select with validation support.

**File:** `components/form-input.blade.php`

**Examples:**

```blade
<!-- Text input -->
@include('components.form-input', [
    'label' => 'Full Name',
    'name' => 'name',
    'placeholder' => 'Enter name',
    'required' => true,
    'error' => $errors->first('name')
])

<!-- Textarea -->
@include('components.form-input', [
    'label' => 'Description',
    'name' => 'description',
    'type' => 'textarea',
    'help' => 'Maximum 500 characters'
])

<!-- Select -->
@include('components.form-input', [
    'label' => 'Category',
    'name' => 'category',
    'type' => 'select'
])
    <option value="">-- Select --</option>
    <option value="cat1">Category 1</option>
    <option value="cat2">Category 2</option>
@endinclude

<!-- With icon -->
@include('components.form-input', [
    'label' => 'Email',
    'name' => 'email',
    'type' => 'email',
    'icon' => '<svg class="w-5 h-5">...</svg>'
])
```

---

### 6. Checkboxes & Radio Buttons

**File:** `components/checkbox.blade.php` | `components/radio.blade.php`

**Examples:**

```blade
<!-- Checkbox -->
@include('components.checkbox', [
    'label' => 'Remember me',
    'name' => 'remember',
    'value' => '1',
    'checked' => old('remember')
])

<!-- Radio button -->
@include('components.radio', [
    'label' => 'Option A',
    'name' => 'choice',
    'value' => 'a',
    'checked' => old('choice') === 'a'
])
```

---

### 7. Alerts

Display success, error, warning, or info messages.

**File:** `components/alert.blade.php`

**Examples:**

```blade
<!-- Info alert -->
@include('components.alert', [
    'type' => 'info',
    'title' => 'Information',
    'closeable' => true
])
    This is an informational message.
@endinclude

<!-- Error alert -->
@include('components.alert', [
    'type' => 'error',
    'title' => 'Error',
    'closeable' => true,
    'icon' => true
])
    Something went wrong. Please try again.
@endinclude

<!-- Success alert -->
@include('components.alert', [
    'type' => 'success',
    'title' => 'Success!',
    'closeable' => true
])
    Operation completed successfully.
@endinclude
```

**Available Types:** `success`, `error`, `warning`, `info`

---

### 8. Badges

Status badges with colors and optional dots.

**File:** `components/badge.blade.php`

**Examples:**

```blade
<!-- Simple badge -->
<x-badge type="success">Active</x-badge>

<!-- With dot indicator -->
<x-badge type="warning" dot>Pending</x-badge>

<!-- Different sizes -->
<x-badge type="error" size="sm">Error</x-badge>
<x-badge type="info" size="lg">Information</x-badge>
```

**Available Types:** `success`, `error`, `warning`, `info`, `purple`, `pink`, `indigo`, `gray`

**Sizes:** `sm`, `md`, `lg`

---

### 9. Modals

Interactive modal dialogs with Alpine.js.

**File:** `components/modal-form.blade.php`

**Example:**

```blade
<!-- Trigger button -->
<button @click="modals['createUser'] = true" class="bg-indigo-600 text-white px-4 py-2 rounded">
    Open Modal
</button>

<!-- Modal -->
@include('components.modal-form', [
    'id' => 'createUser',
    'title' => 'Create New User',
    'size' => 'lg'
])
    <form @submit.prevent="submitForm" class="space-y-4">
        @include('components.form-input', [
            'label' => 'Name',
            'name' => 'name',
            'required' => true
        ])

        @include('components.form-input', [
            'label' => 'Email',
            'name' => 'email',
            'type' => 'email',
            'required' => true
        ])
    </form>

    @slot('footer')
        <button @click="modals['createUser'] = false" type="button" class="px-4 py-2 text-gray-700 border rounded">
            Cancel
        </button>
        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded">
            Create
        </button>
    @endslot
@endinclude
```

**Push modals to stack:**

```blade
@push('modals')
    @include('components.modal-form', [...])
@endpush
```

---

### 10. Progress Bar

Visual progress indicator.

**File:** `components/progress-bar.blade.php`

**Example:**

```blade
@include('components.progress-bar', [
    'label' => 'Upload Progress',
    'percentage' => 65,
    'color' => 'indigo',
    'size' => 'md',
    'showPercentage' => true
])
```

---

### 11. Breadcrumb

Navigation path indicator.

**File:** `components/breadcrumb.blade.php`

**Example:**

```blade
@include('components.breadcrumb', [
    'items' => [
        ['label' => 'Home', 'url' => route('dashboard')],
        ['label' => 'Users', 'url' => route('users.index')],
        ['label' => 'Edit', 'url' => '#']
    ]
])
```

---

## Complete Page Example

See `resources/views/dashboard.blade.php` for a complete working example with:

- Multiple stat cards
- Summary cards
- Data table with actions
- Modal forms
- Alert messages
- Responsive layout

---

## Color Scheme

Primary colors available across all components:

- `blue` - #3B82F6
- `green` - #10B981
- `red` - #EF4444
- `purple` - #A855F7
- `indigo` - #4F46E5 (Primary)
- `yellow` - #FBBF24
- `pink` - #EC4899

---

## Responsive Breakpoints

All components use Tailwind's responsive prefixes:

- Mobile first (default)
- `md:` (768px+) - Tablets
- `lg:` (1024px+) - Desktop
- `xl:` (1280px+) - Large desktop

---

## Alpine.js Integration

The layout provides Alpine.js data:

- `modals` - Object for modal state management
- `sidebarOpen` - Sidebar visibility on mobile
- `mobileMenuOpen` - Mobile menu state

**Example:**

```blade
<button @click="modals['myModal'] = true">Open</button>

<div x-show="modals['myModal']">
    <!-- Modal content -->
</div>
```

---

## Notes

- All components use Tailwind CSS (no external CSS files)
- Icons are inline SVGs for maximum compatibility
- Components use Alpine.js for lightweight interactivity
- Responsive design is built-in by default
- Form validation integrates with Laravel's validation system
