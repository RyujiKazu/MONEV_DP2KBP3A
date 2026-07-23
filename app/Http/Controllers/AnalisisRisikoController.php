<?php

namespace App\Http\Controllers;

use App\Models\EvaluasiKrs;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use Illuminate\Http\Request;

class AnalisisRisikoController extends Controller
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
            ->when($selectedKelurahan, fn ($query) => $query->where('kode_kelurahan', $selectedKelurahan))
            ->when(! $selectedKelurahan && $selectedKecamatan, fn ($query) => $query->where('kode_kecamatan', $selectedKecamatan));

        $totals = (clone $baseQuery)
            ->selectRaw('
                COALESCE(SUM(air_tidak_layak), 0) as air_tidak_layak,
                COALESCE(SUM(jamban_tidak_layak), 0) as jamban_tidak_layak,
                COALESCE(SUM(terlalu_muda), 0) as terlalu_muda,
                COALESCE(SUM(terlalu_tua), 0) as terlalu_tua,
                COALESCE(SUM(terlalu_dekat), 0) as terlalu_dekat,
                COALESCE(SUM(terlalu_banyak), 0) as terlalu_banyak,
                COALESCE(SUM(desil_1), 0) as desil_1,
                COALESCE(SUM(desil_2), 0) as desil_2,
                COALESCE(SUM(desil_3), 0) as desil_3,
                COALESCE(SUM(desil_4), 0) as desil_4,
                COALESCE(SUM(desil_5), 0) as desil_5,
                COALESCE(SUM(desil_6), 0) as desil_6,
                COALESCE(SUM(desil_7), 0) as desil_7,
                COALESCE(SUM(desil_8), 0) as desil_8,
                COALESCE(SUM(desil_9), 0) as desil_9,
                COALESCE(SUM(desil_10), 0) as desil_10,
                COALESCE(SUM(jumlah_keluarga_sasaran), 0) as total_keluarga_sasaran,
                COALESCE(SUM(total_berisiko), 0) as total_berisiko,
                COALESCE(SUM(tidak_berisiko), 0) as total_tidak_berisiko
            ')
            ->first();

        $environmentLabels = ['Air Tidak Layak', 'Jamban Tidak Layak'];
        $environmentValues = [
            (int) ($totals->air_tidak_layak ?? 0),
            (int) ($totals->jamban_tidak_layak ?? 0),
        ];

        $reproductionLabels = ['Terlalu Muda', 'Terlalu Tua', 'Terlalu Dekat', 'Terlalu Banyak'];
        $reproductionValues = [
            (int) ($totals->terlalu_muda ?? 0),
            (int) ($totals->terlalu_tua ?? 0),
            (int) ($totals->terlalu_dekat ?? 0),
            (int) ($totals->terlalu_banyak ?? 0),
        ];

        $welfareLabels = ['Desil 1', 'Desil 2', 'Desil 3', 'Desil 4', 'Desil 5', 'Desil 6', 'Desil 7', 'Desil 8', 'Desil 9', 'Desil 10'];
        $welfareValues = [
            (int) ($totals->desil_1 ?? 0),
            (int) ($totals->desil_2 ?? 0),
            (int) ($totals->desil_3 ?? 0),
            (int) ($totals->desil_4 ?? 0),
            (int) ($totals->desil_5 ?? 0),
            (int) ($totals->desil_6 ?? 0),
            (int) ($totals->desil_7 ?? 0),
            (int) ($totals->desil_8 ?? 0),
            (int) ($totals->desil_9 ?? 0),
            (int) ($totals->desil_10 ?? 0),
        ];

        $safePercentage = (float) ($totals->total_keluarga_sasaran ?? 0) > 0
            ? round(((float) $totals->total_tidak_berisiko / (float) $totals->total_keluarga_sasaran) * 100, 1)
            : 0.0;

        return view('admin.analisis-risiko', [
            'periods' => $periods,
            'selectedYear' => $selectedYear,
            'selectedMonth' => $selectedMonth,
            'selectedKecamatan' => $selectedKecamatan,
            'selectedKelurahan' => $selectedKelurahan,
            'totals' => $totals,
            'safePercentage' => $safePercentage,
            'environmentLabels' => $environmentLabels,
            'environmentValues' => $environmentValues,
            'reproductionLabels' => $reproductionLabels,
            'reproductionValues' => $reproductionValues,
            'welfareLabels' => $welfareLabels,
            'welfareValues' => $welfareValues,
            'welfareAvailable' => array_sum($welfareValues) > 0,
            'kecamatans' => $kecamatans,
            'kelurahans' => $kelurahans,
        ]);
    }
}