<div class="max-w-[1440px] mx-auto px-5 lg:px-10 pt-6 pb-4 relative z-10">
    <header class="flex flex-col gap-5">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="flex justify-center gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 grid place-items-center">
                        <span class="w-5 h-5 inline-block" data-lucide="calendar"></span>
                    </div>
                    <div class="space-y-0.5">
                        <p class="text-[9px] uppercase tracking-[0.5em] text-zinc-600">Mind Palace</p>
                        <p class="text-[9px] uppercase tracking-[0.5em] text-zinc-700">Calendar</p>
                    </div>
                </div>

                <h1 class="flex items-end justify text-[clamp(1.8rem,3.4vw,3.4rem)] font-black tracking-tight leading-[0.9]">
                    <span class="block" x-text="monthLabel"></span>
                    <span class="text-[clamp(0.75rem,1.1vw,1rem)] block text-zinc-700 font-normal tracking-wide" style="font-family: 'Inter', sans-serif; font-style: italic;" x-text="year"></span>
                </h1>
            </div>

            <div class="grid gap-2 grid-cols-3 lg:min-w-[420px]">
                <div class="flex flex-col justify-between border border-white/10 bg-black px-2 py-1 select-none">
                    <p class="text-[9px] font-medium uppercase tracking-[0.15em] text-zinc-500 leading-none">Memórias</p>
                    <p class="text-2xl font-white text-white leading-none mt-1 text-right" x-text="stats.memories"></p>
                </div>
                <div class="flex flex-col justify-between border border-white/10 bg-black px-2 py-1 select-none">
                    <p class="text-[9px] font-medium uppercase tracking-[0.15em] text-zinc-500 leading-none">Aniversários</p>
                    <p class="text-2xl font-white text-white leading-none mt-1 text-right" x-text="stats.birthdays"></p>
                </div>
                <div class="flex flex-col justify-between border border-white/10 bg-black px-2 py-1 select-none">
                    <p class="text-[9px] font-medium uppercase tracking-[0.15em] text-zinc-500 leading-none">Eventos</p>
                    <p class="text-2xl font-white text-white leading-none mt-1 text-right" x-text="stats.events"></p>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-white/10 pt-4">
            <p class="text-[9px] uppercase tracking-[0.32em] text-zinc-600">Compacto, prático e direto.</p>
            <div class="flex items-center gap-2">
                <button type="button" @click="goToToday()" class="h-9 px-3 border border-white/15 text-[10px] uppercase tracking-[0.3em] text-zinc-500 hover:text-white hover:border-white/30 transition">Hoje</button>
                <div class="flex h-9 overflow-hidden border border-white/15">
                    <button type="button" @click="changeMonth(-1)" class="w-9 border-r border-white/15 text-zinc-500 hover:text-white hover:bg-white/[0.04] transition">‹</button>
                    <button type="button" @click="changeMonth(1)" class="w-9 text-zinc-500 hover:text-white hover:bg-white/[0.04] transition">›</button>
                </div>
            </div>
        </div>
    </header>
</div>