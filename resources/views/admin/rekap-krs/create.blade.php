@extends('layouts.app')

@section('title', 'Tambah Data KRS')

@section('content')
    <div class="mx-auto max-w-6xl space-y-6">
        <header class="rounded-[2rem] border border-slate-200 bg-white px-6 py-8 shadow-[0_24px_80px_rgba(15,23,42,0.10)] sm:px-8">
            <p class="text-sm font-semibold tracking-[0.22em] text-[#1f4b75] uppercase">Data KRS</p>
            <h1 class="mt-3 text-3xl font-semibold text-[#1f3550] sm:text-4xl">Tambah Rekap KRS</h1>
            <p class="mt-4 max-w-3xl text-sm leading-7 text-slate-600">Masukkan data langsung sesuai rekap penelitian kecamatan, cantumkan sumbernya, dan tandai dengan jelas apabila data bersifat simulasi.</p>
        </header>
        <x-flash-messages />
        <form action="{{ route('admin.rekap-krs.store') }}" method="post" class="space-y-6">
            @csrf
            @include('admin.rekap-krs._form')
            <div class="flex flex-wrap justify-end gap-3 rounded-[1.5rem] border border-slate-200 bg-white p-5 shadow-sm">
                <a href="{{ route('admin.rekap-krs.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-5 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-[#dbe6ef]">Batal</a>
                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-[#1f4b75] px-5 py-3 text-sm font-medium text-white transition hover:bg-[#173a5c] focus:outline-none focus:ring-4 focus:ring-[#b9cddd]">Simpan Data KRS</button>
            </div>
        </form>
    </div>
@endsection
