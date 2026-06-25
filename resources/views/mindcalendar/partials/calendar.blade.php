<div class="relative z-10">
    <div class="grid grid-cols-7 mb-1 px-1">
        <template x-for="label in ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb']">
            <div class="py-2 text-center text-[9px] uppercase tracking-[0.22em] text-zinc-600 border-b border-white/10" x-text="label"></div>
        </template>
    </div>
    <div class="grid grid-cols-7 gap-1">
        <template x-for="cell in cells" :key="cell.date">
            <div
                class="cal-cell relative text-left border border-white/10 bg-[#0b0b0b] px-2 py-2 min-h-[82px] overflow-visible transition"
                :class="{
                    'opacity-35': !cell.currentMonth,
                    'today': cell.today,
                    'has-birthday': cell.events.some(e => e.type === 'birthday'),
                    'has-event': cell.events.some(e => e.type === 'event'),
                    'has-memory': cell.events.some(e => e.type === 'memory'),
                }"
                @click="cell.currentMonth && selectDay(cell.date)"
            >
                <span class="selected-marker" x-show="cell.date === selectedDate"></span>
                <span class="day-num relative z-10" x-text="cell.day"></span>
                
                <!-- O x-show garante que o botão só apareça no dia selecionado -->
                <button x-show="cell.date === selectedDate"
                        @click.stop="openCreateModal()" 
                        class="absolute bottom-1 right-2 z-20 text-white/50 hover:text-white">
                    +
                </button>
                
                <div class="event-dots relative z-10" x-show="cell.events.length > 0">
                    <template x-for="event in cell.events">
                        <span
                            class="event-dot"
                            :style="{
                                background:
                                    event.type === 'birthday'
                                        ? '#4A9B8E'
                                        : event.type === 'event'
                                            ? '#8B7FD4'
                                            : '#C4A882',

                                boxShadow:
                                    event.type === 'birthday'
                                        ? '0 0 8px #4A9B8E'
                                        : event.type === 'event'
                                            ? '0 0 8px #8B7FD4'
                                            : '0 0 8px #C4A882'
                            }"
                        ></span>
                    </template>
                </div>
            </div>
        </template>
    </div>
</div>