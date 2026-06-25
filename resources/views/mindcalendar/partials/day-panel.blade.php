<div class="border border-white/10 bg-[#0b0b0b] p-4">
    <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
            <p class="text-[9px] uppercase tracking-[0.45em] text-zinc-600">Dia selecionado</p>
            <div class="mt-2 flex items-end gap-3">
                <p class="leading-none font-black text-white" style="font-size: 52px; line-height: 0.9;" x-text="selectedDay"></p>
                <div class="pb-1">
                    <p class="text-xs font-semibold text-zinc-300" x-text="selectedWeekday"></p>
                    <p class="text-[10px] text-zinc-600 mt-0.5" x-text="selectedMonthYear"></p>
                </div>
            </div>
        </div>
        <div class="w-3 h-3 bg-white shrink-0" id="todayPulse"></div>
    </div>
    <div class="my-4 border-t border-white/10"></div>
    <div class="min-h-[120px]" :class="{ 'max-h-[260px] overflow-y-auto custom-scrollbar': selectedEvents.length > 0 }">
        <p class="text-[9px] uppercase tracking-[0.4em] text-zinc-600 mb-3">Linha do tempo</p>
        <div x-show="selectedEvents.length === 0" class="flex flex-col items-center justify-center py-8 text-center">
            <div class="w-8 h-8 mb-3 grid place-items-center border border-white/10 bg-white/[0.02]">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.25)" stroke-width="1.5">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
            </div>
            <p class="text-zinc-700 text-xs">Nenhum momento registrado.</p>
            <button @click="openCreateModal()" class="mt-3 text-[9px] px-3 py-1.5 transition-all duration-300 hover:text-white border border-white/10 text-zinc-500">
                + Registrar
            </button>
        </div>
        <div x-show="selectedEvents.length > 0" class="space-y-3">
            <template x-for="event in selectedEvents" :key="event.id || event.label">
                <div class="timeline-entry">
                    <div
                        class="timeline-node"
                        :style="{
                            background:
                                event.type === 'birthday'
                                    ? '#4A9B8E'
                                    : event.type === 'event'
                                        ? '#8B7FD4'
                                        : '#C4A882',

                            boxShadow:
                                event.type === 'birthday'
                                    ? '0 0 6px #4A9B8E60'
                                    : event.type === 'event'
                                        ? '0 0 6px #8B7FD460'
                                        : '0 0 6px #C4A88260'
                        }"
                    ></div>
                    <div class="pb-4 flex-1 min-w-0 flex items-start gap-3">
                        {{-- Foto do aniversariante --}}
                        <img
                            x-show="event.photo_url"
                            :src="event.photo_url"
                            class="w-8 h-8 object-cover border border-white/10 flex-shrink-0"
                            @@error="$el.style.display='none'"
                            alt=""
                        >

                        {{-- Ícone do evento --}}
                        <div
                            x-show="!event.photo_url"
                            class="w-8 h-8 border border-white/10 bg-white/5 flex items-center justify-center flex-shrink-0 text-zinc-400"
                        >
                            <i
                                :data-lucide="event.icon || 'calendar'"
                                class="w-4 h-4"
                                x-init="$nextTick(() => window.lucide?.createIcons())"
                            ></i>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-white leading-tight cursor-pointer hover:underline" x-text="event.label" @click="openEvent(event)"></p>
                            <p class="text-xs text-zinc-500 mt-1 leading-relaxed" x-show="event.note" x-text="event.note"></p>
                            <span
                                class="inline-block mt-2 text-[9px] font-semibold px-2.5 py-0.5 uppercase tracking-wide border border-white/10 text-zinc-300"
                                x-text="event.type === 'birthday' ? 'Aniversário' : 'Evento'"
                            ></span>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>