<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
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

    public function edit(User $user): RedirectResponse
    {
        return redirect()->route('admin.users.index', ['edit' => $user->id_user]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username'],
            'password' => ['required', Password::min(8)],
            'role' => ['required', Rule::in(User::roles())],
        ], $this->validationMessages());

        User::create($validated);

        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', Rule::unique('users', 'username')->ignore($user->id_user, 'id_user')],
            'password' => ['nullable', Password::min(8)],
            'role' => ['required', Rule::in(User::roles())],
        ], $this->validationMessages());

        if ($request->user()?->is($user) && $validated['role'] !== User::ROLE_ADMIN) {
            return redirect()
                ->route('admin.users.index', ['edit' => $user->id_user])
                ->withInput($request->except('password'))
                ->with('error', 'Peran akun yang sedang digunakan tidak dapat diubah dari Admin.');
        }

        $user->nama_lengkap = $validated['nama_lengkap'];
        $user->username = $validated['username'];
        $user->role = $validated['role'];

        if (! empty($validated['password'])) {
            $user->password = $validated['password'];
        }

        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()?->is($user)) {
            return redirect()->route('admin.users.index')->with('error', 'Akun yang sedang digunakan tidak dapat dihapus.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil dihapus.');
    }

    /**
     * Get validation messages for user management.
     *
     * @return array<string, string>
     */
    private function validationMessages(): array
    {
        return [
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'nama_lengkap.string' => 'Nama lengkap harus berupa teks.',
            'nama_lengkap.max' => 'Nama lengkap maksimal :max karakter.',
            'username.required' => 'Username wajib diisi.',
            'username.string' => 'Username harus berupa teks.',
            'username.max' => 'Username maksimal :max karakter.',
            'username.unique' => 'Username sudah digunakan.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.string' => 'Kata sandi harus berupa teks.',
            'password.min' => 'Kata sandi minimal :min karakter.',
            'role.required' => 'Peran wajib dipilih.',
            'role.in' => 'Peran yang dipilih tidak valid.',
        ];
    }
}
