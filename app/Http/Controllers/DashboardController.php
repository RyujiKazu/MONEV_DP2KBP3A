<?php

namespace App\Http\Controllers;

use App\Http\Requests\DashboardFilterRequest;
use App\Models\Kecamatan;
use App\Services\KrsDashboardService;
use App\Services\KrsEvaluationService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(
        DashboardFilterRequest $request,
        KrsEvaluationService $evaluationService,
        KrsDashboardService $dashboardService,
    ): View {
        $validated = $request->validated();
        $selectedYear = isset($validated['tahun'])
            ? (int) $validated['tahun']
            : $evaluationService->latestYear();
        $selectedKecamatan = $validated['kode_kecamatan'] ?? null;
        $evaluation = $evaluationService->evaluateYear($selectedYear, $selectedKecamatan);

        return view('dashboard.index', [
            'evaluation' => $evaluation,
            'chartData' => $dashboardService->chartData($evaluation),
            'years' => $evaluationService->availableYears(),
            'kecamatans' => Kecamatan::query()->orderBy('nama_kecamatan')->get(),
            'selectedYear' => $selectedYear,
            'selectedKecamatan' => $selectedKecamatan,
        ]);
    }
}
