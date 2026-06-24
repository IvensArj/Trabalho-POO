<x-app-layout>
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.25); }
    </style>
    <div
        x-data="{
            photoPreview:null,
            compressing: false,

            selectedPerson: null,
            panelOpen: false,
            async handlePhotoChange(event) {
                const file = event.target.files[0];
                if (!file) {
                    this.photoPreview = null;
                    return;
                }
                this.photoPreview = URL.createObjectURL(file);
                if (file.size > 3 * 1024 * 1024) {
                    this.compressing = true;
                    try {
                        const compressed = await window.compressImage(file, 3 * 1024 * 1024, 1280);
                        const dt = new DataTransfer();
                        dt.items.add(compressed);
                        event.target.files = dt.files;
                        this.photoPreview = URL.createObjectURL(compressed);
                    } catch (err) {
                        console.error('Erro ao comprimir:', err);
                    } finally {
                        this.compressing = false;
                    }
                }
            }
        }"
        @person-selected.window="
            selectedPerson = $event.detail;
            photoPreview = null;
            panelOpen = true;

            window.dispatchEvent(
                new CustomEvent('fisheye-focus', {
                    detail: { id: $event.detail.id }
                })
            );
        "
        @keyup.escape.window="selectedPerson = null; panelOpen = false; window.dispatchEvent(new CustomEvent('fisheye-unfocus'))"
    >

        <div class="max-w-7xl mx-auto px-6 py-6">
            {{-- Header --}}
            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6 mb-10">
                <div>
                    <span class="text-xs uppercase tracking-[0.3em] text-zinc-600">
                        Mind Palace
                    </span>
                    <h1 class="text-5xl font-black tracking-tight text-white mt-3">
                        MindSocial
                    </h1>
                    <p class="text-zinc-400 mt-3 max-w-xl">
                        Pessoas, conexões e relações que fazem parte da sua história.
                    </p>
                </div>
                <div class="flex gap-3">
                    <button
                        type="button" x-on:click="$dispatch('open-modal', 'groups-modal')"
                        class="px-4 py-2 border border-white/10 bg-white/[0.03] text-white text-sm hover:bg-white/[0.06] transition">
                        Grupos
                    </button>
                    <button
                        type="button" x-on:click="$dispatch('open-modal', 'person-modal')"
                        class="px-5 py-3 bg-white text-black font-semibold text-sm hover:scale-[1.02] transition">
                        Nova Pessoa
                    </button>
                </div>
            </div>

            <div class="grid lg:grid-cols-[1fr_400px] gap-10">
                {{-- Conteúdo principal --}}
                <div class="relative overflow-hidden border border-white/10">

                    @if ($people->isEmpty())
                        <div class="relative z-10 h-full flex flex-col items-center justify-center text-center px-6">
                            <div class="w-24 h-24 mx-auto mb-8 border border-white/10 bg-white/[0.03] flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-zinc-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                                    <circle cx="9" cy="7" r="4" stroke-width="1.5"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M23 21v-2a4 4 0 00-3-3.87"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 3.13a4 4 0 010 7.75"/>
                                </svg>
                            </div>
                            <h2 class="text-3xl font-bold text-white">Nenhuma pessoa cadastrada</h2>
                            <p class="text-zinc-500 mt-4 max-w-md mx-auto leading-relaxed">
                                Crie grupos, adicione pessoas e comece a construir seu mapa social dentro do Mind.
                            </p>
                            <button
                                type="button" x-on:click="$dispatch('open-modal', 'person-modal')"
                                class="mt-8 px-6 py-3 bg-white text-black font-semibold text-sm hover:scale-[1.02] transition">
                                Adicionar primeira pessoa
                            </button>
                        </div>
                    @else
                        <div class="relative h-[420px] flex items-center justify-center">
                            <div class="w-full h-full">
                                <x-fisheye :people="$people"/>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Painel lateral --}}
                <div>
                    <div class="h-[420px] border border-white/10 bg-white/[0.015] p-6 overflow-y-auto">

                        <!-- Ninguém selecionado -->
                        <template x-if="!selectedPerson">
                            <div class="h-full flex flex-col items-center justify-center text-center opacity-80 mt-10">
                                <div class="w-20 h-20 rounded-full bg-white/[0.02] border border-white/5 flex items-center justify-center mb-6 shadow-inner">
                                    <svg class="w-8 h-8 text-zinc-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </div>
                                <h2 class="text-xl font-bold text-white tracking-wide">Esfera Social</h2>
                                <p class="mt-3 text-sm text-zinc-500 max-w-[220px] leading-relaxed">
                                    Gire a esfera e clique em um nó para visualizar ou editar os detalhes da pessoa.
                                </p>

                                <div class="mt-8 grid grid-cols-2 gap-3 w-full px-4">
                                    <div class="bg-white/[0.02] border border-white/5 rounded-lg p-3 shadow-inner">
                                        <div class="text-2xl font-black text-white">{{ $people->count() }}</div>
                                        <div class="text-[9px] uppercase tracking-widest text-zinc-500 mt-1">Pessoas na Rede</div>
                                    </div>
                                    <div class="bg-white/[0.02] border border-white/5 rounded-lg p-3 shadow-inner">
                                        <div class="text-2xl font-black text-white">{{ $groups->count() }}</div>
                                        <div class="text-[9px] uppercase tracking-widest text-zinc-500 mt-1">Grupos Formados</div>
                                    </div>
                                    @php
                                        $largestGroup = $groups->sortByDesc('people_count')->first();
                                    @endphp
                                    @if($largestGroup && $largestGroup->people_count > 0)
                                        <div class="col-span-2 bg-white/[0.02] border border-white/5 rounded-lg p-3 text-left flex items-center justify-between shadow-inner">
                                            <div>
                                                <div class="text-[9px] uppercase tracking-widest text-zinc-500">Maior Grupo</div>
                                                <div class="text-sm font-bold text-white mt-0.5 truncate max-w-[140px]">{{ $largestGroup->name }}</div>
                                            </div>
                                            <div class="text-lg font-black text-white">{{ $largestGroup->people_count }}</div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </template>


                        <x-person-edit-modal :groups="$groups" />

                    </div>
                </div>
            
        </div>

        <x-groups-modal :groups="$groups" />
        <x-person-modal :groups="$groups" />

    </div>

</x-app-layout>