@extends('layouts.app')
@section('title', 'Tambah Target Indikator')
@section('content')
    <div class="mx-auto max-w-5xl space-y-6">
        <header class="rounded-[2rem] border border-slate-200 bg-white px-6 py-8 shadow-[0_24px_80px_rgba(15,23,42,0.10)] sm:px-8"><p class="text-sm font-semibold tracking-[0.22em] text-[#1f4b75] uppercase">Target Indikator</p><h1 class="mt-3 text-3xl font-semibold text-[#1f3550] sm:text-4xl">Tambah Target</h1><p class="mt-4 text-sm leading-7 text-slate-600">Tetapkan tolak ukur KPI untuk tahun tertentu berdasarkan sumber regulatif atau keputusan internal.</p></header>
        <x-flash-messages />
        <form action="{{ route('admin.target-indikator.store') }}" method="post" class="space-y-6">@csrf @include('admin.target-indikator._form')<div class="flex flex-wrap justify-end gap-3 rounded-[1.5rem] border border-slate-200 bg-white p-5 shadow-sm"><a href="{{ route('admin.target-indikator.index') }}" class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-[#dbe6ef]">Batal</a><button type="submit" class="rounded-xl bg-[#1f4b75] px-5 py-3 text-sm font-medium text-white hover:bg-[#173a5c] focus:outline-none focus:ring-4 focus:ring-[#b9cddd]">Simpan Target</button></div></form>
    </div>
@endsection
