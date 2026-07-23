<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    private function ensureAdmin(): void
    {
        abort_unless(Auth::check() && Auth::user()?->role === 'Admin', 403);
    }

    public function index(Request $request)
    {
        $this->ensureAdmin();

        $editingUser = null;

        if ($request->filled('edit')) {
            $editingUser = User::findOrFail($request->integer('edit'));
        }

        $users = User::query()
            ->orderBy('role')
            ->orderBy('nama_lengkap')
            ->get();

        return view('admin.users', compact('users', 'editingUser'));
    }

    public function edit(User $user)
    {
        $this->ensureAdmin();

        return redirect()->route('admin.users.index', ['edit' => $user->id_user]);
    }

    public function store(Request $request)
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username'],
            'password' => ['required', 'string', 'min:3'],
            'role' => ['required', Rule::in(['Admin', 'PKK'])],
        ]);

        User::create($validated);

        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function update(Request $request, User $user)
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', Rule::unique('users', 'username')->ignore($user->id_user, 'id_user')],
            'password' => ['nullable', 'string', 'min:3'],
            'role' => ['required', Rule::in(['Admin', 'PKK'])],
        ]);

        $user->nama_lengkap = $validated['nama_lengkap'];
        $user->username = $validated['username'];
        $user->role = $validated['role'];

        if (! empty($validated['password'])) {
            $user->password = $validated['password'];
        }

        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        $this->ensureAdmin();

        if (Auth::user()?->id_user === $user->id_user) {
            return redirect()->route('admin.users.index')->with('error', 'Akun yang sedang digunakan tidak dapat dihapus.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil dihapus.');
    }
}
