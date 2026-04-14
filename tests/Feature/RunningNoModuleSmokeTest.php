<?php

namespace Tests\Feature;

use App\Models\Masjid;
use App\Models\RunningNo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RunningNoModuleSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_running_no_module_generate_increment_scoping_and_edit_flow(): void
    {
        // --- roles & permissions ---
        $permissions = collect([
            'running_no.view',
            'running_no.generate',
            'running_no.update',
        ])->map(fn (string $name) => Permission::query()->firstOrCreate([
            'name'       => $name,
            'guard_name' => 'web',
        ]));

        $bendahariRole = Role::query()->firstOrCreate(['name' => 'Bendahari', 'guard_name' => 'web']);
        $bendahariRole->syncPermissions($permissions);

        $ajkRole = Role::query()->firstOrCreate(['name' => 'AJK', 'guard_name' => 'web']);
        $ajkRole->syncPermissions([$permissions->firstWhere('name', 'running_no.view')]);

        $userRole = Role::query()->firstOrCreate(['name' => 'User', 'guard_name' => 'web']);
        $userRole->syncPermissions([]);

        // --- fixtures ---
        $masjidA = Masjid::query()->create(['nama' => 'Masjid Running A']);
        $masjidB = Masjid::query()->create(['nama' => 'Masjid Running B']);

        $bendahari = User::query()->create([
            'name'      => 'Bendahari Running',
            'email'     => 'bendahari.running@example.test',
            'password'  => 'password',
            'id_masjid' => $masjidA->id,
            'aktif'     => true,
        ]);
        $bendahari->assignRole($bendahariRole);

        $ajk = User::query()->create([
            'name'      => 'AJK Running',
            'email'     => 'ajk.running@example.test',
            'password'  => 'password',
            'id_masjid' => $masjidA->id,
            'aktif'     => true,
        ]);
        $ajk->assignRole($ajkRole);

        $regularUser = User::query()->create([
            'name'      => 'User Biasa',
            'email'     => 'user.running@example.test',
            'password'  => 'password',
            'id_masjid' => $masjidA->id,
            'aktif'     => true,
        ]);
        $regularUser->assignRole($userRole);

        // Seed a counter for masjidA
        $counterA = RunningNo::query()->create([
            'id_masjid' => $masjidA->id,
            'prefix'    => 'RMT',
            'tahun'     => 2026,
            'bulan'     => 4,
            'last_no'   => 5,
        ]);

        // Seed a counter for masjidB (should be hidden from masjidA bendahari)
        RunningNo::query()->create([
            'id_masjid' => $masjidB->id,
            'prefix'    => 'RMT',
            'tahun'     => 2026,
            'bulan'     => 4,
            'last_no'   => 99,
        ]);

        // --- index: accessible to Bendahari ---
        $this->actingAs($bendahari)
            ->get(route('admin.running-no.index'))
            ->assertOk()
            ->assertSee('RMT');

        // --- index: masjid scoping (masjidB counter hidden) ---
        $response = $this->actingAs($bendahari)
            ->get(route('admin.running-no.index'));
        $response->assertOk();
        $content = $response->getContent();
        $this->assertStringContainsString('RMT-2604-005', $content);
        $this->assertStringNotContainsString('RMT-2604-099', $content);

        // --- index: blocked for regular User ---
        $this->actingAs($regularUser)
            ->get(route('admin.running-no.index'))
            ->assertForbidden();

        // --- index: prefix filter ---
        $this->actingAs($bendahari)
            ->get(route('admin.running-no.index', ['prefix' => 'RMT']))
            ->assertOk()
            ->assertSee('RMT');

        // --- generate form: accessible to Bendahari ---
        $this->actingAs($bendahari)
            ->get(route('admin.running-no.generate'))
            ->assertOk()
            ->assertSee('Jana Nombor Rujukan');

        // --- generate form: blocked for AJK (no generate permission) ---
        $this->actingAs($ajk)
            ->get(route('admin.running-no.generate'))
            ->assertForbidden();

        // --- generate: first call increments from 5 to 6 ---
        $this->actingAs($bendahari)
            ->post(route('admin.running-no.generate.post'), [
                'prefix' => 'RMT',
                'tahun'  => 2026,
                'bulan'  => 4,
            ])
            ->assertOk()
            ->assertSee('RMT-2604-006');

        $this->assertDatabaseHas('running_no', [
            'id_masjid' => $masjidA->id,
            'prefix' => 'RMT',
            'tahun' => 2026,
            'bulan' => 4,
            'last_no' => 6,
        ]);

        // --- generate: second call increments to 7 ---
        $this->actingAs($bendahari)
            ->post(route('admin.running-no.generate.post'), [
                'prefix' => 'RMT',
                'tahun'  => 2026,
                'bulan'  => 4,
            ])
            ->assertOk()
            ->assertSee('RMT-2604-007');

        $this->assertDatabaseHas('running_no', [
            'id_masjid' => $masjidA->id,
            'prefix' => 'RMT',
            'tahun' => 2026,
            'bulan' => 4,
            'last_no' => 7,
        ]);

        // --- generate: new period auto-creates counter starting at 1 ---
        $this->actingAs($bendahari)
            ->post(route('admin.running-no.generate.post'), [
                'prefix' => 'RMT',
                'tahun'  => 2026,
                'bulan'  => 5,
            ])
            ->assertOk()
            ->assertSee('RMT-2605-001');

        $this->assertDatabaseHas('running_no', [
            'id_masjid' => $masjidA->id,
            'prefix'    => 'RMT',
            'tahun'     => 2026,
            'bulan'     => 5,
            'last_no'   => 1,
        ]);

        // --- generate: prefix validation ---
        $this->actingAs($bendahari)
            ->post(route('admin.running-no.generate.post'), [
                'prefix' => '',
                'tahun'  => 2026,
                'bulan'  => 4,
            ])
            ->assertSessionHasErrors('prefix');

        // --- generate: prefix with invalid chars rejected ---
        $this->actingAs($bendahari)
            ->post(route('admin.running-no.generate.post'), [
                'prefix' => 'RM T!',
                'tahun'  => 2026,
                'bulan'  => 4,
            ])
            ->assertSessionHasErrors('prefix');

        // --- edit page loads ---
        $this->actingAs($bendahari)
            ->get(route('admin.running-no.edit', [$masjidA->id, 'RMT', 2026, 4]))
            ->assertOk()
            ->assertSee('Kaunter Terakhir');

        // --- edit: cross-masjid access blocked (403) ---
        $this->actingAs($bendahari)
            ->get(route('admin.running-no.edit', [$masjidB->id, 'RMT', 2026, 4]))
            ->assertForbidden();

        // --- update last_no ---
        $this->actingAs($bendahari)
            ->put(route('admin.running-no.update', [$masjidA->id, 'RMT', 2026, 4]), [
                'last_no' => 0,
            ])
            ->assertRedirect(route('admin.running-no.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('running_no', [
            'id_masjid' => $masjidA->id,
            'prefix'    => 'RMT',
            'tahun'     => 2026,
            'bulan'     => 4,
            'last_no'   => 0,
        ]);

        // --- update: negative value rejected ---
        $this->actingAs($bendahari)
            ->put(route('admin.running-no.update', [$masjidA->id, 'RMT', 2026, 4]), [
                'last_no' => -1,
            ])
            ->assertSessionHasErrors('last_no');

        // --- after reset, generate starts from 1 again ---
        $this->actingAs($bendahari)
            ->post(route('admin.running-no.generate.post'), [
                'prefix' => 'RMT',
                'tahun'  => 2026,
                'bulan'  => 4,
            ])
            ->assertOk()
            ->assertSee('RMT-2604-001');

        // --- update: cross-masjid blocked ---
        $this->actingAs($bendahari)
            ->put(route('admin.running-no.update', [$masjidB->id, 'RMT', 2026, 4]), [
                'last_no' => 0,
            ])
            ->assertForbidden();
    }
}
