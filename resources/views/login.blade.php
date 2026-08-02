<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#0b1f2a">

        <title>Masuk | Sistem Monev Stunting DP2KBP3A</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700|space-grotesk:500,700" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[#eef2f5] text-slate-900 antialiased">
        <main class="min-h-screen p-4 sm:p-6 lg:p-8">
            <section class="grid min-h-[calc(100vh-2rem)] overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-[0_24px_80px_rgba(15,23,42,0.10)] lg:grid-cols-[0.95fr_1.05fr]">
                <div class="flex items-center justify-center px-6 py-10 sm:px-10 lg:px-14">
                    <div class="w-full max-w-md">
                        <div class="flex items-center gap-3">
                            <img src="{{ asset('Logo.jpg') }}" alt="Logo DP2KBP3A" class="h-14 w-14 rounded-2xl object-cover ring-1 ring-slate-200">
                            <div>
                                <p class="text-xs font-semibold tracking-[0.22em] text-slate-600 uppercase">DP2KBP3A</p>
                                <p class="text-sm text-slate-500">Kabupaten Subang</p>
                            </div>
                        </div>

                        <div class="mt-10">
                            <p class="text-sm font-semibold tracking-[0.22em] text-[#244c6f] uppercase">Masuk</p>
                            <h1 class="mt-3 text-3xl font-semibold leading-tight text-[#1f3550] sm:text-4xl">
                                Sistem Login Pengelolaan Pengguna dan Data Wilayah
                            </h1>
                            <p class="mt-4 text-sm leading-6 text-slate-600 sm:text-base">
                                Gunakan akun yang didaftarkan admin untuk mengakses pengelolaan pengguna dan data wilayah.
                            </p>
                        </div>

                        <form action="{{ route('login.submit') }}" method="post" class="mt-8 space-y-4">
                            @csrf

                            @if ($errors->any())
                                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                                    {{ $errors->first() }}
                                </div>
                            @endif

                            <div>
                                <label for="username" class="mb-2 block text-sm font-medium text-slate-700">Username / ID Pengguna</label>
                                <input
                                    id="username"
                                    name="username"
                                    type="text"
                                    autocomplete="username"
                                    placeholder="Masukkan username atau ID pengguna"
                                    class="w-full rounded-xl border border-slate-300 bg-[#f8fafc] px-4 py-3 text-sm outline-none transition placeholder:text-slate-400 focus:border-[#1f4b75] focus:bg-white focus:ring-4 focus:ring-[#dbe6ef]"
                                    value="{{ old('username') }}"
                                >
                            </div>

                            <div>
                                <label for="password" class="mb-2 block text-sm font-medium text-slate-700">Kata sandi</label>
                                <input
                                    id="password"
                                    name="password"
                                    type="password"
                                    autocomplete="current-password"
                                    placeholder="Masukkan kata sandi"
                                    class="w-full rounded-xl border border-slate-300 bg-[#f8fafc] px-4 py-3 text-sm outline-none transition placeholder:text-slate-400 focus:border-[#1f4b75] focus:bg-white focus:ring-4 focus:ring-[#dbe6ef]"
                                >
                            </div>

                            <div class="flex items-center justify-between text-xs text-slate-500 sm:text-sm">
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" class="h-4 w-4 rounded border-slate-300 text-[#1f4b75] focus:ring-[#1f4b75]">
                                    Ingat saya
                                </label>
                            </div>

                            <button
                                type="submit"
                                class="inline-flex w-full items-center justify-center rounded-xl bg-[#1f4b75] px-5 py-3 text-sm font-medium text-white transition hover:bg-[#173a5c]"
                            >
                                Masuk
                            </button>
                        </form>

                        <p class="mt-6 text-xs leading-6 text-slate-500 sm:text-sm">
                            Jika lupa username atau kata sandi, hubungi admin dinas untuk reset akses.
                        </p>
                    </div>
                </div>

                <div class="relative hidden overflow-hidden bg-[linear-gradient(160deg,_#f9fbfc_0%,_#eef4f7_48%,_#e7eef2_100%)] lg:block">
                    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(31,75,117,0.10),_transparent_26%),radial-gradient(circle_at_bottom_left,_rgba(20,128,122,0.10),_transparent_30%)]"></div>
                    <div class="relative flex h-full flex-col justify-between p-10">
                        <div class="flex justify-end">
                            <span class="rounded-full border border-slate-200 bg-white/85 px-4 py-2 text-xs font-medium tracking-[0.18em] text-slate-600 uppercase backdrop-blur">
                                Sistem Resmi
                            </span>
                        </div>

                        <div class="flex flex-1 items-center justify-center">
                            <div class="max-w-md text-center">
                                <img src="{{ asset('Logo.jpg') }}" alt="Logo DP2KBP3A" class="mx-auto h-44 w-44 rounded-[2rem] object-cover shadow-[0_18px_50px_rgba(15,23,42,0.12)] ring-1 ring-slate-200">
                                <h2 class="mt-8 text-2xl font-semibold text-[#1f3550]">
                                    DP2KBP3A Kabupaten Subang
                                </h2>
                                <p class="mt-4 text-sm leading-7 text-slate-600">
                                    Akses sistem ini dikelola oleh admin dinas untuk mendukung pengelolaan pengguna dan data wilayah.
                                </p>

                                <div class="mt-8 grid grid-cols-3 gap-3 text-left text-xs text-slate-500">
                                    <div class="rounded-2xl bg-white/90 p-4 shadow-sm ring-1 ring-slate-200/80">
                                        <p class="font-semibold text-[#1f3550]">Data</p>
                                        <p class="mt-1 leading-5">Terpusat</p>
                                    </div>
                                    <div class="rounded-2xl bg-white/90 p-4 shadow-sm ring-1 ring-slate-200/80">
                                        <p class="font-semibold text-[#1f3550]">Akses</p>
                                        <p class="mt-1 leading-5">Terkontrol</p>
                                    </div>
                                    <div class="rounded-2xl bg-white/90 p-4 shadow-sm ring-1 ring-slate-200/80">
                                        <p class="font-semibold text-[#1f3550]">Laporan</p>
                                        <p class="mt-1 leading-5">Tertib</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 text-sm text-slate-500">
                            <span class="h-2 w-2 rounded-full bg-[#1f4b75]"></span>
                            Sistem aktif untuk pengguna berwenang
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </body>
</html>