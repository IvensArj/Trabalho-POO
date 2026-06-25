<div
    x-cloak
    x-show="showCreateModal"
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
    x-transition.opacity
    @keydown.escape.window="showCreateModal = false"
>
    {{-- Overlay --}}
    <div class="fixed inset-0 bg-black/80 backdrop-blur-sm" @click="showCreateModal = false"></div>

    {{-- Modal --}}
    <div
        class="relative w-full max-w-md bg-zinc-950 border border-white/10 shadow-2xl text-white"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-90"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-90"
        @click.outside="showCreateModal = false"
    >
        {{-- Header --}}
        <div class="px-6 py-4 border-b border-white/10 flex items-center justify-between">
            <h2 class="text-sm font-bold uppercase tracking-widest">Novo Evento</h2>
            @if ($errors->any())
                <div class="bg-red-900/20 border border-red-800 text-red-300 px-4 py-3 text-xs">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <button @click="showCreateModal = false" class="text-zinc-500 hover:text-white transition p-1">
                <span class="w-4 h-4 inline-block" data-lucide="x"></span>
            </button>
        </div>

        {{-- Corpo --}}
        <div class="p-6 space-y-4">
            {{-- Nome --}}
            <div>
                <label class="text-[10px] uppercase tracking-widest text-zinc-500 block mb-1">Nome</label>
                <input
                    x-model="newEventLabel"
                    type="text"
                    required
                    class="w-full bg-white/5 border border-white/10 px-3 py-2 text-sm focus:outline-none focus:border-white/30 placeholder:text-zinc-600"
                    placeholder="Nome do evento"
                >
            </div>

            {{-- Data --}}
            <div>
                <label class="text-[10px] uppercase tracking-widest text-zinc-500 block mb-1">Data</label>
                <input
                    x-model="newEventDate"
                    type="date"
                    required
                    :min="minDate"
                    class="w-full bg-white/5 border border-white/10 px-3 py-2 text-sm focus:outline-none focus:border-white/30"
                >
            </div>

            {{-- Ícone --}}
            <div>
                <label class="text-[10px] uppercase tracking-widest text-zinc-500 block mb-2">Ícone</label>
                <div class="flex flex-wrap gap-2">
                    @foreach ([
                        // Originais
                        'calendar', 'star', 'heart', 'zap', 'gift', 'music', 'coffee', 'book', 'briefcase', 'sun', 'moon', 'smile',
                        // Saúde, Bem-estar e Comida
                        'activity', 'dumbbell', 'utensils', 'apple', 'pill', 'heart-pulse',
                        // Casa, Finanças e Trabalho
                        'home', 'shopping-cart', 'dollar-sign', 'credit-card', 'graduation-cap', 'wrench',
                        // Social, Viagem e Lazer
                        'users', 'map-pin', 'plane', 'car', 'party-popper', 'clapperboard',
                        // Alertas e Status
                        'bell', 'clock', 'check-circle', 'alert-triangle', 'flag', 'trophy',
                    ] as $icon)
                        <button
                            type="button"
                            @click="newEventIcon = '{{ $icon }}'"
                            :class="newEventIcon === '{{ $icon }}' ? 'bg-white text-black' : 'bg-white/5 text-zinc-400 hover:bg-white/10'"
                            class="w-9 h-9 rounded flex items-center justify-center transition"
                        >
                            <span
                                class="w-4 h-4 inline-block"
                                data-lucide="{{ $icon }}"
                            ></span>
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Notas --}}
            <div>
                <label class="text-[10px] uppercase tracking-widest text-zinc-500 block mb-1">Notas</label>
                <textarea
                    x-model="newEventNote"
                    rows="2"
                    class="w-full bg-white/5 border border-white/10 px-3 py-2 text-sm focus:outline-none focus:border-white/30 placeholder:text-zinc-600 resize-none"
                    placeholder="Detalhes..."
                ></textarea>
            </div>
        </div>

        {{-- Footer --}}
        <div class="px-6 py-4 border-t border-white/10 flex justify-end gap-2">
            <button
                type="button"
                @click="showCreateModal = false"
                class="px-4 py-2 text-xs border border-white/10 text-zinc-400 hover:text-white hover:bg-white/5 transition"
            >
                Cancelar
            </button>
            <button
                type="button"
                @click="saveEvent()"
                class="px-4 py-2 text-xs bg-white text-black font-bold uppercase tracking-wider hover:opacity-90 transition inline-flex items-center gap-2"
            >
                Salvar
            </button>
        </div>
    </div>
</div>