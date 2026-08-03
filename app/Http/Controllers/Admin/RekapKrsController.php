<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRekapKrsRequest;
use App\Http\Requests\UpdateRekapKrsRequest;
use App\Models\Kecamatan;
use App\Models\RekapKrs;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RekapKrsController extends Controller
{
    private const PER_PAGE = 15;

    public function index(Request $request): View
    {
        $searchInput = $request->query('search', '');
        $search = is_string($searchInput) ? trim($searchInput) : '';

        $tahunInput = $request->query('tahun');
        $tahun = is_numeric($tahunInput) ? (int) $tahunInput : null;

        $sortInput = $request->query('sort', 'tahun');
        $sort = is_string($sortInput) && in_array($sortInput, ['kecamatan', 'tahun'], true)
            ? $sortInput
            : 'tahun';

        $directionInput = $request->query('direction');
        $defaultDirection = $sort === 'tahun' ? 'desc' : 'asc';
        $direction = is_string($directionInput) && in_array($directionInput, ['asc', 'desc'], true)
            ? $directionInput
            : $defaultDirection;

        $query = RekapKrs::query()
            ->with([
                'kecamatan:kode_kecamatan,nama_kecamatan',
                'pembuat:id_user,nama_lengkap,username',
            ])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->whereHas('kecamatan', function (Builder $kecamatanQuery) use ($search): void {
                    $kecamatanQuery
                        ->where('nama_kecamatan', 'like', "%{$search}%")
                        ->orWhere('kode_kecamatan', 'like', "%{$search}%");
                });
            })
            ->when($tahun !== null, fn (Builder $query): Builder => $query->where('tahun', $tahun));

        $namaKecamatanSubquery = Kecamatan::query()
            ->select('nama_kecamatan')
            ->whereColumn('tb_kecamatan.kode_kecamatan', 'tb_rekap_krs.kode_kecamatan');

        if ($sort === 'kecamatan') {
            $query
                ->orderBy($namaKecamatanSubquery, $direction)
                ->orderByDesc('tahun');
        } else {
            $query
                ->orderBy('tahun', $direction)
                ->orderBy($namaKecamatanSubquery, 'asc');
        }

        $rekapKrs = $query
            ->orderBy('id_rekap')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        $kecamatans = Kecamatan::query()
            ->orderBy('nama_kecamatan')
            ->get(['kode_kecamatan', 'nama_kecamatan']);

        $tahunOptions = RekapKrs::query()
            ->select('tahun')
            ->distinct()
            ->orderByDesc('tahun')
            ->pluck('tahun');

        $filters = compact('search', 'tahun', 'sort', 'direction');
        $sortOptions = [
            'tahun' => 'Tahun',
            'kecamatan' => 'Kecamatan',
        ];

        return view('admin.rekap-krs.index', compact(
            'rekapKrs',
            'kecamatans',
            'tahunOptions',
            'filters',
            'sortOptions'
        ));
    }

    public function create(): View
    {
        $kecamatans = Kecamatan::query()
            ->orderBy('nama_kecamatan')
            ->get(['kode_kecamatan', 'nama_kecamatan']);

        $tahunDefault = now()->year;

        return view('admin.rekap-krs.create', compact('kecamatans', 'tahunDefault'));
    }

    public function store(StoreRekapKrsRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['is_simulasi'] = $request->boolean('is_simulasi');
        $validated['created_by'] = $request->user()?->getAuthIdentifier();

        DB::transaction(static function () use ($validated): void {
            RekapKrs::query()->create($validated);
        });

        return redirect()
            ->route('admin.rekap-krs.index')
            ->with('success', 'Data KRS berhasil ditambahkan.');
    }

    public function show(RekapKrs $rekapKrs): View
    {
        $rekapKrs->loadMissing([
            'kecamatan:kode_kecamatan,nama_kecamatan',
            'pembuat:id_user,nama_lengkap,username',
        ]);

        return view('admin.rekap-krs.show', compact('rekapKrs'));
    }

    public function edit(RekapKrs $rekapKrs): View
    {
        $rekapKrs->loadMissing([
            'kecamatan:kode_kecamatan,nama_kecamatan',
            'pembuat:id_user,nama_lengkap,username',
        ]);

        $kecamatans = Kecamatan::query()
            ->orderBy('nama_kecamatan')
            ->get(['kode_kecamatan', 'nama_kecamatan']);

        return view('admin.rekap-krs.edit', compact('rekapKrs', 'kecamatans'));
    }

    public function update(UpdateRekapKrsRequest $request, RekapKrs $rekapKrs): RedirectResponse
    {
        $validated = $request->validated();

        if ($request->has('is_simulasi')) {
            $validated['is_simulasi'] = $request->boolean('is_simulasi');
        }

        DB::transaction(static function () use ($rekapKrs, $validated): void {
            $rekapKrs->update($validated);
        });

        return redirect()
            ->route('admin.rekap-krs.index')
            ->with('success', 'Data KRS berhasil diperbarui.');
    }

    public function destroy(RekapKrs $rekapKrs): RedirectResponse
    {
        DB::transaction(static function () use ($rekapKrs): void {
            $rekapKrs->delete();
        });

        return redirect()
            ->route('admin.rekap-krs.index')
            ->with('success', 'Data KRS berhasil dihapus.');
    }
}
