<?php

namespace Tests\Feature;

use App\Models\User;

class DashboardAndReportTest extends FeatureTestCase
{
    public function test_dashboard_year_and_kecamatan_filters_work_for_pkk(): void
    {
        $pkk = $this->createUser(User::ROLE_PKK);
        $first = $this->createKecamatan('TEST-KEC-01', 'Kecamatan Pertama');
        $second = $this->createKecamatan('TEST-KEC-02', 'Kecamatan Kedua');
        $this->createRekap($first, 2024);
        $current = $this->createRekap($first, 2025);
        $this->createRekap($second, 2025);

        $this->actingAs($pkk)
            ->get(route('dashboard.index', [
                'tahun' => 2025,
                'kode_kecamatan' => $first->getKey(),
            ]))
            ->assertOk()
            ->assertViewIs('dashboard.index')
            ->assertViewHas('selectedYear', 2025)
            ->assertViewHas('selectedKecamatan', $first->getKey())
            ->assertViewHas('evaluation', function (array $evaluation) use ($current, $first): bool {
                return $evaluation['has_data']
                    && $evaluation['summary']['total_krs'] === $current->total_krs
                    && $evaluation['records']->count() === 1
                    && $evaluation['records']->first()['kode_kecamatan'] === $first->getKey();
            });
    }

    public function test_csv_report_can_be_downloaded_with_selected_filters(): void
    {
        $admin = $this->createUser();
        $selected = $this->createKecamatan('TEST-KEC-01', 'Kecamatan CSV');
        $excluded = $this->createKecamatan('TEST-KEC-02', 'Kecamatan Tidak Dipilih');
        $this->createRekap($selected, 2024);
        $this->createRekap($selected, 2025);
        $this->createRekap($excluded, 2025);

        $response = $this->actingAs($admin)->get(route('laporan.csv', [
            'tahun' => 2025,
            'kode_kecamatan' => $selected->getKey(),
        ]));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $response->assertDownload('laporan-evaluasi-krs-2025-'.now()->format('Ymd').'.csv');

        $content = $response->streamedContent();

        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);
        $rows = collect(preg_split('/\R/', substr($content, 3)) ?: [])
            ->map(fn (string $line): array => str_getcsv($line, ';', '"', ''));
        $metadata = $rows
            ->filter(fn (array $row): bool => count($row) >= 2)
            ->mapWithKeys(fn (array $row): array => [$row[0] => $row[1]]);

        $this->assertStringStartsWith(now()->format('d-m-Y'), $metadata->get('Tanggal generate'));
        $this->assertSame($admin->nama_lengkap, $metadata->get('Dibuat oleh'));
        $this->assertSame('2025', $metadata->get('Tahun'));
        $this->assertSame('2024', $metadata->get('Tahun pembanding'));
        $this->assertSame('Kecamatan CSV', $metadata->get('Kecamatan'));
        $this->assertStringContainsString('Ringkasan Kabupaten Subang', $content);
        $this->assertStringContainsString('Peringkat;Kecamatan', $content);
        $this->assertStringContainsString('Rincian Evaluasi per Indikator', $content);
        $this->assertStringContainsString('KPI-04', $content);
        $this->assertStringContainsString('Kecamatan CSV', $content);
        $this->assertStringNotContainsString('Kecamatan Tidak Dipilih', $content);
    }

    public function test_pdf_report_can_be_downloaded_by_pkk(): void
    {
        $pkk = $this->createUser(User::ROLE_PKK);
        $kecamatan = $this->createKecamatan('TEST-KEC-01', 'Kecamatan PDF');
        $this->createRekap($kecamatan, 2024);
        $this->createRekap($kecamatan, 2025);

        $response = $this->actingAs($pkk)->get(route('laporan.pdf', ['tahun' => 2025]));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $response->assertDownload('laporan-evaluasi-krs-2025-'.now()->format('Ymd').'.pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }
}
