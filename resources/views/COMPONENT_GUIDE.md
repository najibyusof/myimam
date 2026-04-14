<!-- Component Usage Guide -->

# Premium UI Component Library

A comprehensive Tailwind CSS component library for Laravel IMAM Dashboard.

## Quick Start

All components are located in `resources/views/components/` and automatically loaded as Blade components.

## Available Components

### 1. Stat Card

Displays key metrics with optional trend indicators.

```blade
<x-stat-card
    title="Total Users"
    value="2,543"
    subtitle="Active users"
    :trend="['direction' => 'up', 'value' => 12]"
    color="blue"
    icon="<svg>...</svg>"
/>
```

**Props:**

- `title` (string) - Card title
- `value` (string|number) - Main value to display
- `subtitle` (string, optional) - Subtitle text
- `trend` (array, optional) - Trend data: `['direction' => 'up|down', 'value' => number]`
- `color` (string) - Color: `blue`, `green`, `red`, `purple`, `indigo`
- `icon` (string, optional) - SVG icon HTML

---

### 2. Summary Card

Displays a list of summary items with header and optional action button.

```blade
<x-summary-card
    title="Financial Summary"
    color="blue"
    icon="<svg>...</svg>"
    :items="[
        ['label' => 'Total Revenue', 'value' => 'RM 125,450'],
        ['label' => 'Total Expenses', 'value' => 'RM 89,230'],
    ]"
    :action="['label' => 'View Report', 'url' => route('reports.index')]"
/>
```

**Props:**

- `title` (string) - Card title
- `color` (string) - Color: `blue`, `green`, `red`, `purple`, `indigo`
- `icon` (string, optional) - SVG icon HTML
- `items` (array, optional) - List of `['label' => '', 'value' => '']`
- `action` (array, optional) - Action button: `['label' => '', 'url' => '']`

---

### 3. Data Table

Responsive table with search, filters, and actions.

```blade
<x-data-table
    title="Recent Users"
    :columns="[
        ['key' => 'name', 'label' => 'Name', 'type' => 'avatar'],
        ['key' => 'email', 'label' => 'Email'],
        ['key' => 'status', 'label' => 'Status', 'type' => 'badge'],
        ['key' => 'joined', 'label' => 'Joined', 'type' => 'date'],
    ]"
    :rows="$users"
    :actions="[
        ['type' => 'link', 'label' => 'Edit', 'url' => fn($row) => route('users.edit', $row['id'])],
        ['type' => 'button', 'label' => 'Delete'],
    ]"
/>
```

**Props:**

- `title` (string) - Table title
- `columns` (array) - Column definitions
    - `key` - Data key
    - `label` - Column header
    - `type` - `avatar`, `badge`, `link`, `date`, or plain text
- `rows` (array) - Table data rows
- `searchable` (bool, default: true) - Show search box
- `filterable` (bool, default: false) - Show filter dropdown
- `actions` (array, optional) - Action buttons per row

**Column Types:**

- `avatar` - Shows initials in circle with name
- `badge` - Status badge with auto-coloring
- `link` - Clickable link with specified route
- `date` - Formatted datetime
- Default - Plain text

---

### 4. Button

Versatile button component with multiple styles and sizes.

```blade
<x-button type="primary" size="md" @click="openModal">
    Click Me
</x-button>

<x-button type="danger" size="lg" icon="<svg>...</svg>" iconPosition="left">
    Delete
</x-button>

<x-button type="link" href="{{ route('home') }}">
    Go Home
</x-button>
```

**Props:**

- `type` - `primary`, `secondary`, `danger`, `success`, `warning`, `ghost`, `link`
- `size` - `sm`, `md`, `lg`, `xl`
- `href` (optional) - Link URL (renders as anchor tag)
- `disabled` (bool) - Disable button
- `loading` (bool) - Show spinner and disable
- `icon` (string, optional) - SVG icon
- `iconPosition` - `left` or `right`

---

### 5. Form Input

Text input with validation support.

