<?php

namespace Tests\Feature;

use App\Models\LogAktiviti;
use App\Models\Masjid;
use App\Models\User;
use App\Services\LogAktivitiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LogAktivitiModuleSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_log_aktiviti_module_read_only_audit_trail_with_filters_and_scoping(): void
    {
        // --- roles & permissions ---
        $auditView = Permission::query()->firstOrCreate(['name' => 'audit.view', 'guard_name' => 'web']);

        $auditorRole = Role::query()->firstOrCreate(['name' => 'Auditor', 'guard_name' => 'web']);
        $auditorRole->syncPermissions([$auditView]);

        $managerRole = Role::query()->firstOrCreate(['name' => 'Manager', 'guard_name' => 'web']);
        $managerRole->syncPermissions([$auditView]);

        $userRole = Role::query()->firstOrCreate(['name' => 'User', 'guard_name' => 'web']);
        $userRole->syncPermissions([]);

        // --- fixtures ---
        $masjidA = Masjid::query()->create(['nama' => 'Masjid Log A']);
        $masjidB = Masjid::query()->create(['nama' => 'Masjid Log B']);

        $auditor = User::query()->create([
            'name'      => 'Auditor Log',
            'email'     => 'auditor.log@example.test',
            'password'  => 'password',
            'id_masjid' => $masjidA->id,
            'aktif'     => true,
        ]);
        $auditor->assignRole($auditorRole);

        $manager = User::query()->create([
            'name'      => 'Manager Log',
            'email'     => 'manager.log@example.test',
            'password'  => 'password',
            'id_masjid' => $masjidA->id,
            'aktif'     => true,
        ]);
        $manager->assignRole($managerRole);

        $regularUser = User::query()->create([
            'name'      => 'User Biasa',
            'email'     => 'user.log@example.test',
            'password'  => 'password',
            'id_masjid' => $masjidA->id,
            'aktif'     => true,
        ]);
        $regularUser->assignRole($userRole);

        // --- seed log records ---
        $logA1 = LogAktiviti::query()->create([
            'id_masjid'  => $masjidA->id,
            'id_user'    => $auditor->id,
            'jenis'      => 'LOGIN_OK',
            'modul'      => null,
            'aksi'       => 'Login berjaya',
            'butiran'    => 'Pengguna log masuk dari browser',
            'ip'         => '192.168.1.10',
            'user_agent' => 'Mozilla/5.0',
            'created_at' => '2026-04-10 08:00:00',
        ]);

        $logA2 = LogAktiviti::query()->create([
            'id_masjid'  => $masjidA->id,
            'id_user'    => $auditor->id,
            'jenis'      => 'CREATE',
            'modul'      => 'Hasil',
            'aksi'       => 'tambah rekod',
            'butiran'    => 'Rekod hasil Jumaat ditambah',
            'data_baru'  => json_encode(['jumlah' => 500.00]),
            'ip'         => '192.168.1.10',
            'user_agent' => 'Mozilla/5.0',
            'created_at' => '2026-04-11 10:30:00',
        ]);

        $logA3 = LogAktiviti::query()->create([
            'id_masjid'  => $masjidA->id,
            'id_user'    => $auditor->id,
            'jenis'      => 'DELETE',
            'modul'      => 'Belanja',
            'aksi'       => 'hapus rekod',
            'butiran'    => 'Rekod belanja draf dipadamkan',
            'data_lama'  => json_encode(['amaun' => 200.00]),
            'ip'         => '192.168.1.10',
            'user_agent' => 'Mozilla/5.0',
            'created_at' => '2026-04-12 14:00:00',
        ]);

        // MasjidB log (should be hidden from masjidA Auditor)
        $logB = LogAktiviti::query()->create([
            'id_masjid'  => $masjidB->id,
            'id_user'    => $manager->id,
            'jenis'      => 'UPDATE',
            'modul'      => 'Akaun',
            'aksi'       => 'kemaskini',
            'butiran'    => 'Rekod tersembunyi masjidB',
            'ip'         => '10.0.0.1',
            'user_agent' => 'Chrome',
            'created_at' => '2026-04-12 09:00:00',
        ]);

        // --- index: forbidden for regular user ---
        $this->actingAs($regularUser)
            ->get(route('admin.log-aktiviti.index'))
            ->assertForbidden();

        // --- index: accessible to auditor ---
        $this->actingAs($auditor)
            ->get(route('admin.log-aktiviti.index'))
            ->assertOk()
            ->assertSee('Log Aktiviti');

        // --- index: masjid scoping (masjidB log hidden) ---
        $response = $this->actingAs($auditor)
            ->get(route('admin.log-aktiviti.index'));
        $response->assertOk();
        $response->assertSee('Login berjaya');
        $response->assertDontSee('Rekod tersembunyi masjidB');

        // --- index: jenis filter ---
        $this->actingAs($auditor)
            ->get(route('admin.log-aktiviti.index', ['jenis' => 'LOGIN_OK']))
            ->assertOk()
            ->assertSee('Login berjaya')
            ->assertDontSee('Rekod hasil Jumaat ditambah');

        // --- index: jenis filter DELETE ---
        $this->actingAs($auditor)
            ->get(route('admin.log-aktiviti.index', ['jenis' => 'DELETE']))
            ->assertOk()
            ->assertSee('Rekod belanja draf dipadamkan')
            ->assertDontSee('Login berjaya');

        // --- index: modul filter ---
        $this->actingAs($auditor)
            ->get(route('admin.log-aktiviti.index', ['modul' => 'Hasil']))
            ->assertOk()
            ->assertSee('Rekod hasil Jumaat ditambah')
            ->assertDontSee('Rekod belanja draf dipadamkan');

        // --- index: date range filter ---
        $this->actingAs($auditor)
            ->get(route('admin.log-aktiviti.index', [
                'date_from' => '2026-04-11',
                'date_to'   => '2026-04-11',
            ]))
            ->assertOk()
            ->assertSee('Rekod hasil Jumaat ditambah')
            ->assertDontSee('Login berjaya');

        // --- index: user filter ---
        $this->actingAs($auditor)
            ->get(route('admin.log-aktiviti.index', ['user_id' => $auditor->id]))
            ->assertOk()
            ->assertSee('Login berjaya');

        // --- show: detail page loads ---
        $this->actingAs($auditor)
            ->get(route('admin.log-aktiviti.show', $logA2))
            ->assertOk()
            ->assertSee('CREATE')
            ->assertSee('Rekod hasil Jumaat ditambah')
            ->assertSee('Data Baru');

        // --- show: detail page with data_lama diff ---
        $this->actingAs($auditor)
            ->get(route('admin.log-aktiviti.show', $logA3))
            ->assertOk()
            ->assertSee('DELETE')
            ->assertSee('Data Lama');

        // --- show: cross-masjid access blocked (403) ---
        $this->actingAs($auditor)
            ->get(route('admin.log-aktiviti.show', $logB))
            ->assertForbidden();

        // --- show: regular user blocked ---
        $this->actingAs($regularUser)
            ->get(route('admin.log-aktiviti.show', $logA1))
            ->assertForbidden();

        // --- LogAktivitiService recording ---
        $service = new LogAktivitiService();
        $this->actingAs($auditor);

        $logged = $service->record(
            jenis: LogAktivitiService::JENIS_CREATE,
            modul: 'TestModul',
            aksi: 'ujian rekod',
            options: [
                'rujukan_id' => 999,
                'butiran'    => 'Log dari service',
                'data_baru'  => ['field' => 'value'],
            ]
        );

        $this->assertInstanceOf(LogAktiviti::class, $logged);
        $this->assertSame('CREATE', $logged->jenis);
        $this->assertSame('TestModul', $logged->modul);
        $this->assertSame(999, $logged->rujukan_id);
        $this->assertEquals(['field' => 'value'], $logged->data_baru);

        // --- service: lowercase jenis normalised to uppercase ---
        $logged2 = $service->record('update', 'TestModul', 'kemaskini');
        $this->assertSame('UPDATE', $logged2->jenis);
    }
}
