<div class="border border-white/10 bg-[#0b0b0b] p-4 mt-2">
    <p class="text-[9px] uppercase tracking-[0.45em] text-zinc-600 mb-4">Resumo do mês</p>
    <div class="space-y-2.5">
        <div class="flex items-center justify-between gap-3">
            <span class="text-xs text-zinc-500">Memórias</span>
            <div class="flex items-center gap-2">
                <div class="h-px w-14 bg-white/25"></div>
                <span class="text-xs font-bold text-white" x-text="stats.memories"></span>
            </div>
        </div>
        <div class="flex items-center justify-between gap-3">
            <span class="text-xs text-zinc-500">Aniversários</span>
            <div class="flex items-center gap-2">
                <div class="h-px w-10 bg-zinc-400/50"></div>
                <span class="text-xs font-bold text-zinc-300" x-text="stats.birthdays"></span>
            </div>
        </div>
        <div class="flex items-center justify-between gap-3">
            <span class="text-xs text-zinc-500">Eventos</span>
            <div class="flex items-center gap-2">
                <div class="h-px w-10 bg-zinc-600/50"></div>
                <span class="text-xs font-bold text-zinc-400" x-text="stats.events"></span>
            </div>
        </div>
    </div>
</div>

<div class="border border-white/10 bg-[#0b0b0b] p-4">
    <p class="text-[9px] uppercase tracking-[0.3em] text-zinc-600 mb-4">Em breve</p>
    <div class="space-y-3">
        <template x-for="item in upcoming" :key="item.date">
            <div class="flex gap-3" @click="selectDay(item.date)">
                <div class="w-9 h-9 flex items-center justify-center text-[11px] font-black flex-shrink-0 border border-white/10 bg-white/[0.02]" x-text="item.day"></div>
                <div class="min-w-0 py-0.5">
                    <p class="text-xs font-medium text-zinc-300 truncate" x-text="item.label"></p>
                    <p class="text-[9px] text-zinc-600 mt-0.5 uppercase tracking-[0.22em]" x-text="item.subtitle"></p>
                </div>
            </div>
        </template>
        <p x-show="upcoming.length === 0" class="text-xs text-zinc-700 py-2">Nada em breve por aqui.</p>
    </div>
</div>