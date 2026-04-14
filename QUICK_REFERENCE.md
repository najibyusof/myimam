# Quick Reference Guide - Premium UI Components

## 🚀 Start Here

### 1. Basic Page Template

```blade
@extends('layouts.app-premium')

@section('content')
    <!-- Your content here -->
@endsection
```

---

## 📋 Component Quick Reference

### Stat Card (Metrics)

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

**Colors:** blue | green | red | purple | indigo | yellow | pink

---

### Summary Card (Info List)

```blade
<x-summary-card
    title="Summary Title"
    color="indigo"
    :items="[
        ['label' => 'Item 1', 'value' => 'Value 1'],
        ['label' => 'Item 2', 'value' => 'Value 2'],
    ]"
    :action="['label' => 'Button', 'url' => '#']"
/>
```

---

### Data Table (Lists)

```blade
@include('components.data-table', [
    'title' => 'Users',
    'columns' => [
        ['key' => 'name', 'label' => 'Name', 'type' => 'avatar'],
        ['key' => 'email', 'label' => 'Email'],
        ['key' => 'status', 'label' => 'Status', 'type' => 'badge'],
    ],
    'rows' => $users,
    'actions' => [
        ['type' => 'link', 'label' => 'Edit', 'url' => fn($row) => '#'],
        ['type' => 'button', 'label' => 'Delete'],
    ]
])
```

**Column Types:** avatar | badge | link | date | text

---

### Button

```blade
<x-button type="primary" size="md">Click Me</x-button>
<x-button type="danger" size="lg">Delete</x-button>
<x-button type="link" href="{{ route('home') }}">Home</x-button>
```

**Types:** primary | secondary | danger | success | warning | ghost | link  
**Sizes:** sm | md | lg | xl

---

### Form Input

```blade
@include('components.form-input', [
    'label' => 'Email',
    'name' => 'email',
    'type' => 'email',
    'placeholder' => 'user@example.com',
    'required' => true,
    'error' => $errors->first('email')
])
```

**Types:** text | email | password | number | date | textarea | select

---

### Checkbox & Radio

```blade
@include('components.checkbox', [
    'label' => 'Remember me',
    'name' => 'remember',
    'checked' => true
])

@include('components.radio', [
    'label' => 'Option A',
    'name' => 'choice',
    'value' => 'a'
])
```

---

### Alert

```blade
@include('components.alert', [
    'type' => 'success',
    'title' => 'Success!',
    'closeable' => true
])
    Operation completed successfully.
@endinclude
```

**Types:** success | error | warning | info

---

### Badge

```blade
<x-badge type="success">Active</x-badge>
<x-badge type="warning" dot>Pending</x-badge>
<x-badge type="error" size="lg">Error</x-badge>
```

**Types:** success | error | warning | info | purple | pink | indigo | gray  
**Sizes:** sm | md | lg

---

### Progress Bar

```blade
@include('components.progress-bar', [
    'label' => 'Upload',
    'percentage' => 65,
    'color' => 'indigo'
])
```

---

### Breadcrumb

```blade
@include('components.breadcrumb', [
    'items' => [
        ['label' => 'Home', 'url' => route('home')],
        ['label' => 'Dashboard', 'url' => route('dashboard')],
    ]
])
```

---

### Modal

```blade
<!-- Button to open -->
<button @click="modals['createUser'] = true">Create User</button>

<!-- Modal definition -->
@push('modals')
@include('components.modal-form', [
    'id' => 'createUser',
    'title' => 'Create New User',
    'size' => 'lg'
])
    <!-- Modal content -->
    @include('components.form-input', [
        'label' => 'Name',
        'name' => 'name',
        'required' => true
    ])

    @slot('footer')
        <button @click="modals['createUser'] = false">Cancel</button>
        <button type="submit">Create</button>
    @endslot
@endinclude
@endpush
```

**Sizes:** sm | md | lg | xl | 2xl

---

## 🎨 Common Patterns

### Dashboard with Stats

```blade
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
    <x-stat-card ... />
    <x-stat-card ... />
    <!-- ... -->
</div>
```

### Data Management Page

```blade
<!-- Search & Filters -->
<!-- Data Table with actions -->
<!-- Stats below -->

@push('modals')
    <!-- Create/Edit Modal -->
@endpush
```

### Form Page

```blade
<form @submit.prevent="submit">
    @include('components.form-input', [...])
    @include('components.form-input', [...])
    @include('components.checkbox', [...])

    <x-button type="primary">Submit</x-button>
</form>
```

---

## 🔧 Alpine.js Interactivity

### Opening Modals

```html
<button @click="modals['myModal'] = true">Open</button>
```

### Closing Modals

```html
<button @click="modals['myModal'] = false">Close</button>
```

### Conditional Display

```html
<div x-show="modals['myModal']">Modal content</div>
```

### Toggle States

```html
<button @click="sidebarOpen = !sidebarOpen">Toggle Sidebar</button>
```

