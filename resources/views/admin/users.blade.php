<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Pengguna | Sistem Monev Stunting DP2KBP3A</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[#eef2f5] text-slate-900 antialiased">
        <main class="min-h-screen lg:flex">
            @include('partials.sidebar')

            <section class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-6xl space-y-6">
                    <div class="rounded-[2rem] border border-slate-200 bg-white px-6 py-8 shadow-[0_24px_80px_rgba(15,23,42,0.10)] sm:px-8">
                        <h1 class="mt-3 text-3xl font-semibold text-[#1f3550] sm:text-4xl">Kelola Pengguna</h1>
                    </div>

                    <div class="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
                        <section class="px-0 py-0">
                        @if (session('success'))
                            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                                {{ session('error') }}
                            </div>
                        @endif

                        <div class="rounded-[1.5rem] border border-slate-200 bg-slate-50 p-6">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="text-sm font-semibold text-[#1f3550]">{{ $editingUser ? 'Edit Pengguna' : 'Tambah Pengguna' }}</p>
                                    <h2 class="mt-1 text-xl font-semibold text-slate-900">Form akun login</h2>
                                </div>
                                @if($editingUser)
                                    <a href="{{ route('admin.users.index') }}" class="text-sm font-medium text-[#1f4b75] hover:text-[#173a5c]">Batal edit</a>
                                @endif
                            </div>

                            <form action="{{ $editingUser ? route('admin.users.update', $editingUser) : route('admin.users.store') }}" method="post" class="mt-6 space-y-4">
                                @csrf
                                @if ($editingUser)
                                    @method('PUT')
                                @endif

                                <div>
                                    <label class="mb-2 block text-sm font-medium text-slate-700" for="nama_lengkap">Nama lengkap</label>
                                    <input id="nama_lengkap" name="nama_lengkap" type="text" value="{{ old('nama_lengkap', $editingUser->nama_lengkap ?? '') }}" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition placeholder:text-slate-400 focus:border-[#1f4b75] focus:ring-4 focus:ring-[#dbe6ef]" placeholder="Nama lengkap pengguna">
                                    @error('nama_lengkap')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-medium text-slate-700" for="username">Username</label>
                                    <input id="username" name="username" type="text" value="{{ old('username', $editingUser->username ?? '') }}" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition placeholder:text-slate-400 focus:border-[#1f4b75] focus:ring-4 focus:ring-[#dbe6ef]" placeholder="Username untuk login">
                                    @error('username')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-medium text-slate-700" for="password">Kata sandi {{ $editingUser ? '(kosongkan jika tidak diganti)' : '' }}</label>
                                    <input id="password" name="password" type="password" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition placeholder:text-slate-400 focus:border-[#1f4b75] focus:ring-4 focus:ring-[#dbe6ef]" placeholder="Kata sandi login">
                                    @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-medium text-slate-700" for="role">Peran</label>
                                    <select id="role" name="role" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-[#1f4b75] focus:ring-4 focus:ring-[#dbe6ef]">
                                        <option value="Admin" @selected(old('role', $editingUser->role ?? '') === 'Admin')>Admin</option>
                                        <option value="PKK" @selected(old('role', $editingUser->role ?? '') === 'PKK')>PKK</option>
                                    </select>
                                    @error('role')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                </div>

                                <div class="flex flex-wrap gap-3 pt-2">
                                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-[#1f4b75] px-5 py-3 text-sm font-medium text-white transition hover:bg-[#173a5c]">
                                        {{ $editingUser ? 'Simpan Perubahan' : 'Simpan Pengguna' }}
                                    </button>
                                </div>
                            </form>
                        </div>

                        </div>

                        <div class="mt-6 overflow-hidden rounded-[1.5rem] border border-slate-200 bg-white">
                            <div class="border-b border-slate-200 px-6 py-4">
                                <h2 class="text-lg font-semibold text-slate-900">Daftar pengguna</h2>
                                <p class="mt-1 text-sm text-slate-500">Klik ubah untuk mengganti username, kata sandi, atau peran akun.</p>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                                    <thead class="bg-slate-50 text-slate-600">
                                        <tr>
                                            <th class="px-6 py-3 font-medium">ID</th>
                                            <th class="px-6 py-3 font-medium">Nama Lengkap</th>
                                            <th class="px-6 py-3 font-medium">Username</th>
                                            <th class="px-6 py-3 font-medium">Peran</th>
                                            <th class="px-6 py-3 font-medium">Dibuat</th>
                                            <th class="px-6 py-3 font-medium">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-200 bg-white">
                                        @forelse ($users as $user)
                                            <tr>
                                                <td class="px-6 py-4 text-slate-600">{{ $user->id_user }}</td>
                                                <td class="px-6 py-4 font-medium text-slate-900">{{ $user->nama_lengkap }}</td>
                                                <td class="px-6 py-4 text-slate-600">{{ $user->username }}</td>
                                                <td class="px-6 py-4 text-slate-600">{{ $user->role }}</td>
                                                <td class="px-6 py-4 text-slate-600">{{ $user->created_at ? \Illuminate\Support\Carbon::parse($user->created_at)->format('d-m-Y H:i') : '-' }}</td>
                                                <td class="px-6 py-4">
                                                    <div class="flex flex-wrap gap-2">
                                                        <a href="{{ route('admin.users.edit', $user) }}" class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-medium text-[#1f4b75] hover:bg-slate-50">Ubah</a>
                                                        <form action="{{ route('admin.users.destroy', $user) }}" method="post" onsubmit="return confirm('Yakin ingin menghapus pengguna ini?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="rounded-lg border border-red-200 px-3 py-2 text-xs font-medium text-red-600 hover:bg-red-50">Hapus</button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="px-6 py-8 text-center text-slate-500">Belum ada data pengguna.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        </section>
                    </div>
                </div>
            </section>
        </main>
    </body>
</html>
