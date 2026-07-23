<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DataWilayahController extends Controller
{
    private function ensureAdmin(): void
    {
        abort_unless(Auth::check() && Auth::user()?->role === 'Admin', 403);
    }

    public function index(Request $request)
    {
        $this->ensureAdmin();

        $editingKecamatan = null;
        $editingKelurahan = null;

        if ($request->filled('edit_kecamatan')) {
            $editingKecamatan = Kecamatan::query()->findOrFail($request->input('edit_kecamatan'));
        }

        if ($request->filled('edit_kelurahan')) {
            $editingKelurahan = Kelurahan::query()->findOrFail($request->input('edit_kelurahan'));
        }

        $kecamatans = Kecamatan::query()
            ->withCount('kelurahans')
            ->orderBy('nama_kecamatan')
            ->get();

        $kelurahans = Kelurahan::query()
            ->with('kecamatan')
            ->orderBy('nama_kelurahan')
            ->get();

        return view('admin.data-wilayah', compact('kecamatans', 'kelurahans', 'editingKecamatan', 'editingKelurahan'));
    }

    public function storeKecamatan(Request $request)
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'kode_kecamatan' => ['required', 'string', 'max:20', 'unique:tb_kecamatan,kode_kecamatan'],
            'nama_kecamatan' => ['required', 'string', 'max:100'],
        ]);

        Kecamatan::create($validated);

        return redirect()->route('admin.data-wilayah.index')->with('success', 'Data kecamatan berhasil ditambahkan.');
    }

    public function updateKecamatan(Request $request, Kecamatan $kecamatan)
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'nama_kecamatan' => ['required', 'string', 'max:100'],
        ]);

        $kecamatan->update($validated);

        return redirect()->route('admin.data-wilayah.index')->with('success', 'Data kecamatan berhasil diperbarui.');
    }

    public function destroyKecamatan(Kecamatan $kecamatan)
    {
        $this->ensureAdmin();

        $kecamatan->delete();

        return redirect()->route('admin.data-wilayah.index')->with('success', 'Data kecamatan berhasil dihapus.');
    }

    public function storeKelurahan(Request $request)
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'kode_kelurahan' => ['required', 'string', 'max:20', 'unique:tb_kelurahan,kode_kelurahan'],
            'kode_kecamatan' => ['required', 'exists:tb_kecamatan,kode_kecamatan'],
            'nama_kelurahan' => ['required', 'string', 'max:100'],
        ]);

        Kelurahan::create($validated);

        return redirect()->route('admin.data-wilayah.index')->with('success', 'Data kelurahan berhasil ditambahkan.');
    }

    public function updateKelurahan(Request $request, Kelurahan $kelurahan)
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'kode_kecamatan' => ['required', 'exists:tb_kecamatan,kode_kecamatan'],
            'nama_kelurahan' => ['required', 'string', 'max:100'],
        ]);

        $kelurahan->update($validated);

        return redirect()->route('admin.data-wilayah.index')->with('success', 'Data kelurahan berhasil diperbarui.');
    }

    public function destroyKelurahan(Kelurahan $kelurahan)
    {
        $this->ensureAdmin();

        $kelurahan->delete();

        return redirect()->route('admin.data-wilayah.index')->with('success', 'Data kelurahan berhasil dihapus.');
    }
}