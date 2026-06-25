<div class="max-w-[1440px] mx-auto px-5 lg:px-10 pt-6 pb-4 relative z-10">
    <header class="flex flex-col gap-5">
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
            <div class="flex gap-3 justify ">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 grid place-items-center">
                        <span class="w-5 h-5 inline-block" data-lucide="globe"></span>
                    </div>
                    <div class="space-y-0.5">
                        <p class="text-[9px] uppercase tracking-[0.5em] text-zinc-600">Mind Palace</p>
                        <p class="text-[9px] uppercase tracking-[0.5em] text-zinc-700">Social</p>
                    </div>
                </div>

                <h1 class="text-[clamp(1.8rem,3.4vw,3.4rem)] font-black tracking-tight leading-[0.9] text-white">
                    MindSocial
                </h1>
            </div>

            <div class="grid gap-2 sm:grid-cols-2 lg:min-w-[420px]">
                <button type="button" x-on:click="$dispatch('open-modal', 'groups-modal')" class="h-9 px-3 border border-white/15 text-[10px] uppercase tracking-[0.3em] text-zinc-500 hover:text-white hover:border-white/30 transition">
                    Grupos
                </button>
                <button type="button" x-on:click="$dispatch('open-modal', 'person-modal')" class="h-9 px-3 bg-white text-black text-[10px] font-bold uppercase tracking-[0.3em] hover:opacity-90 transition">
                    Nova Pessoa
                </button>
            </div>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-white/10">
        </div>
    </header>
</div>
