@props([
    'title' => 'Belum ada data',
    'description' => null,
])

<div {{ $attributes->class(['rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center']) }}>
    <svg aria-hidden="true" class="mx-auto h-10 w-10 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 5.25A2.25 2.25 0 0 1 6 3h8.25L20.25 9v9.75A2.25 2.25 0 0 1 18 21H6a2.25 2.25 0 0 1-2.25-2.25V5.25Z" />
        <path stroke-linecap="round" stroke-linejoin="round" d="M14.25 3v6h6M8 14h8M8 17h5" />
    </svg>
    <p class="mt-4 text-sm font-semibold text-slate-800">{{ $title }}</p>
    @if ($description)
        <p class="mx-auto mt-2 max-w-xl text-sm leading-6 text-slate-500">{{ $description }}</p>
    @endif
    @if (trim((string) $slot) !== '')
        <div class="mt-5">{{ $slot }}</div>
    @endif
</div>
