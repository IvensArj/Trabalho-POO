<x-app-layout>

    <div x-data="{ groupsModal: false, personModal: false }">

        <div class="max-w-7xl mx-auto px-6 py-8">

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
                        @click="groupsModal = true"
                        class="px-4 py-2 border border-white/10 bg-white/[0.03] text-white text-sm hover:bg-white/[0.06] transition">
                        Grupos
                    </button>
                    <button
                        @click="personModal = true"
                        class="px-5 py-3 bg-white text-black font-semibold text-sm hover:scale-[1.02] transition">
                        Nova Pessoa
                    </button>
                </div>
            </div>

            {{-- Stats --}}
            <div class="grid md:grid-cols-3 gap-4 mb-8">
                <div class="border border-white/10 bg-white/[0.02] p-6">
                    <p class="text-xs uppercase tracking-widest text-zinc-600">Pessoas</p>
                    <p class="text-4xl font-black text-white mt-3">{{ $people->count() }}</p>
                </div>
                <div class="border border-white/10 bg-white/[0.02] p-6">
                    <p class="text-xs uppercase tracking-widest text-zinc-600">Grupos</p>
                    <p class="text-4xl font-black text-white mt-3">{{ $groups->count() }}</p>
                </div>
                <div class="border border-white/10 bg-white/[0.02] p-6">
                    <p class="text-xs uppercase tracking-widest text-zinc-600">Conexões</p>
                    <p class="text-4xl font-black text-white mt-3">—</p>
                </div>
            </div>

            {{-- Conteúdo principal --}}
            <div class="relative overflow-hidden border border-white/10 bg-white/[0.02] p-10">

                {{-- Halftone --}}
                <div
                    class="absolute inset-0 opacity-[0.03] pointer-events-none"
                    style="background-image:radial-gradient(rgba(255,255,255,.6) 1px,transparent 1px);background-size:14px 14px;">
                </div>

                @if ($people->isEmpty())

                    <div class="relative z-10 text-center py-20">
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
                            @click="personModal = true"
                            class="mt-8 px-6 py-3 bg-white text-black font-semibold text-sm hover:scale-[1.02] transition">
                            Adicionar primeira pessoa
                        </button>
                    </div>

                @else

                    <div id="mind-social-container" class="relative z-10 flex flex-wrap justify-center gap-4" x-data="{ mouseX: 0, mouseY: 0, updateMousePosition(event) { this.mouseX = event.clientX; this.mouseY = event.clientY; } }" @mousemove="updateMousePosition($event)">

                        @foreach ($people as $person)
                            @php
                                $parts    = explode(' ', trim($person->name));
                                $initials = strtoupper(substr($parts[0], 0, 1));
                                if (count($parts) > 1) { $initials .= strtoupper(substr(end($parts), 0, 1)); }
                            @endphp

                            <div x-data="{ showDetails: false, getScale() { const rect = $el.getBoundingClientRect(); const centerX = rect.left + rect.width / 2; const centerY = rect.top + rect.height / 2; const distance = Math.sqrt(Math.pow(this.mouseX - centerX, 2) + Math.pow(this.mouseY - centerY, 2)); const maxDistance = 300; // Adjust this value for the radius of the effect const scale = 1 + (1 - Math.min(distance, maxDistance) / maxDistance) * 0.2; // Max scale 1.2 return `scale(${scale})`; } }" @mouseenter="showDetails = true" @mouseleave="showDetails = false" :style="getScale()" class="relative border border-white/10 bg-white/[0.02] p-6 transition-all duration-300 hover:bg-white/[0.05] origin-center">

                                <div class="flex items-start justify-between mb-5">
                                    @if ($person->photo)
                                        <img src="{{ asset('storage/' . $person->photo) }}" alt="{{ $person->name }}"
                                            class="w-12 h-12 object-cover border border-white/10">
                                    @else
                                        <div class="w-12 h-12 border border-white/10 bg-white/[0.06] flex items-center justify-center text-sm font-black text-white shrink-0">
                                            {{ $initials }}
                                        </div>
                                    @endif

                                    @if ($person->group)
                                        <span class="text-xs text-zinc-500 border border-white/10 bg-white/[0.02] px-2 py-1">
                                            {{ $person->group->name }}
                                        </span>
                                    @endif
                                </div>

                                <h3 class="text-base font-bold text-white leading-tight">{{ $person->name }}</h3>

                                <div x-show="showDetails" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-90" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-90" class="absolute inset-0 bg-black/80 backdrop-blur-sm p-6 flex flex-col justify-center items-center text-center">
                                    <p class="text-sm text-white font-bold">{{ $person->name }}</p>
                                    @if ($person->nickname)
                                        <p class="text-xs text-zinc-400">"{{ $person->nickname }}"</p>
                                    @endif
                                    @if ($person->group)
                                        <p class="text-xs text-zinc-500 mt-1">Grupo: {{ $person->group->name }}</p>
                                    @endif
                                    @if ($person->notes)
                                        <p class="text-xs text-zinc-500 mt-2 line-clamp-3">{{ $person->notes }}</p>
                                    @endif
                                    <a href="{{ route('mind-people.show', $person) }}" class="mt-4 text-xs text-white hover:underline">Ver detalhes →</a>
                                </div>

                                

                            </div>
                        @endforeach

                    </div>

                @endif

            </div>

        </div>

        {{-- MODAL: Grupos --}}
        <div x-show="groupsModal" x-transition.opacity class="fixed inset-0 z-50" style="display:none">
            <div @click="groupsModal = false" class="absolute inset-0 bg-black/70 backdrop-blur-sm"></div>
            <div class="relative h-full flex items-center justify-center p-6">
                <div @click.stop x-transition class="w-full max-w-lg border border-white/10 bg-zinc-950 overflow-hidden">

                    <div class="px-6 py-5 border-b border-white/10 flex items-center justify-between">
                        <div>
                            <span class="text-xs uppercase tracking-[0.3em] text-zinc-600">MindSocial</span>
                            <h2 class="text-2xl font-black text-white mt-1">Grupos</h2>
                        </div>
                        <button @click="groupsModal = false" class="text-zinc-600 hover:text-white transition text-lg leading-none">✕</button>
                    </div>

                    <div class="p-6 max-h-72 overflow-y-auto space-y-2">
                        @forelse ($groups as $group)
                            <div class="flex items-center justify-between px-4 py-3 border border-white/10 bg-white/[0.02]">
                                <div>
                                    <p class="text-sm text-white font-medium">{{ $group->name }}</p>
                                    @if ($group->description)
                                        <p class="text-xs text-zinc-600 mt-0.5">{{ $group->description }}</p>
                                    @endif
                                </div>
                                <div class="flex items-center gap-4 shrink-0 ml-4">
                                    <span class="text-xs text-zinc-600">
                                        {{ $group->people->count() }} {{ $group->people->count() === 1 ? 'pessoa' : 'pessoas' }}
                                    </span>
                                    <form method="POST" action="{{ route('mind-groups.destroy', $group) }}"
                                        onsubmit="return confirm('Excluir o grupo {{ addslashes($group->name) }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-zinc-700 hover:text-red-400 transition text-xs">✕</button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <p class="text-zinc-700 text-sm text-center py-8">Nenhum grupo criado ainda.</p>
                        @endforelse
                    </div>

                    <div class="px-6 pb-6" x-data="{ creating: false }">
                        <button @click="creating = !creating"
                            class="w-full py-2.5 border border-dashed border-white/10 hover:border-white/30 text-xs text-zinc-600 hover:text-white transition">
                            + Novo grupo
                        </button>
                        <div x-show="creating" x-transition class="mt-4">
                            <form method="POST" action="{{ route('mind-groups.store') }}" class="space-y-3">
                                @csrf
                                <input type="text" name="name" required placeholder="Nome do grupo"
                                    class="w-full bg-white/[0.03] border border-white/10 text-white text-sm px-4 py-3 focus:outline-none focus:border-white/30 placeholder:text-zinc-700">
                                <input type="text" name="description" placeholder="Descrição (opcional)"
                                    class="w-full bg-white/[0.03] border border-white/10 text-white text-sm px-4 py-3 focus:outline-none focus:border-white/30 placeholder:text-zinc-700">
                                <button type="submit"
                                    class="w-full px-5 py-3 bg-white text-black text-sm font-bold hover:opacity-90 transition">
                                    Criar grupo
                                </button>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- MODAL: Nova Pessoa --}}
        <div x-show="personModal" x-transition.opacity class="fixed inset-0 z-50" style="display:none">
            <div @click="personModal = false" class="absolute inset-0 bg-black/70 backdrop-blur-sm"></div>
            <div class="relative h-full flex items-center justify-center p-6">
                <div @click.stop x-transition class="w-full max-w-lg border border-white/10 bg-zinc-950 overflow-hidden">

                    <div class="px-6 py-5 border-b border-white/10 flex items-center justify-between">
                        <div>
                            <span class="text-xs uppercase tracking-[0.3em] text-zinc-600">MindSocial</span>
                            <h2 class="text-2xl font-black text-white mt-1">Nova Pessoa</h2>
                        </div>
                        <button @click="personModal = false" class="text-zinc-600 hover:text-white transition text-lg leading-none">✕</button>
                    </div>

                    <form method="POST" action="{{ route('mind-people.store') }}" class="p-6 space-y-4">
                        @csrf

                        <div>
                            <label class="text-xs uppercase tracking-widest text-zinc-600 block mb-2">Nome *</label>
                            <input type="text" name="name" required autofocus placeholder="Nome completo"
                                class="w-full bg-white/[0.03] border border-white/10 text-white text-sm px-4 py-3 focus:outline-none focus:border-white/30 placeholder:text-zinc-700">
                        </div>

                        <div>
                            <label class="text-xs uppercase tracking-widest text-zinc-600 block mb-2">Apelido</label>
                            <input type="text" name="nickname" placeholder="Como você chama essa pessoa"
                                class="w-full bg-white/[0.03] border border-white/10 text-white text-sm px-4 py-3 focus:outline-none focus:border-white/30 placeholder:text-zinc-700">
                        </div>

                        <div>
                            <label class="text-xs uppercase tracking-widest text-zinc-600 block mb-2">Grupo</label>
                            <div x-data="{ showGroupForm: false }">
                                <select name="mind_group_id"
                                    class="w-full bg-zinc-950 border border-white/10 text-white text-sm px-4 py-3 focus:outline-none focus:border-white/30 appearance-none">
                                    <option value="">Sem grupo</option>
                                    @foreach ($groups as $group)
                                        <option value="{{ $group->id }}">{{ $group->name }}</option>
                                    @endforeach
                                </select>
                                <button type="button" @click="showGroupForm = !showGroupForm" class="mt-2 w-full py-2.5 border border-dashed border-white/10 hover:border-white/30 text-xs text-zinc-600 hover:text-white transition">
                                    + Novo grupo
                                </button>
                                <div x-show="showGroupForm" x-transition class="mt-4">
                                    <form method="POST" action="{{ route('mind-groups.store') }}" class="space-y-3">
                                        @csrf
                                        <input type="text" name="name" required placeholder="Nome do grupo"
                                            class="w-full bg-white/[0.03] border border-white/10 text-white text-sm px-4 py-3 focus:outline-none focus:border-white/30 placeholder:text-zinc-700">
                                        <input type="text" name="description" placeholder="Descrição (opcional)"
                                            class="w-full bg-white/[0.03] border border-white/10 text-white text-sm px-4 py-3 focus:outline-none focus:border-white/30 placeholder:text-zinc-700">
                                        <button type="submit"
                                            class="w-full px-5 py-3 bg-white text-black text-sm font-bold hover:opacity-90 transition">
                                            Criar grupo
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="text-xs uppercase tracking-widest text-zinc-600 block mb-2">Notas</label>
                            <textarea name="notes" rows="3" placeholder="Algo importante sobre essa pessoa..."
                                class="w-full bg-white/[0.03] border border-white/10 text-white text-sm px-4 py-3 focus:outline-none focus:border-white/30 placeholder:text-zinc-700 resize-none"></textarea>
                        </div>

                        <div class="flex gap-3 pt-1">
                            <button type="submit"
                                class="flex-1 px-6 py-3 bg-white text-black font-bold text-sm hover:scale-[1.02] transition">
                                Adicionar
                            </button>
                            <button type="button" @click="personModal = false"
                                class="flex-1 px-6 py-3 border border-white/10 text-white text-sm hover:bg-white/[0.03] transition">
                                Cancelar
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>

    </div>

</x-app-layout>