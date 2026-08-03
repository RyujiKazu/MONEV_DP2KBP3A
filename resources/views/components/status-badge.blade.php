@props([
    'status',
    'tone' => null,
])

@php
    $normalizedTone = strtolower((string) ($tone ?? $status));

    $toneClasses = match (true) {
        in_array($normalizedTone, ['aktual'], true) => 'border-blue-200 bg-blue-50 text-blue-700',
        in_array($normalizedTone, ['simulasi'], true) => 'border-amber-300 bg-amber-50 text-amber-900',
        in_array($normalizedTone, ['campuran'], true) => 'border-violet-200 bg-violet-50 text-violet-700',
        in_array($normalizedTone, ['hijau', 'terkendali', 'membaik', 'prioritas rendah'], true) => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        in_array($normalizedTone, ['kuning', 'perlu perhatian', 'tetap', 'prioritas sedang'], true) => 'border-amber-200 bg-amber-50 text-amber-800',
        in_array($normalizedTone, ['merah', 'prioritas', 'memburuk', 'prioritas tinggi'], true) => 'border-red-200 bg-red-50 text-red-700',
        default => 'border-slate-200 bg-slate-100 text-slate-700',
    };
@endphp

<span {{ $attributes->class(['inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold', $toneClasses]) }}>
    {{ trim((string) $slot) !== '' ? $slot : $status }}
</span>
