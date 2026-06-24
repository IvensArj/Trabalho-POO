{{-- Foto + identidade --}}
<div class="relative h-44 bg-zinc-900 overflow-hidden flex-shrink-0">

    {{-- Foto ou placeholder --}}
    <template x-if="selectedPerson?.photo">
        <img
            :src="selectedPerson.photo"
            :alt="selectedPerson.name"
            class="w-full h-full object-cover opacity-60"
        >
    </template>

    {{-- Overlay gradiente --}}
    <div class="absolute inset-0 bg-gradient-to-t from-zinc-950 via-zinc-950/60 to-transparent"></div>

    {{-- Botão fechar --}}
    <button
        @click="panelOpen = false; selectedPerson = null"
        class="absolute top-3 right-3 w-7 h-7 border border-white/10 bg-black/50 flex items-center justify-center hover:bg-white/10 transition"
    >
        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>

    {{-- Nome sobre a foto --}}
    <div class="absolute bottom-0 left-0 p-5">
        <h2
            x-text="selectedPerson?.name"
            class="text-2xl font-black text-white tracking-tight leading-none"
        ></h2>
        <p
            x-text="selectedPerson?.nickname ? '@' + selectedPerson.nickname : ''"
            class="text-zinc-400 text-sm mt-1"
        ></p>
    </div>
</div>

{{-- Corpo --}}
<div class="p-5 flex-1 overflow-y-auto space-y-5">

    {{-- Grupos --}}
    <div>
        <p class="text-[10px] uppercase tracking-[0.3em] text-zinc-600 mb-2">Grupos</p>
        <div class="flex flex-wrap gap-1.5">
            <template x-if="selectedPerson?.groups?.length > 0">
                <template x-for="group in selectedPerson.groups" :key="group">
                    <span
                        x-text="group"
                        class="px-2 py-0.5 text-xs border border-white/10 bg-white/[0.04] text-zinc-300"
                    ></span>
                </template>
            </template>
            <template x-if="!selectedPerson?.groups?.length">
                <span class="text-xs text-zinc-600">Sem grupos</span>
            </template>
        </div>
    </div>

    {{-- Ações --}}
    <div class="border-t border-white/10 pt-4 flex gap-2">
        <button class="flex-1 py-2 text-xs border border-white/10 text-zinc-300 hover:bg-white/5 transition">
            Ver detalhes
        </button>
        <button class="flex-1 py-2 text-xs bg-white text-black font-semibold hover:scale-[1.02] transition">
            Editar
        </button>
    </div>

</div>