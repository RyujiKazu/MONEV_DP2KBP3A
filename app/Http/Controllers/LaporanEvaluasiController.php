<?php

namespace App\Http\Controllers;

use App\Http\Requests\LaporanEvaluasiFilterRequest;
use App\Models\Kecamatan;
use App\Services\KrsEvaluationService;
use App\Services\KrsReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanEvaluasiController extends Controller
{
    public function index(
        LaporanEvaluasiFilterRequest $request,
        KrsReportService $reportService,
        KrsEvaluationService $evaluationService,
    ): View {
        return view('laporan.index', array_merge(
            $reportService->prepare($request->validated(), $request->user()),
            [
                'years' => $evaluationService->availableYears(),
                'kecamatans' => Kecamatan::query()->orderBy('nama_kecamatan')->get(),
            ],
        ));
    }

    public function print(LaporanEvaluasiFilterRequest $request, KrsReportService $reportService): View
    {
        return view('laporan.print', $reportService->prepare($request->validated(), $request->user()));
    }

    public function csv(LaporanEvaluasiFilterRequest $request, KrsReportService $reportService): StreamedResponse
    {
        $report = $reportService->prepare($request->validated(), $request->user());
        $filename = $reportService->filename('csv', $report['filters']['tahun']);

        return response()->streamDownload(function () use ($report, $reportService): void {
            $stream = fopen('php://output', 'wb');

            if ($stream === false) {
                return;
            }

            fwrite($stream, "\xEF\xBB\xBF");

            foreach ($reportService->csvRows($report) as $row) {
                fputcsv($stream, $row, ';', '"', '');
            }

            fclose($stream);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function pdf(LaporanEvaluasiFilterRequest $request, KrsReportService $reportService): Response
    {
        $report = $reportService->prepare($request->validated(), $request->user());
        $filename = $reportService->filename('pdf', $report['filters']['tahun']);

        return Pdf::loadView('laporan.pdf', $report)
            ->setPaper('a4', 'landscape')
            ->download($filename);
    }
}
