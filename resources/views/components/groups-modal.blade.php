@props(['groups'])

<x-modal name="groups-modal" max-width="3xl" :show="$errors->group->any()" focusable>
    <div class="flex flex-col max-h-[70vh] w-full overflow-hidden">
        {{-- Header --}}
        <div class="px-6 py-5 border-b border-white/10 flex items-center justify-between shrink-0">
            <div>
                <span class="text-[10px] uppercase tracking-[0.3em] text-zinc-500 font-semibold">MindSocial</span>
                <h2 class="text-lg font-black text-white mt-0.5">Gerenciar Grupos</h2>
            </div>

            <button
                type="button"
                x-on:click="$dispatch('close-modal', 'groups-modal')"
                class="text-zinc-500 hover:text-white transition p-1.5 rounded-lg hover:bg-white/5"
                aria-label="Fechar"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="flex flex-col lg:flex-row flex-1 min-h-0">
            {{-- Formulário --}}
            <div class="lg:w-[38%] border-b lg:border-b-0 lg:border-r border-white/10 p-6 overflow-y-auto">
                <p class="text-[10px] uppercase tracking-widest text-zinc-600 font-semibold mb-3">Novo grupo</p>

                <form method="POST" action="{{ route('mind-groups.store') }}" class="space-y-3">
                    @csrf

                    <div>
                        <input
                            type="text"
                            name="name"
                            required
                            value="{{ $errors->group->any() ? old('name') : '' }}"
                            placeholder="Nome do grupo"
                            class="w-full bg-white/[0.03] border @error('name', 'group') border-red-500/50 @else border-white/10 @enderror text-white text-sm px-3 py-2.5 focus:outline-none focus:border-white/30 placeholder:text-zinc-600 transition rounded-lg"
                        >
                        @error('name', 'group')
                            <p class="text-xs text-red-400 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <input
                        type="text"
                        name="description"
                        value="{{ $errors->group->any() ? old('description') : '' }}"
                        placeholder="Descrição (opcional)"
                        class="w-full bg-white/[0.03] border border-white/10 text-white text-sm px-3 py-2.5 focus:outline-none focus:border-white/30 placeholder:text-zinc-600 transition rounded-lg"
                    >

                    <x-icon-picker name="icon" :selected="$errors->group->any() ? old('icon', 'users') : 'users'" />

                    <button
                        type="submit"
                        class="w-full py-2.5 bg-white text-black text-sm font-bold hover:scale-[1.02] transition rounded-lg"
                    >
                        Criar Grupo
                    </button>
                </form>
            </div>

            {{-- Lista --}}
            <div class="lg:w-[62%] flex flex-col flex-1 min-h-0">
                <div class="px-6 pt-5 pb-3 shrink-0 border-b border-white/10">
                    @if ($groups->isNotEmpty())
                        <p class="text-[10px] uppercase tracking-widest text-zinc-600 font-semibold">
                            {{ $groups->count() }} {{ $groups->count() === 1 ? 'grupo existente' : 'grupos existentes' }}
                        </p>
                    @else
                        <p class="text-[10px] uppercase tracking-widest text-zinc-600 font-semibold">
                            Nenhum grupo criado ainda
                        </p>
                    @endif
                </div>

                <div class="flex-1 min-h-0 overflow-y-auto px-6 py-4">
                    <div class="space-y-2">
                        @forelse ($groups as $group)
                            @php $isFailedEdit = $errors->group->any() && old('_group_id') == $group->id; @endphp
                            <div x-data="{ editing: {{ $isFailedEdit ? 'true' : 'false' }} }" class="border border-white/10 bg-white/[0.02] rounded-xl overflow-hidden">

                                {{-- View mode --}}
                                <div x-show="!editing" class="px-4 py-3 flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <div class="w-8 h-8 rounded-full border border-white/10 bg-white/[0.04] flex items-center justify-center shrink-0">
                                            <i data-lucide="{{ $group->icon }}" class="w-3.5 h-3.5 text-zinc-300"></i>
                                        </div>

                                        <div class="min-w-0">
                                            <p class="text-sm font-semibold text-white truncate">{{ $group->name }}</p>
                                            @if ($group->description)
                                                <p class="text-xs text-zinc-500 truncate leading-relaxed">{{ $group->description }}</p>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2 shrink-0">
                                        <span class="text-[11px] text-zinc-600 tabular-nums">
                                            {{ $group->people_count }} {{ $group->people_count === 1 ? 'pessoa' : 'pessoas' }}
                                        </span>

                                        <button
                                            type="button"
                                            @click="editing = true"
                                            class="px-2.5 py-1 text-xs text-zinc-400 hover:text-white bg-white/[0.04] hover:bg-white/[0.08] rounded-lg transition"
                                        >
                                            Editar
                                        </button>

                                        <form
                                            method="POST"
                                            action="{{ route('mind-groups.destroy', $group->id) }}"
                                            onsubmit="return confirm('Excluir este grupo? Todas as associações serão removidas.')"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="px-2.5 py-1 text-xs text-red-400/70 hover:text-red-400 bg-red-400/[0.05] hover:bg-red-400/[0.10] rounded-lg transition"
                                            >
                                                Excluir
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                {{-- Edit mode --}}
                                <form
                                    x-show="editing"
                                    x-transition
                                    method="POST"
                                    action="{{ route('mind-groups.update', $group->id) }}"
                                    class="px-4 py-4 space-y-3 border-t border-white/5"
                                >
                                    @csrf
                                    @method('PUT')

                                    <input type="hidden" name="_group_id" value="{{ $group->id }}">

                                    <input
                                        type="text"
                                        name="name"
                                        value="{{ $isFailedEdit ? old('name') : $group->name }}"
                                        required
                                        class="w-full bg-black/40 border @error('name', 'group') border-red-500/50 @else border-white/10 @enderror text-white text-sm px-3 py-2.5 focus:outline-none focus:border-white/30 rounded-lg"
                                    >
                                    @if ($isFailedEdit)
                                        @error('name', 'group')
                                            <p class="text-xs text-red-400">{{ $message }}</p>
                                        @enderror
                                    @endif

                                    <input
                                        type="text"
                                        name="description"
                                        value="{{ $group->description }}"
                                        placeholder="Descrição"
                                        class="w-full bg-black/40 border border-white/10 text-white text-sm px-3 py-2.5 focus:outline-none focus:border-white/30 rounded-lg placeholder:text-zinc-700"
                                    >

                                    <x-icon-picker name="icon" :selected="$group->icon" />

                                    <div class="flex gap-2 pt-1">
                                        <button
                                            type="submit"
                                            class="px-4 py-2 bg-white text-black text-xs font-bold hover:scale-[1.02] transition rounded-lg"
                                        >
                                            Salvar
                                        </button>

                                        <button
                                            type="button"
                                            @click="editing = false"
                                            class="px-4 py-2 border border-white/10 text-white text-xs hover:bg-white/5 transition rounded-lg"
                                        >
                                            Cancelar
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @empty
                            <div class="text-center py-10 border border-dashed border-white/10 rounded-xl">
                                <p class="text-zinc-600 text-sm">Nenhum grupo criado ainda.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-modal>