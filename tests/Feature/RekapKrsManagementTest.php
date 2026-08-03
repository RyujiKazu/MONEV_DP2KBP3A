<?php

namespace Tests\Feature;

use App\Models\RekapKrs;
use App\Models\User;

class RekapKrsManagementTest extends FeatureTestCase
{
    public function test_admin_can_create_read_update_and_delete_krs_data(): void
    {
        $admin = $this->createUser(User::ROLE_ADMIN);
        $kecamatan = $this->createKecamatan();
        $payload = $this->validRekapPayload($kecamatan, 2025);

        $this->actingAs($admin)
            ->get(route('admin.rekap-krs.create'))
            ->assertOk();

        $this->actingAs($admin)
            ->post(route('admin.rekap-krs.store'), $payload)
            ->assertRedirect(route('admin.rekap-krs.index'))
            ->assertSessionHas('success');

        $rekap = RekapKrs::query()->sole();

        $this->assertSame($admin->getKey(), $rekap->created_by);

        $this->actingAs($admin)
            ->get(route('admin.rekap-krs.show', $rekap))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('admin.rekap-krs.edit', $rekap))
            ->assertOk();

        $updatedPayload = $this->validRekapPayload($kecamatan, 2026);

        $this->actingAs($admin)
            ->put(route('admin.rekap-krs.update', $rekap), $updatedPayload)
            ->assertRedirect(route('admin.rekap-krs.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('tb_rekap_krs', [
            'id_rekap' => $rekap->getKey(),
            'kode_kecamatan' => $kecamatan->getKey(),
            'tahun' => 2026,
            'created_by' => $admin->getKey(),
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.rekap-krs.destroy', $rekap))
            ->assertRedirect(route('admin.rekap-krs.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('tb_rekap_krs', ['id_rekap' => $rekap->getKey()]);
    }

    public function test_duplicate_kecamatan_and_year_is_rejected(): void
    {
        $admin = $this->createUser();
        $kecamatan = $this->createKecamatan();
        $this->createRekap($kecamatan, 2025, creator: $admin);

        $this->actingAs($admin)
            ->from(route('admin.rekap-krs.create'))
            ->post(route('admin.rekap-krs.store'), $this->validRekapPayload($kecamatan, 2025))
            ->assertRedirect(route('admin.rekap-krs.create'))
            ->assertSessionHasErrors('kode_kecamatan');

        $this->assertDatabaseCount('tb_rekap_krs', 1);
    }

    public function test_negative_krs_value_is_rejected(): void
    {
        $admin = $this->createUser();
        $kecamatan = $this->createKecamatan();
        $payload = $this->validRekapPayload($kecamatan, 2025, ['jumlah_keluarga' => -1]);

        $this->actingAs($admin)
            ->post(route('admin.rekap-krs.store'), $payload)
            ->assertSessionHasErrors('jumlah_keluarga');

        $this->assertDatabaseCount('tb_rekap_krs', 0);
    }

    public function test_total_krs_and_target_family_equality_is_validated(): void
    {
        $admin = $this->createUser();
        $kecamatan = $this->createKecamatan();
        $payload = $this->validRekapPayload($kecamatan);
        $payload['tidak_berisiko']++;

        $this->actingAs($admin)
            ->post(route('admin.rekap-krs.store'), $payload)
            ->assertSessionHasErrors('tidak_berisiko');

        $this->assertDatabaseCount('tb_rekap_krs', 0);
    }

    public function test_welfare_total_equality_is_validated(): void
    {
        $admin = $this->createUser();
        $kecamatan = $this->createKecamatan();
        $payload = $this->validRekapPayload($kecamatan);
        $payload['kesejahteraan_1']++;

        $this->actingAs($admin)
            ->post(route('admin.rekap-krs.store'), $payload)
            ->assertSessionHasErrors('kesejahteraan_lebih_4');

        $this->assertDatabaseCount('tb_rekap_krs', 0);
    }
}
