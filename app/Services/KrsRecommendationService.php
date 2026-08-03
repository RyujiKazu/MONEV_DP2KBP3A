<?php

namespace App\Services;

use App\Models\RekapKrs;

class KrsRecommendationService
{
    private const REKOMENDASI = [
        'krs' => 'Melakukan verifikasi kondisi wilayah dan memprioritaskan koordinasi intervensi terhadap keluarga berisiko.',
        'air_minum' => 'Melakukan koordinasi dengan perangkat daerah terkait untuk meningkatkan akses sumber air minum utama yang layak.',
        'jamban' => 'Melakukan koordinasi intervensi sanitasi dan peningkatan akses jamban layak.',
        'terlalu_muda' => 'Meningkatkan edukasi pendewasaan usia perkawinan dan persiapan kehidupan berkeluarga.',
        'terlalu_tua' => 'Meningkatkan konseling kesehatan reproduksi dan perencanaan kehamilan.',
        'terlalu_dekat' => 'Meningkatkan konseling pengaturan jarak kelahiran dan pelayanan keluarga berencana.',
        'terlalu_banyak' => 'Meningkatkan konseling keluarga berencana dan penggunaan alat kontrasepsi.',
        'pus_4t' => 'Meningkatkan konseling kesehatan reproduksi, perencanaan kehamilan, dan pelayanan keluarga berencana.',
        'kesejahteraan' => 'Melakukan verifikasi dan koordinasi dengan program perlindungan sosial atau perangkat daerah terkait.',
        'tren' => 'Perkuat pemantauan pada periode berikutnya karena indikator mengalami peningkatan risiko.',
    ];

    /**
     * @param  array<string, array<string, mixed>>  $indicators
     * @param  array<string, mixed>|null  $dominantFactor
     * @param  RekapKrs|array<string, mixed>  $record
     * @return list<string>
     */
    public function forEvaluation(array $indicators, ?array $dominantFactor, RekapKrs|array $record): array
    {
        $recommendations = [];
        $kpiUtama = $indicators['KPI-01'] ?? null;

        if (($kpiUtama['actual'] ?? null) !== null && ($kpiUtama['meets_benchmark'] ?? null) === false) {
            $recommendations[] = self::REKOMENDASI['krs'];
        }

        $factorKey = $dominantFactor['recommendation_key'] ?? null;

        if (is_string($factorKey) && isset(self::REKOMENDASI[$factorKey])) {
            $recommendations[] = self::REKOMENDASI[$factorKey];
        }

        $totalKrs = $this->value($record, 'total_krs');
        $kelompokKesejahteraanRendah = $this->value($record, 'kesejahteraan_1')
            + $this->value($record, 'kesejahteraan_2');

        if ($totalKrs > 0 && $kelompokKesejahteraanRendah > ($totalKrs / 2)) {
            $recommendations[] = self::REKOMENDASI['kesejahteraan'];
        }

        if (collect($indicators)->contains(fn (array $indicator): bool => ($indicator['status_tren'] ?? null) === 'Memburuk')) {
            $recommendations[] = self::REKOMENDASI['tren'];
        }

        if ($recommendations === []) {
            $recommendations[] = 'Pertahankan pemantauan berkala dan verifikasi konsistensi data pada periode berikutnya.';
        }

        return array_values(array_unique($recommendations));
    }

    /** @param RekapKrs|array<string, mixed> $record */
    private function value(RekapKrs|array $record, string $key): int
    {
        return (int) ($record instanceof RekapKrs ? $record->getAttribute($key) : ($record[$key] ?? 0));
    }
}