```blade
<x-form-input
    label="Email Address"
    name="email"
    type="email"
    value="{{ old('email') }}"
    placeholder="you@example.com"
    required
    :error="$errors->first('email')"
    help="We'll never share your email"
    icon="<svg>...</svg>"
/>
```

**Props:**

- `label` (string) - Input label
- `name` (string) - Input name
- `type` - `text`, `email`, `password`, `number`, `date`, `textarea`, `select`
- `value` (string) - Input value
- `placeholder` (string) - Placeholder text
- `required` (bool) - Mark as required
- `disabled` (bool) - Disable input
- `error` (string, optional) - Error message
- `help` (string, optional) - Help text
- `icon` (string, optional) - SVG icon

---

### 6. Checkbox

Checkbox input with label.

```blade
<x-checkbox
    label="Remember me"
    name="remember"
    value="1"
    :checked="old('remember')"
/>
```

**Props:**

- `label` (string) - Label text
- `name` (string) - Input name
- `value` - Input value
- `checked` (bool) - Is checked
- `disabled` (bool) - Is disabled
- `error` (optional) - Error message

---

### 7. Radio

Radio button with label.

```blade
<x-radio label="Option A" name="choice" value="a" />
<x-radio label="Option B" name="choice" value="b" />
```

---

### 8. Alert

Alert message box with optional close button.

```blade
<x-alert type="success" title="Success!" closeable>
    Operation completed successfully.
</x-alert>

<x-alert type="error" title="Error" closeable>
    Something went wrong.
</x-alert>
```

**Props:**

- `type` - `success`, `error`, `warning`, `info`
- `title` (string) - Alert title
- `closeable` (bool) - Show close button
- `icon` (bool, default: true) - Show icon

---

### 9. Badge

Status badge component.

```blade
<x-badge type="success" size="md">Active</x-badge>
<x-badge type="warning" dot>Pending</x-badge>
<x-badge type="error">Inactive</x-badge>
```

**Props:**

- `type` - `success`, `error`, `warning`, `info`, `purple`, `pink`, `indigo`, `gray`
- `size` - `sm`, `md`, `lg`
- `dot` (bool) - Show colored dot

---

### 10. Progress Bar

Progress bar with percentage.

```blade
<x-progress-bar
    label="Upload Progress"
    :percentage="65"
    color="indigo"
    size="md"
    showPercentage
/>
```

---

### 11. Breadcrumb

Navigation breadcrumb.

```blade
<x-breadcrumb :items="[
    ['label' => 'Home', 'url' => route('home')],
    ['label' => 'Dashboard', 'url' => route('dashboard')],
]" />
```

---

### 12. Modal

Modal dialog with Alpine.js integration.

```blade
<x-modal id="createUser" title="Create New User" size="lg">
    <form @submit.prevent="submitForm">
        <!-- Form content -->
    </form>
    @slot('footer')
        <button @click="modals['createUser'] = false" class="px-4 py-2 text-gray-700 border rounded">
            Cancel
        </button>
        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded">
            Create
        </button>
    @endslot
</x-modal>

<!-- Trigger button -->
<button @click="modals['createUser'] = true">Open Modal</button>
```

---

## Layout Integration

### Main App Layout

The `app-premium.blade.php` layout provides:

- Responsive sidebar navigation
- Sticky top navigation bar
- Alpine.js state management for modals and UI state
- Main content area

```blade
@extends('layouts.app-premium')

@section('content')
    <!-- Your page content -->
@endsection
```

---

## Color Palette

Primary colors: `indigo`, `blue`, `green`, `red`, `purple`, `yellow`, `pink`

Each component supports these colors and applies appropriate shading.

---

## Responsive Design

All components are mobile-first and responsive:

- Mobile: Full width, stacked layout
- Tablet (md: 768px): Two-column grids
- Desktop (lg: 1024px): Multi-column layouts

---

## Accessibility

Components include:

- Semantic HTML
- ARIA labels where needed
- Proper focus management
- Keyboard navigation support (modals close with Escape)

---

## Examples

See `dashboard-premium.blade.php` for complete working example with all components integrated.
