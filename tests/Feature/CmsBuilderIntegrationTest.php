<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckSubscription;
use App\Http\Middleware\CheckTenantActive;
use App\Models\CmsBuilderPage;
use App\Models\Masjid;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class CmsBuilderIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_route_renders_global_builder_page_when_available(): void
    {
        CmsBuilderPage::query()->create([
            'masjid_id' => null,
            'slug' => 'home',
            'title' => 'Global Home',
            'content_json' => [
                'components' => [
                    [
                        'type' => 'text',
                        'props' => [
                            'text' => 'Builder Global Home Content',
                        ],
                    ],
                ],
            ],
            'is_active' => true,
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Builder Global Home Content');
    }

    public function test_home_route_prioritizes_tenant_builder_page_over_global(): void
    {
        $masjid = Masjid::query()->create([
            'nama' => 'Masjid Ujian Tenant',
            'code' => 'ujian-tenant',
            'status' => 'active',
            'subscription_status' => 'active',
        ]);

        CmsBuilderPage::query()->create([
            'masjid_id' => null,
            'slug' => 'home',
            'title' => 'Global Home',
            'content_json' => [
                'components' => [
                    [
                        'type' => 'text',
                        'props' => [
                            'text' => 'Builder Global Fallback',
                        ],
                    ],
                ],
            ],
            'is_active' => true,
        ]);

        CmsBuilderPage::query()->create([
            'masjid_id' => $masjid->id,
            'slug' => 'home',
            'title' => 'Tenant Home',
            'content_json' => [
                'components' => [
                    [
                        'type' => 'text',
                        'props' => [
                            'text' => 'Builder Tenant Home Content',
                        ],
                    ],
                ],
            ],
            'is_active' => true,
        ]);

        $response = $this->get('/?masjid=ujian-tenant');

        $response->assertOk();
        $response->assertSee('Builder Tenant Home Content');
        $response->assertDontSee('Builder Global Fallback');
    }

    public function test_login_route_renders_builder_login_component_when_available(): void
    {
        CmsBuilderPage::query()->create([
            'masjid_id' => null,
            'slug' => 'login',
            'title' => 'Login Builder',
            'content_json' => [
                'components' => [
                    [
                        'type' => 'login_form',
                        'props' => [
                            'title' => 'Log Masuk CMS Dinamik',
                            'subtitle' => 'Masukkan maklumat akaun anda.',
                        ],
                    ],
                ],
            ],
            'is_active' => true,
        ]);

        $response = $this->get('/login');

        $response->assertOk();
        $response->assertSee('Log Masuk CMS Dinamik');
        $response->assertSee('Masukkan maklumat akaun anda.');
    }

    public function test_admin_can_save_and_load_builder_layout_for_own_masjid(): void
    {
        $this->withoutMiddleware([CheckTenantActive::class, CheckSubscription::class]);

        $permission = Permission::findOrCreate('cms.manage', 'web');
        $role = Role::query()->firstOrCreate(
            ['name' => 'Admin', 'guard_name' => 'web'],
            ['level' => 2, 'masjid_id' => null]
        );
        $role->givePermissionTo($permission);

        $masjid = Masjid::query()->create([
            'nama' => 'Masjid Admin CMS',
            'code' => 'admin-cms',
            'status' => 'active',
            'subscription_status' => 'active',
        ]);

        $admin = User::query()->create([
            'id_masjid' => $masjid->id,
            'name' => 'Admin CMS',
            'email' => 'admin.cms@masjid.com',
            'password' => 'password',
            'peranan' => 'admin',
            'aktif' => true,
            'email_verified_at' => now(),
        ]);
        $admin->assignRole($role);

        $payload = [
            'target_masjid_id' => $masjid->id,
            'title' => 'Halaman CMS Ujian',
            'is_active' => '1',
            'content_json' => json_encode([
                'components' => [
                    [
                        'type' => 'text',
                        'props' => [
                            'text' => 'Komponen Ujian Save Load',
                        ],
                    ],
                ],
            ], JSON_THROW_ON_ERROR),
        ];

        $this->actingAs($admin)
            ->put(route('admin.cms.builder.update', ['slug' => 'home']), $payload)
            ->assertRedirect();

        $this->assertDatabaseHas('cms_pages', [
            'slug' => 'home',
            'masjid_id' => $masjid->id,
            'title' => 'Halaman CMS Ujian',
            'is_active' => 1,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.cms.builder.edit', ['slug' => 'home']));

        $response->assertOk();
        $response->assertSee('Komponen Ujian Save Load');
    }

    public function test_admin_can_publish_unpublish_and_restore_previous_version(): void
    {
        $this->withoutMiddleware([CheckTenantActive::class, CheckSubscription::class]);

        $permission = Permission::findOrCreate('cms.manage', 'web');
        $role = Role::query()->firstOrCreate(
            ['name' => 'Admin', 'guard_name' => 'web'],
            ['level' => 2, 'masjid_id' => null]
        );
        $role->givePermissionTo($permission);

        $masjid = Masjid::query()->create([
            'nama' => 'Masjid Version',
            'code' => 'masjid-version',
            'status' => 'active',
            'subscription_status' => 'active',
        ]);

        $admin = User::query()->create([
            'id_masjid' => $masjid->id,
            'name' => 'Admin Version',
            'email' => 'admin.version@masjid.com',
            'password' => 'password',
            'peranan' => 'admin',
            'aktif' => true,
            'email_verified_at' => now(),
        ]);
        $admin->assignRole($role);

        $publishPayload = [
            'target_masjid_id' => $masjid->id,
            'title' => 'Halaman Publish',
            'action' => 'publish',
            'is_active' => '1',
            'content_json' => json_encode([
                'components' => [
                    [
                        'type' => 'text',
                        'props' => ['text' => 'Versi Published'],
                    ],
                ],
            ], JSON_THROW_ON_ERROR),
        ];

        $this->actingAs($admin)
            ->put(route('admin.cms.builder.update', ['slug' => 'home']), $publishPayload)
            ->assertRedirect();

        $this->assertDatabaseHas('cms_pages', [
            'masjid_id' => $masjid->id,
            'slug' => 'home',
            'is_active' => 1,
        ]);

        $unpublishPayload = [
            'target_masjid_id' => $masjid->id,
            'title' => 'Halaman Unpublish',
            'action' => 'unpublish',
            'is_active' => '0',
            'content_json' => json_encode([
                'components' => [
                    [
                        'type' => 'text',
                        'props' => ['text' => 'Versi Unpublished'],
                    ],
                ],
            ], JSON_THROW_ON_ERROR),
        ];

        $this->actingAs($admin)
            ->put(route('admin.cms.builder.update', ['slug' => 'home']), $unpublishPayload)
            ->assertRedirect();

        $this->assertDatabaseHas('cms_pages', [
            'masjid_id' => $masjid->id,
            'slug' => 'home',
            'is_active' => 0,
            'title' => 'Halaman Unpublish',
        ]);

        $publishedVersionId = (int) \DB::table('cms_page_versions')
            ->where('masjid_id', $masjid->id)
            ->where('slug', 'home')
            ->where('action', 'publish')
            ->orderByDesc('version_no')
            ->value('id');

        $this->actingAs($admin)
            ->post(route('admin.cms.builder.versions.restore', ['slug' => 'home', 'version' => $publishedVersionId]), [
                'target_masjid_id' => $masjid->id,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('cms_pages', [
            'masjid_id' => $masjid->id,
            'slug' => 'home',
            'is_active' => 1,
            'title' => 'Halaman Publish',
        ]);

        $this->assertDatabaseHas('cms_page_versions', [
            'masjid_id' => $masjid->id,
            'slug' => 'home',
            'action' => 'restore',
        ]);
    }

    public function test_admin_can_save_seo_metadata_and_public_page_renders_it(): void
    {
        $this->withoutMiddleware([CheckTenantActive::class, CheckSubscription::class]);

        $permission = Permission::findOrCreate('cms.manage', 'web');
        $role = Role::query()->firstOrCreate(
            ['name' => 'Admin', 'guard_name' => 'web'],
            ['level' => 2, 'masjid_id' => null]
        );
        $role->givePermissionTo($permission);

        $masjid = Masjid::query()->create([
            'nama' => 'Masjid SEO',
            'code' => 'masjid-seo',
            'status' => 'active',
            'subscription_status' => 'active',
        ]);

        $admin = User::query()->create([
            'id_masjid' => $masjid->id,
            'name' => 'Admin SEO',
            'email' => 'admin.seo@masjid.com',
            'password' => 'password',
            'peranan' => 'admin',
            'aktif' => true,
            'email_verified_at' => now(),
        ]);
        $admin->assignRole($role);

        $payload = [
            'target_masjid_id' => $masjid->id,
            'title' => 'Halaman SEO',
            'seo_title' => 'Portal Masjid SEO',
            'seo_meta_description' => 'Meta description untuk halaman builder tenant.',
            'is_active' => '1',
            'content_json' => json_encode([
                'components' => [
                    [
                        'type' => 'text',
                        'props' => [
                            'text' => 'Kandungan SEO Tenant',
                        ],
                    ],
                ],
            ], JSON_THROW_ON_ERROR),
        ];

        $this->actingAs($admin)
            ->put(route('admin.cms.builder.update', ['slug' => 'home']), $payload)
            ->assertRedirect();

        $this->assertDatabaseHas('cms_pages', [
            'masjid_id' => $masjid->id,
            'slug' => 'home',
            'seo_title' => 'Portal Masjid SEO',
            'seo_meta_description' => 'Meta description untuk halaman builder tenant.',
        ]);

        $response = $this->get('/?masjid=masjid-seo');

        $response->assertOk();
        $response->assertSee('<title>Portal Masjid SEO</title>', false);
        $response->assertSee('<meta name="description" content="Meta description untuk halaman builder tenant.">', false);
        $response->assertSee('Kandungan SEO Tenant');
    }

    public function test_admin_can_upload_and_list_media_library_items(): void
    {
        $this->withoutMiddleware([CheckTenantActive::class, CheckSubscription::class]);
        Storage::fake('public');

        $permission = Permission::findOrCreate('cms.manage', 'web');
        $role = Role::query()->firstOrCreate(
            ['name' => 'Admin', 'guard_name' => 'web'],
            ['level' => 2, 'masjid_id' => null]
        );
        $role->givePermissionTo($permission);

        $masjid = Masjid::query()->create([
            'nama' => 'Masjid Media',
            'code' => 'masjid-media',
            'status' => 'active',
            'subscription_status' => 'active',
        ]);

        $admin = User::query()->create([
            'id_masjid' => $masjid->id,
            'name' => 'Admin Media',
            'email' => 'admin.media@masjid.com',
            'password' => 'password',
            'peranan' => 'admin',
            'aktif' => true,
            'email_verified_at' => now(),
        ]);
        $admin->assignRole($role);

        $uploadResponse = $this->actingAs($admin)
            ->post(route('admin.cms.builder.media.upload'), [
                'image' => UploadedFile::fake()->image('banner.jpg', 1200, 600),
            ]);

        $uploadResponse->assertOk();
        $uploadResponse->assertJsonStructure(['url', 'path']);

        $path = $uploadResponse->json('path');
        Storage::disk('public')->assertExists($path);

        $libraryResponse = $this->actingAs($admin)
            ->get(route('admin.cms.builder.media.index'));

        $libraryResponse->assertOk();
        $libraryResponse->assertJsonPath('items.0.path', $path);
        $libraryResponse->assertJsonPath('items.0.name', basename($path));
    }
}
