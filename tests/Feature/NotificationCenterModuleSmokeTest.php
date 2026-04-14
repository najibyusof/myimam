<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationCenterModuleSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_center_lists_and_marks_notifications(): void
    {
        $user = User::factory()->create([
            'aktif' => true,
            'email_verified_at' => now(),
        ]);

        $otherUser = User::factory()->create([
            'aktif' => true,
            'email_verified_at' => now(),
        ]);

        $notification = $user->appNotifications()->create([
            'type' => 'App\\Notifications\\FinanceNotification',
            'data' => [
                'title' => 'Payment approved',
                'message' => 'Your payment request has been approved.',
                'category' => 'finance',
            ],
            'read_at' => null,
        ]);

        $otherUser->appNotifications()->create([
            'type' => 'App\\Notifications\\ApprovalNotification',
            'data' => [
                'title' => 'Not visible',
                'message' => 'Should not appear for other users.',
                'category' => 'approval',
            ],
            'read_at' => null,
        ]);

        $this->actingAs($user)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('In-App Notifications')
            ->assertSee('Payment approved')
            ->assertDontSee('Not visible')
            ->assertSee('Finance');

        $this->actingAs($user)
            ->patch(route('notifications.read', $notification->id))
            ->assertRedirect();

        $this->assertNotNull($notification->fresh()->read_at);

        $this->actingAs($user)
            ->patch(route('notifications.unread', $notification->id))
            ->assertRedirect();

        $this->assertNull($notification->fresh()->read_at);
    }
}
