<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTargetIndikatorRequest;
use App\Http\Requests\UpdateTargetIndikatorRequest;
use App\Models\TargetIndikator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TargetIndikatorController extends Controller
{
    private const PER_PAGE = 15;

    public function index(Request $request): View
    {
        $searchInput = $request->query('search', '');
        $search = is_string($searchInput) ? trim($searchInput) : '';

        $tahunInput = $request->query('tahun');
        $tahun = is_numeric($tahunInput) ? (int) $tahunInput : null;

        $kodeInput = $request->query('kode_indikator');
        $kodeIndikator = is_string($kodeInput) && array_key_exists($kodeInput, TargetIndikator::INDIKATOR)
            ? $kodeInput
            : null;

        $jenisInput = $request->query('jenis_target');
        $jenisTarget = is_string($jenisInput) && in_array($jenisInput, ['Regulatif', 'Internal'], true)
            ? $jenisInput
            : null;

        $statusInput = $request->query('status_aktif');
        $statusAktif = is_string($statusInput) && in_array($statusInput, ['0', '1'], true)
            ? $statusInput
            : null;

        $sortInput = $request->query('sort', 'tahun');
        $sortColumns = [
            'tahun' => 'tahun_berlaku',
            'indikator' => 'kode_indikator',
            'nilai_target' => 'nilai_target',
            'status' => 'status_aktif',
        ];
        $sort = is_string($sortInput) && array_key_exists($sortInput, $sortColumns)
            ? $sortInput
            : 'tahun';

        $directionInput = $request->query('direction');
        $defaultDirection = $sort === 'tahun' ? 'desc' : 'asc';
        $direction = is_string($directionInput) && in_array($directionInput, ['asc', 'desc'], true)
            ? $directionInput
            : $defaultDirection;

        $targetIndikators = TargetIndikator::query()
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $searchQuery) use ($search): void {
                    $searchQuery
                        ->where('kode_indikator', 'like', "%{$search}%")
                        ->orWhere('nama_indikator', 'like', "%{$search}%")
                        ->orWhere('sumber_target', 'like', "%{$search}%");
                });
            })
            ->when($tahun !== null, fn (Builder $query): Builder => $query->where('tahun_berlaku', $tahun))
            ->when($kodeIndikator !== null, fn (Builder $query): Builder => $query->where('kode_indikator', $kodeIndikator))
            ->when($jenisTarget !== null, fn (Builder $query): Builder => $query->where('jenis_target', $jenisTarget))
            ->when($statusAktif !== null, fn (Builder $query): Builder => $query->where('status_aktif', $statusAktif === '1'))
            ->orderBy($sortColumns[$sort], $direction)
            ->orderBy('kode_indikator')
            ->orderBy('id_target')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        $tahunOptions = TargetIndikator::query()
            ->select('tahun_berlaku')
            ->distinct()
            ->orderByDesc('tahun_berlaku')
            ->pluck('tahun_berlaku');

        $indikatorOptions = TargetIndikator::INDIKATOR;
        $filters = [
            'search' => $search,
            'tahun' => $tahun,
            'kode_indikator' => $kodeIndikator,
            'jenis_target' => $jenisTarget,
            'status_aktif' => $statusAktif,
            'sort' => $sort,
            'direction' => $direction,
        ];
        $sortOptions = [
            'tahun' => 'Tahun berlaku',
            'indikator' => 'Indikator',
            'nilai_target' => 'Nilai target',
            'status' => 'Status aktif',
        ];

        return view('admin.target-indikator.index', compact(
            'targetIndikators',
            'tahunOptions',
            'indikatorOptions',
            'filters',
            'sortOptions'
        ));
    }

    public function create(): View
    {
        return view('admin.target-indikator.create', $this->formOptions());
    }

    public function store(StoreTargetIndikatorRequest $request): RedirectResponse
    {
        $validated = $this->withIndicatorName($request->validated());

        DB::transaction(static function () use ($validated): void {
            TargetIndikator::query()->create($validated);
        });

        return redirect()
            ->route('admin.target-indikator.index')
            ->with('success', 'Target indikator berhasil ditambahkan.');
    }

    public function show(TargetIndikator $targetIndikator): View
    {
        return view('admin.target-indikator.show', compact('targetIndikator'));
    }

    public function edit(TargetIndikator $targetIndikator): View
    {
        return view('admin.target-indikator.edit', [
            'targetIndikator' => $targetIndikator,
            ...$this->formOptions(),
        ]);
    }

    public function update(UpdateTargetIndikatorRequest $request, TargetIndikator $targetIndikator): RedirectResponse
    {
        $validated = $this->withIndicatorName($request->validated());

        DB::transaction(static function () use ($targetIndikator, $validated): void {
            $targetIndikator->update($validated);
        });

        return redirect()
            ->route('admin.target-indikator.index')
            ->with('success', 'Target indikator berhasil diperbarui.');
    }

    public function destroy(TargetIndikator $targetIndikator): RedirectResponse
    {
        DB::transaction(static function () use ($targetIndikator): void {
            $targetIndikator->delete();
        });

        return redirect()
            ->route('admin.target-indikator.index')
            ->with('success', 'Target indikator berhasil dihapus.');
    }

    /**
     * Build the common form options.
     *
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return [
            'indikatorOptions' => TargetIndikator::INDIKATOR,
            'arahTargetOptions' => [
                'Minimize' => 'Minimize',
                'Maximize' => 'Maximize',
            ],
            'jenisTargetOptions' => [
                'Regulatif' => 'Regulatif',
                'Internal' => 'Internal',
            ],
            'tahunDefault' => now()->year,
        ];
    }

    /**
     * Add the canonical indicator name to validated form data.
     *
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function withIndicatorName(array $validated): array
    {
        $validated['nama_indikator'] = TargetIndikator::INDIKATOR[$validated['kode_indikator']];

        return $validated;
    }
}
