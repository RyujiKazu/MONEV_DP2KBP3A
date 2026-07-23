<?php

namespace App\Http\Controllers;

use App\Models\EvaluasiKrs;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $kecamatans = Kecamatan::query()->orderBy('nama_kecamatan')->get();
        $kelurahans = Kelurahan::query()->with('kecamatan')->orderBy('nama_kelurahan')->get();

        $periods = EvaluasiKrs::query()
            ->selectRaw('YEAR(periode_evaluasi) as tahun, MONTH(periode_evaluasi) as bulan')
            ->distinct()
            ->orderByDesc('tahun')
            ->orderByDesc('bulan')
            ->get();

        $latestPeriod = EvaluasiKrs::query()
            ->selectRaw('YEAR(periode_evaluasi) as tahun, MONTH(periode_evaluasi) as bulan')
            ->orderByDesc('periode_evaluasi')
            ->first();

        $selectedYear = $request->integer('tahun') ?: ($latestPeriod?->tahun ? (int) $latestPeriod->tahun : (int) now()->year);
        $selectedMonth = $request->integer('bulan') ?: ($latestPeriod?->bulan ? (int) $latestPeriod->bulan : (int) now()->month);
        $selectedKecamatan = $request->string('kode_kecamatan')->toString() ?: null;
        $selectedKelurahan = $request->string('kode_kelurahan')->toString() ?: null;

        $baseQuery = EvaluasiKrs::query()
            ->when(
                $selectedYear && $selectedMonth,
                fn ($query) => $query->whereYear('periode_evaluasi', $selectedYear)->whereMonth('periode_evaluasi', $selectedMonth)
            )
            ->when($selectedKelurahan, fn ($query) => $query->where('tb_evaluasi_krs.kode_kelurahan', $selectedKelurahan))
            ->when(! $selectedKelurahan && $selectedKecamatan, fn ($query) => $query->where('tb_evaluasi_krs.kode_kecamatan', $selectedKecamatan));

        $totals = (clone $baseQuery)
            ->selectRaw('
                COALESCE(SUM(jumlah_keluarga_sasaran), 0) as total_keluarga_sasaran,
                COALESCE(SUM(total_berisiko), 0) as total_berisiko,
                COALESCE(SUM(tidak_berisiko), 0) as total_tidak_berisiko,
                COALESCE(SUM(jumlah_keluarga), 0) as total_keluarga,
                COALESCE(SUM(air_tidak_layak), 0) as air_tidak_layak,
                COALESCE(SUM(jamban_tidak_layak), 0) as jamban_tidak_layak,
                COALESCE(SUM(terlalu_muda), 0) as terlalu_muda,
                COALESCE(SUM(terlalu_tua), 0) as terlalu_tua,
                COALESCE(SUM(terlalu_dekat), 0) as terlalu_dekat,
                COALESCE(SUM(terlalu_banyak), 0) as terlalu_banyak
            ')
            ->first();

        $topKecamatan = (clone $baseQuery)
            ->join('tb_kecamatan', 'tb_evaluasi_krs.kode_kecamatan', '=', 'tb_kecamatan.kode_kecamatan')
            ->select(
                'tb_evaluasi_krs.kode_kecamatan',
                'tb_kecamatan.nama_kecamatan',
                DB::raw('COALESCE(SUM(tb_evaluasi_krs.total_berisiko), 0) as total_berisiko')
            )
            ->groupBy('tb_evaluasi_krs.kode_kecamatan', 'tb_kecamatan.nama_kecamatan')
            ->orderByDesc('total_berisiko')
            ->limit(5)
            ->get();

        $topKelurahan = (clone $baseQuery)
            ->leftJoin('tb_kelurahan', 'tb_evaluasi_krs.kode_kelurahan', '=', 'tb_kelurahan.kode_kelurahan')
            ->select(
                'tb_evaluasi_krs.kode_kelurahan',
                'tb_kelurahan.nama_kelurahan',
                DB::raw('COALESCE(SUM(tb_evaluasi_krs.total_berisiko), 0) as total_berisiko')
            )
            ->groupBy('tb_evaluasi_krs.kode_kelurahan', 'tb_kelurahan.nama_kelurahan')
            ->orderByDesc('total_berisiko')
            ->limit(5)
            ->get();

        $scopeLabel = 'Kabupaten Subang';
        if ($selectedKelurahan) {
            $scopeLabel = optional($kelurahans->firstWhere('kode_kelurahan', $selectedKelurahan))->nama_kelurahan ?? $scopeLabel;
        } elseif ($selectedKecamatan) {
            $scopeLabel = optional($kecamatans->firstWhere('kode_kecamatan', $selectedKecamatan))->nama_kecamatan ?? $scopeLabel;
        }

        $periodLabel = $this->formatPeriod((int) $selectedYear, (int) $selectedMonth);
        $safePercentage = (float) ($totals->total_keluarga_sasaran ?? 0) > 0
            ? round(((float) $totals->total_tidak_berisiko / (float) $totals->total_keluarga_sasaran) * 100, 1)
            : 0.0;

        $vervalPercentage = (float) ($totals->total_keluarga_sasaran ?? 0) > 0
            ? round(((float) $totals->total_tidak_berisiko / (float) $totals->total_keluarga_sasaran) * 100, 1)
            : 0.0;

        $sanitationRiskTotal = (int) ($totals->air_tidak_layak ?? 0) + (int) ($totals->jamban_tidak_layak ?? 0);
        $sanitationRiskPercentage = (float) ($totals->total_berisiko ?? 0) > 0
            ? round(($sanitationRiskTotal / (float) $totals->total_berisiko) * 100, 1)
            : 0.0;

        $reproductionIndicators = [
            'terlalu_muda' => (int) ($totals->terlalu_muda ?? 0),
            'terlalu_tua' => (int) ($totals->terlalu_tua ?? 0),
            'terlalu_dekat' => (int) ($totals->terlalu_dekat ?? 0),
            'terlalu_banyak' => (int) ($totals->terlalu_banyak ?? 0),
        ];
        $dominantReproductionKey = null;
        $dominantReproductionValue = -1;

        foreach ($reproductionIndicators as $key => $value) {
            if ($value > $dominantReproductionValue) {
                $dominantReproductionKey = $key;
                $dominantReproductionValue = $value;
            }
        }

        $evaluationSummaries = [
            $this->buildVervalSummary($selectedKecamatan, $selectedKelurahan, $vervalPercentage),
            $this->buildSanitationSummary($selectedKecamatan, $selectedKelurahan, $sanitationRiskPercentage),
            $this->buildReproductionSummary($selectedKecamatan, $selectedKelurahan, $dominantReproductionKey, $reproductionIndicators),
        ];

        $overallSummary = collect($evaluationSummaries)
            ->sortByDesc(fn ($item) => $this->riskPriority($item['status']))
            ->first();

        $gaugeState = $safePercentage >= 75
            ? ['label' => 'Aman', 'class' => 'text-emerald-600', 'bar' => '#16a34a']
            : ($safePercentage >= 50
                ? ['label' => 'Waspada', 'class' => 'text-amber-600', 'bar' => '#f59e0b']
                : ['label' => 'Kritis', 'class' => 'text-rose-600', 'bar' => '#e11d48']);

        return view('dashboard', [
            'periods' => $periods,
            'selectedYear' => $selectedYear,
            'selectedMonth' => $selectedMonth,
            'selectedKecamatan' => $selectedKecamatan,
            'selectedKelurahan' => $selectedKelurahan,
            'periodLabel' => $periodLabel,
            'totals' => $totals,
            'topKecamatan' => $topKecamatan,
            'topKelurahan' => $topKelurahan,
            'scopeLabel' => $scopeLabel,
            'safePercentage' => $safePercentage,
            'vervalPercentage' => $vervalPercentage,
            'sanitationRiskTotal' => $sanitationRiskTotal,
            'sanitationRiskPercentage' => $sanitationRiskPercentage,
            'evaluationSummaries' => $evaluationSummaries,
            'overallSummary' => $overallSummary,
            'gaugeState' => $gaugeState,
            'kecamatans' => $kecamatans,
            'kelurahans' => $kelurahans,
        ]);
    }

    private function buildVervalSummary(?string $selectedKecamatan, ?string $selectedKelurahan, float $vervalPercentage): array
    {
        $scopeText = $selectedKelurahan ? 'kelurahan terpilih' : ($selectedKecamatan ? 'kecamatan terpilih' : 'Kabupaten Subang');

        if ($vervalPercentage < 80) {
            return [
                'title' => 'Evaluasi Kinerja Penjangkauan Sasaran (Verval)',
                'status' => 'Kritis',
                'badge' => 'bg-rose-50 text-rose-700',
                'text' => "Evaluasi: Kinerja penjangkauan sasaran di {$scopeText} masih rendah ({$vervalPercentage}%). Rekomendasi: lakukan pembinaan langsung ke kader desa terkait kendala pendataan di lapangan.",
            ];
        }

        if ($vervalPercentage < 100) {
            return [
                'title' => 'Evaluasi Kinerja Penjangkauan Sasaran (Verval)',
                'status' => 'Waspada',
                'badge' => 'bg-amber-50 text-amber-700',
                'text' => "Evaluasi: Penjangkauan sasaran di {$scopeText} hampir memenuhi target ({$vervalPercentage}%). Rekomendasi: percepat pendataan dan verifikasi lapangan.",
            ];
        }

        return [
            'title' => 'Evaluasi Kinerja Penjangkauan Sasaran (Verval)',
            'status' => 'Optimal',
            'badge' => 'bg-emerald-50 text-emerald-700',
            'text' => "Evaluasi: Seluruh sasaran KRS di {$scopeText} telah terverifikasi (100%). Rekomendasi: pertahankan pola kerja saat ini dan lakukan monitoring berkala.",
        ];
    }

    private function buildSanitationSummary(?string $selectedKecamatan, ?string $selectedKelurahan, float $sanitationRiskPercentage): array
    {
        $scopeText = $selectedKelurahan ? 'kelurahan terpilih' : ($selectedKecamatan ? 'kecamatan terpilih' : 'wilayah terpilih');

        if ($sanitationRiskPercentage > 10) {
            return [
                'title' => 'Evaluasi Indikator Fasilitas Lingkungan',
                'status' => 'Darurat Sanitasi',
                'badge' => 'bg-rose-50 text-rose-700',
                'text' => "Evaluasi: Risiko stunting di {$scopeText} didominasi oleh ketiadaan jamban dan air bersih ({$sanitationRiskPercentage}%). Rekomendasi: segera jadwalkan intervensi lintas sektor dengan PUPR atau program MCK/bedah rumah.",
            ];
        }

        if ($sanitationRiskPercentage > 0) {
            return [
                'title' => 'Evaluasi Indikator Fasilitas Lingkungan',
                'status' => 'Waspada',
                'badge' => 'bg-amber-50 text-amber-700',
                'text' => "Evaluasi: Masih ada keluarga dengan sanitasi tidak layak di {$scopeText} ({$sanitationRiskPercentage}%). Rekomendasi: perkuat edukasi sanitasi dan pemetaan rumah tangga prioritas.",
            ];
        }

        return [
            'title' => 'Evaluasi Indikator Fasilitas Lingkungan',
            'status' => 'Optimal',
            'badge' => 'bg-emerald-50 text-emerald-700',
            'text' => "Evaluasi: Tidak ditemukan risiko sanitasi pada data {$scopeText}. Rekomendasi: pertahankan pengawasan lingkungan dan validasi data berkala.",
        ];
    }

    private function buildReproductionSummary(?string $selectedKecamatan, ?string $selectedKelurahan, ?string $dominantKey, array $reproductionIndicators): array
    {
        $scopeText = $selectedKelurahan ? 'kelurahan terpilih' : ($selectedKecamatan ? 'kecamatan terpilih' : 'wilayah terpilih');
        $dominantValue = (int) ($dominantKey ? ($reproductionIndicators[$dominantKey] ?? 0) : 0);

        if ($dominantValue <= 0) {
            return [
                'title' => 'Evaluasi Indikator 4 Terlalu',
                'status' => 'Optimal',
                'badge' => 'bg-emerald-50 text-emerald-700',
                'text' => "Evaluasi: Tidak ada indikator 4 terlalu yang menonjol di {$scopeText}. Rekomendasi: pertahankan layanan konseling KB dan pendampingan keluarga.",
            ];
        }

        if ($dominantKey === 'terlalu_muda') {
            return [
                'title' => 'Evaluasi Indikator 4 Terlalu',
                'status' => 'Kritis',
                'badge' => 'bg-rose-50 text-rose-700',
                'text' => "Evaluasi: Risiko stunting di {$scopeText} disebabkan oleh tingginya angka pernikahan dini. Rekomendasi: gencarkan edukasi PIK-R di sekolah-sekolah setempat.",
            ];
        }

        if ($dominantKey === 'terlalu_banyak') {
            return [
                'title' => 'Evaluasi Indikator 4 Terlalu',
                'status' => 'Kritis',
                'badge' => 'bg-rose-50 text-rose-700',
                'text' => "Evaluasi: Risiko di {$scopeText} didominasi oleh jarak kehamilan yang tidak terkontrol. Rekomendasi: tingkatkan sosialisasi dan layanan pemasangan alat kontrasepsi gratis bagi PUS.",
            ];
        }

        if ($dominantKey === 'terlalu_dekat') {
            return [
                'title' => 'Evaluasi Indikator 4 Terlalu',
                'status' => 'Waspada',
                'badge' => 'bg-amber-50 text-amber-700',
                'text' => "Evaluasi: Jarak kehamilan yang terlalu dekat masih menonjol di {$scopeText}. Rekomendasi: perkuat konseling KB dan kunjungan rumah PUS prioritas.",
            ];
        }

        return [
            'title' => 'Evaluasi Indikator 4 Terlalu',
            'status' => 'Waspada',
            'badge' => 'bg-amber-50 text-amber-700',
            'text' => "Evaluasi: Faktor reproduksi tertentu masih muncul di {$scopeText}. Rekomendasi: lakukan pendampingan kesehatan reproduksi dan monitoring PUS.",
        ];
    }

    private function riskPriority(string $status): int
    {
        return match ($status) {
            'Kritis', 'Darurat Sanitasi' => 3,
            'Waspada' => 2,
            default => 1,
        };
    }

    private function formatPeriod(int $year, int $month): string
    {
        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        return ($months[$month] ?? 'Bulan') . ' ' . $year;
    }
}