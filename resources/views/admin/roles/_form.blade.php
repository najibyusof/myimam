@php
    $roleModel = $role ?? null;
    $selectedPermissions = old('permissions', $selectedPermissions ?? []);
@endphp

<div class="space-y-6">
    <div>
        <x-input-label for="name" :value="__('Role Name')" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $roleModel?->name ?? '')"
            required autofocus />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>

    <div>
        <div class="flex items-center justify-between gap-4">
            <x-input-label :value="__('Permissions Matrix')" />
            <button type="button" id="select-all-permissions"
                class="text-sm font-medium text-indigo-600 hover:text-indigo-500">Select all</button>
        </div>

        <div class="mt-3 grid gap-4 md:grid-cols-2">
            @foreach ($permissionGroups as $groupName => $permissions)
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="mb-3 flex items-center justify-between">
                        <h4 class="text-sm font-semibold uppercase tracking-wide text-slate-700">{{ $groupName }}</h4>
                        <button type="button" data-group="{{ $loop->index }}"
                            class="group-toggle text-xs font-medium text-indigo-600 hover:text-indigo-500">Toggle group</button>
                    </div>

                    <div class="space-y-2">
                        @foreach ($permissions as $permission)
                            <label class="flex items-center gap-2 text-sm text-slate-700">
                                <input type="checkbox" name="permissions[]" value="{{ $permission['name'] }}"
                                    data-group-item="{{ $loop->parent->index }}" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                    @checked(in_array($permission['name'], $selectedPermissions, true))>
                                <span>{{ $permission['name'] }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <x-input-error class="mt-2" :messages="$errors->get('permissions')" />
        <x-input-error class="mt-2" :messages="$errors->get('permissions.*')" />
    </div>
</div>

@once
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectAllBtn = document.getElementById('select-all-permissions');
            const allCheckboxes = document.querySelectorAll('input[name="permissions[]"]');

            if (selectAllBtn) {
                selectAllBtn.addEventListener('click', function() {
                    const hasUnchecked = Array.from(allCheckboxes).some((checkbox) => !checkbox.checked);
                    allCheckboxes.forEach((checkbox) => {
                        checkbox.checked = hasUnchecked;
                    });
                });
            }

            document.querySelectorAll('.group-toggle').forEach((button) => {
                button.addEventListener('click', function() {
                    const group = this.getAttribute('data-group');
                    const groupCheckboxes = document.querySelectorAll(`[data-group-item="${group}"]`);
                    const hasUnchecked = Array.from(groupCheckboxes).some((checkbox) => !checkbox.checked);

                    groupCheckboxes.forEach((checkbox) => {
                        checkbox.checked = hasUnchecked;
                    });
                });
            });
        });
    </script>
@endonce
