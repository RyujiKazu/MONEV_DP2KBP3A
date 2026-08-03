@props(['showValidationSummary' => true])

@php($errorBag = $errors ?? new \Illuminate\Support\ViewErrorBag())

@if (session('success') || session('error') || ($showValidationSummary && $errorBag->any()))
    <section class="space-y-3" aria-label="Pemberitahuan">
        @if (session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status" aria-live="polite">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                {{ session('error') }}
            </div>
        @endif

        @if ($showValidationSummary && $errorBag->any())
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                Periksa kembali isian formulir. Ada data yang belum valid.
            </div>
        @endif
    </section>
@endif
