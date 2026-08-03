<?php

namespace Tests\Feature;

class DataWilayahProtectionTest extends FeatureTestCase
{
    public function test_kecamatan_with_krs_data_cannot_be_deleted(): void
    {
        $admin = $this->createUser();
        $kecamatan = $this->createKecamatan();
        $rekap = $this->createRekap($kecamatan, 2025, creator: $admin);

        $this->actingAs($admin)
            ->delete(route('admin.data-wilayah.kecamatan.destroy', $kecamatan))
            ->assertRedirect(route('admin.data-wilayah.index'))
            ->assertSessionHas('error', 'Kecamatan tidak dapat dihapus karena sudah memiliki data KRS. Hapus data KRS terkait terlebih dahulu.');

        $this->assertDatabaseHas('tb_kecamatan', ['kode_kecamatan' => $kecamatan->getKey()]);
        $this->assertDatabaseHas('tb_rekap_krs', ['id_rekap' => $rekap->getKey()]);
    }
}
