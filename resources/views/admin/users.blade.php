@extends('layouts.app')

@section('title', 'Pengguna')

@section('content')
    <div class="mx-auto max-w-6xl space-y-6">
        <header class="rounded-[2rem] border border-slate-200 bg-white px-6 py-8 shadow-[0_24px_80px_rgba(15,23,42,0.10)] sm:px-8">
            <p class="text-sm font-semibold tracking-[0.22em] text-[#1f4b75] uppercase">Administrasi</p>
            <h1 class="mt-3 text-3xl font-semibold text-[#1f3550] sm:text-4xl">Kelola Pengguna</h1>
            <p class="mt-4 max-w-3xl text-sm leading-7 text-slate-600 sm:text-base">
                Kelola akun Admin dan PKK yang dapat menggunakan sistem sesuai kewenangannya.
            </p>
        </header>

        <x-flash-messages />

        <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,0.8fr)_minmax(0,1.2fr)]">
            <section class="rounded-[1.5rem] border border-slate-200 bg-slate-50 p-6 shadow-sm" aria-labelledby="form-pengguna-title">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-[#1f3550]">{{ $editingUser ? 'Edit Pengguna' : 'Tambah Pengguna' }}</p>
                        <h2 id="form-pengguna-title" class="mt-1 text-xl font-semibold text-slate-900">Form akun login</h2>
                    </div>
                    @if ($editingUser)
                        <a href="{{ route('admin.users.index') }}" class="rounded-lg text-sm font-medium text-[#1f4b75] hover:text-[#173a5c] focus:outline-none focus:ring-4 focus:ring-[#dbe6ef]">Batal edit</a>
                    @endif
                </div>

                <form action="{{ $editingUser ? route('admin.users.update', $editingUser) : route('admin.users.store') }}" method="post" class="mt-6 space-y-4">
                    @csrf
                    @if ($editingUser)
                        @method('PUT')
                    @endif

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700" for="nama_lengkap">Nama lengkap</label>
                        <input id="nama_lengkap" name="nama_lengkap" type="text" value="{{ old('nama_lengkap', $editingUser->nama_lengkap ?? '') }}" maxlength="100" required autocomplete="name" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition placeholder:text-slate-400 focus:border-[#1f4b75] focus:ring-4 focus:ring-[#dbe6ef]" placeholder="Nama lengkap pengguna">
                        @error('nama_lengkap')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700" for="username">Username</label>
                        <input id="username" name="username" type="text" value="{{ old('username', $editingUser->username ?? '') }}" maxlength="50" required autocomplete="username" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition placeholder:text-slate-400 focus:border-[#1f4b75] focus:ring-4 focus:ring-[#dbe6ef]" placeholder="Username untuk login">
                        @error('username')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700" for="password">Kata sandi {{ $editingUser ? '(kosongkan jika tidak diganti)' : '' }}</label>
                        <input id="password" name="password" type="password" minlength="8" @required(! $editingUser) autocomplete="new-password" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition placeholder:text-slate-400 focus:border-[#1f4b75] focus:ring-4 focus:ring-[#dbe6ef]" placeholder="Minimal 8 karakter">
                        @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700" for="role">Peran</label>
                        <select id="role" name="role" required class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-[#1f4b75] focus:ring-4 focus:ring-[#dbe6ef]">
                            <option value="Admin" @selected(old('role', $editingUser->role ?? '') === 'Admin')>Admin</option>
                            <option value="PKK" @selected(old('role', $editingUser->role ?? '') === 'PKK')>PKK</option>
                        </select>
                        @error('role')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-[#1f4b75] px-5 py-3 text-sm font-medium text-white transition hover:bg-[#173a5c] focus:outline-none focus:ring-4 focus:ring-[#b9cddd] sm:w-auto">
                            {{ $editingUser ? 'Simpan Perubahan' : 'Simpan Pengguna' }}
                        </button>
                    </div>
                </form>
            </section>

            <section class="overflow-hidden rounded-[1.5rem] border border-slate-200 bg-white shadow-sm" aria-labelledby="daftar-pengguna-title">
                <div class="border-b border-slate-200 px-6 py-4">
                    <h2 id="daftar-pengguna-title" class="text-lg font-semibold text-slate-900">Daftar pengguna</h2>
                    <p class="mt-1 text-sm leading-6 text-slate-500">Ubah username, kata sandi, atau peran akun melalui tombol aksi.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-[52rem] divide-y divide-slate-200 text-left text-sm">
                        <caption class="sr-only">Daftar akun pengguna sistem</caption>
                        <thead class="bg-slate-50 text-slate-600">
                            <tr>
                                <th scope="col" class="px-6 py-3 font-medium">ID</th>
                                <th scope="col" class="px-6 py-3 font-medium">Nama Lengkap</th>
                                <th scope="col" class="px-6 py-3 font-medium">Username</th>
                                <th scope="col" class="px-6 py-3 font-medium">Peran</th>
                                <th scope="col" class="px-6 py-3 font-medium">Dibuat</th>
                                <th scope="col" class="px-6 py-3 font-medium">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @forelse ($users as $user)
                                <tr class="transition hover:bg-slate-50/70">
                                    <td class="px-6 py-4 text-slate-600">{{ $user->id_user }}</td>
                                    <th scope="row" class="px-6 py-4 font-medium text-slate-900">{{ $user->nama_lengkap }}</th>
                                    <td class="px-6 py-4 text-slate-600">{{ $user->username }}</td>
                                    <td class="px-6 py-4 text-slate-600">{{ $user->role }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-slate-600">{{ $user->created_at ? \Illuminate\Support\Carbon::parse($user->created_at)->format('d-m-Y H:i') : '-' }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-2">
                                            <a href="{{ route('admin.users.edit', $user) }}" class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-medium text-[#1f4b75] transition hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-[#dbe6ef]">Ubah</a>
                                            <form action="{{ route('admin.users.destroy', $user) }}" method="post" onsubmit="return confirm('Yakin ingin menghapus pengguna ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-lg border border-red-200 px-3 py-2 text-xs font-medium text-red-600 transition hover:bg-red-50 focus:outline-none focus:ring-4 focus:ring-red-100">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-10 text-center text-slate-500">Belum ada data pengguna.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
@endsection
