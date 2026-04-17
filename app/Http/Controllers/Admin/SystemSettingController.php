<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SystemSettingController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()?->peranan === 'superadmin', 403);

        $settings = DB::table('system_settings')
            ->pluck('value', 'key')
            ->toArray();

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()?->peranan === 'superadmin', 403);

        $validated = $request->validate([
            'landing_page_mode' => ['required', 'string', 'in:cms,static'],
        ]);

        foreach ($validated as $key => $value) {
            DB::table('system_settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $value, 'updated_at' => now(), 'created_at' => now()]
            );

            cache()->forget("setting_{$key}");
        }

        return redirect()
            ->route('admin.settings.index')
            ->with('status', 'Tetapan sistem telah dikemas kini.');
    }
}