---

## 📱 Responsive Classes

```blade
<!-- Hidden on mobile, visible on tablet+ -->
<div class="hidden md:block">Tablet content</div>

<!-- 1 column mobile, 2 columns tablet, 3 columns desktop -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
    <!-- items -->
</div>

<!-- Flex responsive -->
<div class="flex flex-col md:flex-row gap-4">
    <!-- items -->
</div>
```

---

## 🎨 Tailwind Utility Classes Reference

### Spacing

```
p-4 (padding), m-4 (margin), gap-6 (grid gap)
pt-0, pb-4, px-6, py-8 (directional)
```

### Sizing

```
w-full, w-1/2, h-screen, max-w-2xl
```

### Colors

```
bg-indigo-600, text-gray-700, border-gray-300
hover:bg-indigo-700, focus:ring-indigo-500
```

### Display

```
flex, grid, hidden, block, inline-block
md:flex, lg:grid (responsive)
```

### Positioning

```
absolute, relative, fixed, sticky
top-0, left-0, inset-0
```

---

## 🌈 Color Naming

```
indigo-50, indigo-100, ..., indigo-900
blue-600, green-500, red-400, etc.

Usage:
bg-blue-500      (background)
text-blue-600    (text)
border-blue-300  (border)
hover:bg-blue-600 (hover state)
```

---

## ✅ Common Tasks

### Add Search Box

```blade
<input type="search" placeholder="Search..."
       class="px-4 py-2 border border-gray-300 rounded-lg">
```

### Show Loading State

```blade
<x-button type="primary" :loading="$isLoading">Submit</x-button>
```

### Display Validation Errors

```blade
@include('components.form-input', [
    'error' => $errors->first('email')
])
```

### Create Dropdown Select

```blade
@include('components.form-input', [
    'label' => 'Category',
    'name' => 'category',
    'type' => 'select',
    'required' => true
])
    <option value="">-- Select --</option>
    <option value="cat1">Category 1</option>
@endinclude
```

### Show Permission-Only Content

```blade
@can('users.manage')
    <!-- Admin only content -->
@endcan
```

---

## 📊 Layout Grid Examples

### 1 Column (Mobile)

```
[Full Width]
```

### 2 Columns (Tablet - md:)

```
[Col 1] [Col 2]
```

### 4 Columns (Desktop - lg:)

```
[C1] [C2] [C3] [C4]
```

### Mixed Layout

```blade
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
    <!-- Items -->
</div>
```

---

## 🔐 Permission Integration

```blade
<!-- Show only if user can -->
@can('users.view')
    <x-nav-item label="Users" href="{{ route('users.index') }}" />
@endcan

<!-- Show for all except specific permission -->
@cannot('admin')
    Regular user content
@endcannot
```

---

## 📌 File Locations

```
Components:  resources/views/components/
Layouts:     resources/views/layouts/
Pages:       resources/views/pages/
Examples:    resources/views/dashboard.blade.php
Docs:        resources/views/*.md
```

---

## 🎯 Performance Tips

1. Use component composition (reuse components)
2. Lazy load tables with pagination
3. Optimize Tailwind CSS for production
4. Cache compiled views
5. Minimize Alpine.js operations
6. Use CDN for icons if needed

---

## 🐛 Troubleshooting

### Modal Not Opening?

- Check if modals object is defined
- Verify button has `@click="modals['id'] = true"`
- Check browser console for errors

### Styling Not Applied?

- Ensure Tailwind CSS is compiled
- Check class names are spelled correctly
- Run `npm run prod` after changes

### Table Not Showing?

- Verify `rows` prop is not empty
- Check column `key` matches data keys
- Ensure data format is correct

### Form Input Not Appearing?

- Check name and label are provided
- Verify within valid container
- Check for validation errors hiding input

---

## 📚 Complete Documentation

For more details, see:

- **COMPONENT_GUIDE.md** - Full API reference
- **COMPONENT_INTEGRATION_GUIDE.md** - Integration patterns
- **IMPLEMENTATION_SUMMARY.md** - Complete overview

---

## 💡 Example: Complete Page

```blade
@extends('layouts.app-premium')

@section('content')
    <!-- Breadcrumb -->
    @include('components.breadcrumb', ['items' => [...]])

    <!-- Page Header -->
    <h1>Page Title</h1>

    <!-- Stats Row -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <x-stat-card title="Total" value="2,543" color="blue" />
        <!-- More cards... -->
    </div>

    <!-- Data Table -->
    @include('components.data-table', [
        'columns' => [...],
        'rows' => $data
    ])

    <!-- Alerts -->
    @include('components.alert', [
        'type' => 'success',
        'title' => 'Success!'
    ])
        Operation completed.
    @endinclude
@endsection

@push('modals')
    <!-- Modals here -->
@endpush
```

---

**Ready to build? Start with the basic page template above! 🚀**
