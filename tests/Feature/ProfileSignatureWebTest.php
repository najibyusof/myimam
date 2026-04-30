<?php

namespace Tests\Feature;

use App\Models\Masjid;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileSignatureWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_upload_signature_image_from_web_profile(): void
    {
        Storage::fake('public');

        $masjid = Masjid::query()->create([
            'nama' => 'Masjid Profile Signature',
            'status' => 'active',
            'subscription_status' => 'active',
            'subscription_expiry' => now()->addDays(30),
        ]);

        $user = User::factory()->create([
            'id_masjid' => $masjid->id,
            'aktif' => true,
        ]);

        $response = $this->actingAs($user)
            ->patch('/profile', [
                'name' => 'Signature User',
                'email' => $user->email,
                'signature_image' => UploadedFile::fake()->image('signature.png', 600, 150),
            ]);

        $response->assertSessionHasNoErrors()->assertRedirect('/profile');

        $user->refresh();

        $this->assertNotNull($user->signature_path);
        Storage::disk('public')->assertExists($user->signature_path);
    }

    public function test_user_can_remove_signature_image_from_web_profile(): void
    {
        Storage::fake('public');

        $path = 'signature-images/existing-signature.png';
        Storage::disk('public')->put($path, 'sig');

        $masjid = Masjid::query()->create([
            'nama' => 'Masjid Profile Signature 2',
            'status' => 'active',
            'subscription_status' => 'active',
            'subscription_expiry' => now()->addDays(30),
        ]);

        $user = User::factory()->create([
            'id_masjid' => $masjid->id,
            'aktif' => true,
            'signature_path' => $path,
        ]);

        $response = $this->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'remove_signature_image' => '1',
            ]);

        $response->assertSessionHasNoErrors()->assertRedirect('/profile');

        $user->refresh();

        $this->assertNull($user->signature_path);
        Storage::disk('public')->assertMissing($path);
    }
}
