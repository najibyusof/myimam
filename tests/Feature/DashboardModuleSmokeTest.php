<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardModuleSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_module_system_overview_renders_core_sections(): void
    {
        $user = User::factory()->create([
            'aktif' => true,
            'email_verified_at' => now(),
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('dashboard'));

        $response
            ->assertOk()
            ->assertSee('System Overview')
            ->assertSee('Dashboard Module')
            ->assertSee('Role focus: User')
            ->assertSee('My Unread Notifications')
            ->assertSee('My Activities (7d)')
            ->assertSee('System Alerts In Scope')
            ->assertSee('Recent Activity Log')
            ->assertSee('Notification Preview');
    }
}
