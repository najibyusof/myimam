<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileSignatureApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_user_can_upload_signature(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->post(route('api.auth.profile.signature.upload'), [
                'signature_image' => UploadedFile::fake()->image('api-signature.png', 600, 150),
            ]);

        $response->assertOk()->assertJsonPath('message', 'Signature uploaded successfully');

        $user->refresh();

        $this->assertNotNull($user->signature_path);
        Storage::disk('public')->assertExists($user->signature_path);
    }

    public function test_api_user_can_remove_signature(): void
    {
        Storage::fake('public');

        $path = 'signature-images/api-existing-signature.png';
        Storage::disk('public')->put($path, 'sig');

        $user = User::factory()->create([
            'signature_path' => $path,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->delete(route('api.auth.profile.signature.remove'));

        $response->assertOk()->assertJsonPath('message', 'Signature removed successfully');

        $user->refresh();

        $this->assertNull($user->signature_path);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_api_update_profile_can_upload_and_remove_signature(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $uploadResponse = $this->actingAs($user, 'sanctum')
            ->patch(route('api.auth.profile.update'), [
                'name' => 'API Signature User',
                'signature_image' => UploadedFile::fake()->image('inline-signature.png', 600, 150),
            ]);

        $uploadResponse->assertOk();

        $user->refresh();
        $uploadedPath = $user->signature_path;

        $this->assertNotNull($uploadedPath);
        Storage::disk('public')->assertExists($uploadedPath);

        $removeResponse = $this->actingAs($user, 'sanctum')
            ->patch(route('api.auth.profile.update'), [
                'remove_signature_image' => true,
            ]);

        $removeResponse->assertOk();

        $user->refresh();

        $this->assertNull($user->signature_path);
        Storage::disk('public')->assertMissing($uploadedPath);
    }
}
