@props([
    'excludeErrorRoutes' => [],
])

@php
    $hideErrors = collect($excludeErrorRoutes)->contains(fn ($pattern) => request()->routeIs($pattern));
    $showErrorToast = $errors->any() && !$hideErrors;
@endphp

<div
    x-data="{
        showSuccess: {{ session('success') ? 'true' : 'false' }},
        showError: {{ $showErrorToast ? 'true' : 'false' }},
        init() {
            if (this.showSuccess) setTimeout(() => this.showSuccess = false, 4000);
            if (this.showError) setTimeout(() => this.showError = false, 6000);
        }
    }"
    class="fixed bottom-6 right-6 z-50 flex flex-col gap-3 pointer-events-none"
>
    @if (session('success'))
        <div x-show="showSuccess" x-transition.duration.300ms class="pointer-events-auto bg-zinc-900/90 border border-emerald-500/30 text-emerald-300 px-5 py-4 rounded-xl shadow-2xl backdrop-blur-md max-w-sm flex items-start gap-3">
            <svg class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span class="text-sm font-medium leading-snug">{{ session('success') }}</span>
            <button type="button" @click="showSuccess = false" class="text-emerald-500/50 hover:text-emerald-500 transition ml-auto"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
        </div>
    @endif

    @if ($showErrorToast)
        <div x-show="showError" x-transition.duration.300ms class="pointer-events-auto bg-zinc-900/90 border border-red-500/30 text-red-300 px-5 py-4 rounded-xl shadow-2xl backdrop-blur-md max-w-sm flex items-start gap-3">
            <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div>
                <p class="text-sm font-bold mb-1.5 text-red-400">Não foi possível salvar:</p>
                <ul class="text-xs space-y-1 list-disc list-inside opacity-90 text-red-300/80">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <button type="button" @click="showError = false" class="text-red-500/50 hover:text-red-500 transition ml-auto"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
        </div>
    @endif
</div>