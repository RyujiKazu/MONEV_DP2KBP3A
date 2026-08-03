<?php

namespace Tests\Feature;

use App\Models\TargetIndikator;

class TargetIndikatorManagementTest extends FeatureTestCase
{
    public function test_admin_can_create_read_update_and_delete_indicator_target(): void
    {
        $admin = $this->createUser();

        $this->actingAs($admin)
            ->get(route('admin.target-indikator.create'))
            ->assertOk();

        $this->actingAs($admin)
            ->post(route('admin.target-indikator.store'), $this->validTargetPayload())
            ->assertRedirect(route('admin.target-indikator.index'))
            ->assertSessionHas('success');

        $target = TargetIndikator::query()->sole();

        $this->assertSame(TargetIndikator::INDIKATOR['KPI-01'], $target->nama_indikator);

        $this->actingAs($admin)
            ->get(route('admin.target-indikator.show', $target))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('admin.target-indikator.edit', $target))
            ->assertOk();

        $this->actingAs($admin)
            ->put(route('admin.target-indikator.update', $target), $this->validTargetPayload([
                'kode_indikator' => 'KPI-02',
                'nilai_target' => '15.5000',
                'jenis_target' => 'Regulatif',
                'sumber_target' => 'Dokumen regulatif pengujian',
                'status_aktif' => '0',
            ]))
            ->assertRedirect(route('admin.target-indikator.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('tb_target_indikator', [
            'id_target' => $target->getKey(),
            'kode_indikator' => 'KPI-02',
            'nama_indikator' => TargetIndikator::INDIKATOR['KPI-02'],
            'jenis_target' => 'Regulatif',
            'status_aktif' => false,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.target-indikator.destroy', $target))
            ->assertRedirect(route('admin.target-indikator.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('tb_target_indikator', ['id_target' => $target->getKey()]);
    }

    public function test_duplicate_target_and_excess_decimal_precision_are_rejected(): void
    {
        $admin = $this->createUser();
        $this->createTarget('KPI-01', 2025);

        $this->actingAs($admin)
            ->post(route('admin.target-indikator.store'), $this->validTargetPayload())
            ->assertSessionHasErrors('kode_indikator');

        $this->actingAs($admin)
            ->post(route('admin.target-indikator.store'), $this->validTargetPayload([
                'kode_indikator' => 'KPI-02',
                'nilai_target' => '12.34567',
            ]))
            ->assertSessionHasErrors('nilai_target');

        $this->assertDatabaseCount('tb_target_indikator', 1);
    }
}
