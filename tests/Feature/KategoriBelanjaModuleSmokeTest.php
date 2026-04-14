<?php

namespace Tests\Feature;

use App\Models\KategoriBelanja;
use App\Models\Masjid;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class KategoriBelanjaModuleSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_kategori_belanja_module_and_perform_crud_flow(): void
    {
        $permissions = [
            'kategori_belanja.view',
            'kategori_belanja.create',
            'kategori_belanja.update',
            'kategori_belanja.delete',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
        }

        $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $adminRole->givePermissionTo($permissions);

        $masjid = Masjid::create([
            'nama' => 'Masjid Ujian Kategori Belanja',
            'alamat' => 'Jalan Ujian 1',
            'daerah' => 'Kuala Lumpur',
            'negeri' => 'Wilayah Persekutuan',
            'no_pendaftaran' => 'MSJ-KAT-001',
            'status' => 'active',
            'subscription_status' => 'active',
            'subscription_expiry' => now()->addMonth(),
        ]);

        $admin = User::create([
            'name' => 'Admin Kategori Belanja',
            'email' => 'admin.kategori.' . Str::random(6) . '@example.test',
            'password' => Hash::make('password'),
            'id_masjid' => $masjid->id,
            'aktif' => true,
        ]);
        $admin->assignRole($adminRole);

        $indexResponse = $this->actingAs($admin)->get(route('admin.kategori-belanja.index'));
        $indexResponse->assertOk();
        $indexResponse->assertSee('Kategori Belanja');

        $createResponse = $this->actingAs($admin)->post(route('admin.kategori-belanja.store'), [
            'id_masjid' => $masjid->id,
            'kod' => 'KB-UTIL',
            'nama_kategori' => 'Utiliti Masjid',
            'aktif' => true,
        ]);

        $kategoriBelanja = KategoriBelanja::where('id_masjid', $masjid->id)->where('kod', 'KB-UTIL')->firstOrFail();

        $createResponse->assertRedirect(route('admin.kategori-belanja.edit', $kategoriBelanja));
        $this->assertDatabaseHas('kategori_belanja', [
            'id_masjid' => $masjid->id,
            'kod' => 'KB-UTIL',
            'nama_kategori' => 'Utiliti Masjid',
            'aktif' => true,
        ]);

        $editResponse = $this->actingAs($admin)->put(route('admin.kategori-belanja.update', $kategoriBelanja), [
            'id_masjid' => $masjid->id,
            'kod' => 'KB-UTIL-UPD',
            'nama_kategori' => 'Utiliti Dan Penyelenggaraan',
            'aktif' => true,
        ]);

        $editResponse->assertRedirect(route('admin.kategori-belanja.edit', $kategoriBelanja));
        $this->assertDatabaseHas('kategori_belanja', [
            'id' => $kategoriBelanja->id,
            'kod' => 'KB-UTIL-UPD',
            'nama_kategori' => 'Utiliti Dan Penyelenggaraan',
        ]);

        $toggleResponse = $this->actingAs($admin)->patch(route('admin.kategori-belanja.status', $kategoriBelanja));
        $toggleResponse->assertRedirect(route('admin.kategori-belanja.index'));

        $this->assertDatabaseHas('kategori_belanja', [
            'id' => $kategoriBelanja->id,
            'aktif' => false,
        ]);

        $deleteResponse = $this->actingAs($admin)->delete(route('admin.kategori-belanja.destroy', $kategoriBelanja));
        $deleteResponse->assertRedirect(route('admin.kategori-belanja.index'));

        $this->assertDatabaseMissing('kategori_belanja', [
            'id' => $kategoriBelanja->id,
        ]);
    }
}
