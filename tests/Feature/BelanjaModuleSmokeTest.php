<?php

namespace Tests\Feature;

use App\Models\Akaun;
use App\Models\BaucarBayaran;
use App\Models\Belanja;
use App\Models\KategoriBelanja;
use App\Models\Masjid;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BelanjaModuleSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_belanja_module_draft_submitted_soft_delete_and_baucar_filter_flow(): void
    {
        $permissions = collect([
            'belanja.view',
            'belanja.create',
            'belanja.update',
            'belanja.delete',
        ])->map(function (string $name) {
            return Permission::query()->firstOrCreate([
                'name' => $name,
                'guard_name' => 'web',
            ]);
        });

        $role = Role::query()->firstOrCreate([
            'name' => 'Bendahari',
            'guard_name' => 'web',
        ]);
        $role->syncPermissions($permissions);

        $masjidA = Masjid::query()->create([
            'nama' => 'Masjid Belanja A',
            'status' => 'active',
            'subscription_status' => 'active',
            'subscription_expiry' => now()->addDays(30),
        ]);
        $masjidB = Masjid::query()->create([
            'nama' => 'Masjid Belanja B',
            'status' => 'active',
            'subscription_status' => 'active',
            'subscription_expiry' => now()->addDays(30),
        ]);

        $bendahari = User::query()->create([
            'name' => 'Bendahari Belanja',
            'email' => 'bendahari.belanja@example.test',
            'password' => 'password',
            'id_masjid' => $masjidA->id,
            'aktif' => true,
        ]);
        $bendahari->assignRole($role);

        $akaunA = Akaun::query()->create([
            'id_masjid' => $masjidA->id,
            'nama_akaun' => 'Bank Belanja A',
            'jenis' => 'bank',
            'status_aktif' => true,
        ]);
        $akaunHidden = Akaun::query()->create([
            'id_masjid' => $masjidB->id,
            'nama_akaun' => 'Bank Hidden B',
            'jenis' => 'bank',
            'status_aktif' => true,
        ]);

        $kategoriA = KategoriBelanja::query()->create([
            'id_masjid' => $masjidA->id,
            'kod' => 'UTIL',
            'nama_kategori' => 'Utiliti A',
            'aktif' => true,
        ]);
        $kategoriHidden = KategoriBelanja::query()->create([
            'id_masjid' => $masjidB->id,
            'kod' => 'HIDE',
            'nama_kategori' => 'Kategori Hidden B',
            'aktif' => true,
        ]);

        $baucarA = BaucarBayaran::query()->create([
            'id_masjid' => $masjidA->id,
            'tarikh' => '2026-04-12',
            'no_baucar' => 'BV-A-001',
            'id_akaun' => $akaunA->id,
            'kaedah' => 'bank',
            'jumlah' => 0,
            'status' => 'DRAF',
            'created_by' => $bendahari->id,
        ]);
        $baucarHidden = BaucarBayaran::query()->create([
            'id_masjid' => $masjidB->id,
            'tarikh' => '2026-04-12',
            'no_baucar' => 'BV-B-001',
            'id_akaun' => $akaunHidden->id,
            'kaedah' => 'bank',
            'jumlah' => 0,
            'status' => 'DRAF',
            'created_by' => $bendahari->id,
        ]);

        $draftRecord = Belanja::query()->create([
            'id_masjid' => $masjidA->id,
            'tarikh' => '2026-04-10',
            'id_akaun' => $akaunA->id,
            'id_kategori_belanja' => $kategoriA->id,
            'amaun' => 200,
            'created_by' => $bendahari->id,
            'status' => 'DRAF',
            'is_deleted' => false,
            'id_baucar' => $baucarA->id,
        ]);

        $submittedRecord = Belanja::query()->create([
            'id_masjid' => $masjidA->id,
            'tarikh' => '2026-04-11',
            'id_akaun' => $akaunA->id,
            'id_kategori_belanja' => $kategoriA->id,
            'amaun' => 450,
            'created_by' => $bendahari->id,
            'status' => 'LULUS',
            'is_deleted' => false,
            'id_baucar' => null,
            'dilulus_oleh' => $bendahari->id,
            'tarikh_lulus' => now(),
        ]);

        Belanja::query()->create([
            'id_masjid' => $masjidB->id,
            'tarikh' => '2026-04-11',
            'id_akaun' => $akaunHidden->id,
            'id_kategori_belanja' => $kategoriHidden->id,
            'amaun' => 999,
            'created_by' => $bendahari->id,
            'status' => 'DRAF',
            'is_deleted' => false,
            'id_baucar' => $baucarHidden->id,
        ]);

        $this->actingAs($bendahari)
            ->get(route('admin.belanja.index'))
            ->assertOk()
            ->assertSeeText('200.00')
            ->assertSeeText('450.00')
            ->assertDontSeeText('999.00');

        $this->actingAs($bendahari)
            ->get(route('admin.belanja.index', ['status' => 'draft']))
            ->assertOk()
            ->assertSeeText('200.00')
            ->assertDontSeeText('450.00');

        $this->actingAs($bendahari)
            ->get(route('admin.belanja.index', ['baucar_id' => $baucarA->id]))
            ->assertOk()
            ->assertSeeText('200.00')
            ->assertDontSeeText('450.00');

        $this->actingAs($bendahari)
            ->post(route('admin.belanja.store'), [
                'id_masjid' => $masjidB->id,
                'tarikh' => '2026-04-14',
                'amaun' => 320.50,
                'id_akaun' => $akaunA->id,
                'id_kategori_belanja' => $kategoriA->id,
                'id_baucar' => $baucarA->id,
                'submit_action' => 'draft',
                'penerima' => 'Pembekal A',
                'catatan' => 'Belanja baharu ujian',
            ])
            ->assertRedirect();

        $created = Belanja::query()->where('catatan', 'Belanja baharu ujian')->first();
        $this->assertNotNull($created);
        $this->assertSame($masjidA->id, $created->id_masjid);
        $this->assertSame('DRAF', $created->status);

        $this->actingAs($bendahari)
            ->put(route('admin.belanja.update', $draftRecord), [
                'id_masjid' => $masjidA->id,
                'tarikh' => '2026-04-10',
                'amaun' => 275,
                'id_akaun' => $akaunA->id,
                'id_kategori_belanja' => $kategoriA->id,
                'id_baucar' => $baucarA->id,
                'submit_action' => 'submitted',
                'penerima' => 'Vendor Utiliti',
                'catatan' => 'Belanja draf dikemaskini',
            ])
            ->assertRedirect();

        $this->assertSame('LULUS', $draftRecord->fresh()->status);
        $this->assertSame('275.00', (string) $draftRecord->fresh()->amaun);

        $this->actingAs($bendahari)
            ->delete(route('admin.belanja.destroy', $submittedRecord))
            ->assertRedirect();

        $this->assertTrue($submittedRecord->fresh()->is_deleted);
        $this->assertNotNull($submittedRecord->fresh()->deleted_at);

        $this->actingAs($bendahari)
            ->get(route('admin.belanja.index'))
            ->assertOk()
            ->assertDontSeeText('450.00');
    }

    public function test_belanja_attachment_can_be_viewed_by_read_only_user_in_same_masjid(): void
    {
        Storage::fake('public');

        $viewPermission = Permission::query()->firstOrCreate([
            'name' => 'belanja.view',
            'guard_name' => 'web',
        ]);

        $viewerRole = Role::query()->firstOrCreate([
            'name' => 'AJK',
            'guard_name' => 'web',
        ]);
        $viewerRole->syncPermissions([$viewPermission]);

        $masjid = Masjid::query()->create([
            'nama' => 'Masjid Attachment View',
            'status' => 'active',
            'subscription_status' => 'active',
            'subscription_expiry' => now()->addDays(30),
        ]);

        $viewer = User::query()->create([
            'name' => 'AJK Viewer',
            'email' => 'ajk.viewer@example.test',
            'password' => 'password',
            'id_masjid' => $masjid->id,
            'aktif' => true,
        ]);
        $viewer->assignRole($viewerRole);

        $akaun = Akaun::query()->create([
            'id_masjid' => $masjid->id,
            'nama_akaun' => 'Bank Attachment',
            'jenis' => 'bank',
            'status_aktif' => true,
        ]);

        $kategori = KategoriBelanja::query()->create([
            'id_masjid' => $masjid->id,
            'kod' => 'DOC',
            'nama_kategori' => 'Dokumen',
            'aktif' => true,
        ]);

        $attachmentPath = 'belanja-bukti/test-attachment.pdf';
        Storage::disk('public')->put($attachmentPath, 'fake pdf content');
        Storage::disk('public')->assertExists($attachmentPath);

        $belanja = Belanja::query()->create([
            'id_masjid' => $masjid->id,
            'tarikh' => '2026-04-15',
            'id_akaun' => $akaun->id,
            'id_kategori_belanja' => $kategori->id,
            'amaun' => 50,
            'created_by' => $viewer->id,
            'status' => 'LULUS',
            'is_deleted' => false,
            'bukti_fail' => $attachmentPath,
        ]);

        $this->actingAs($viewer)
            ->get(route('admin.belanja.viewAttachment', $belanja))
            ->assertOk()
            ->assertHeader('content-disposition', 'inline; filename="test-attachment.pdf"');
    }
}
