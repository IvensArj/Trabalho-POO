<x-app-layout>

    <div
        x-data="{
            groupsModal: false,
            personModal: {{ $errors->any() && (old('_token') && !old('_method')) ? 'true' : 'false' }},
            photoPreview: null,
            compressing: false,
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
    >

        <div class="max-w-7xl mx-auto px-6 py-8">

            {{-- Flash Messages --}}
            @if (session('success'))
                <div class="mb-6 border border-emerald-500/30 bg-emerald-500/10 text-emerald-300 px-4 py-3 rounded-lg flex items-center justify-between">
                    <span class="text-sm">{{ session('success') }}</span>
                </div>
            @endif

            @if ($errors->any() && !request()->routeIs('mind-groups.*'))
                <div class="mb-6 border border-red-500/30 bg-red-500/10 text-red-300 px-4 py-3 rounded-lg">
                    <p class="text-sm font-semibold mb-1">Não foi possível salvar. Verifique os erros abaixo:</p>
                    <ul class="text-xs space-y-0.5 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

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

                    <div id="mind-social-container" class="relative z-10 flex flex-wrap justify-center gap-4"
                        x-data="{ mouseX: 0, mouseY: 0, updateMousePosition(event) { this.mouseX = event.clientX; this.mouseY = event.clientY; } }"
                        @mousemove="updateMousePosition($event)">

                        @foreach ($people as $person)
                            @php
                                $parts    = explode(' ', trim($person->name));
                                $initials = strtoupper(substr($parts[0], 0, 1));
                                if (count($parts) > 1) { $initials .= strtoupper(substr(end($parts), 0, 1)); }
                            @endphp

                            <div x-data="{
                                showDetails: false,
                                getScale() {
                                    const rect = $el.getBoundingClientRect();
                                    const centerX = rect.left + rect.width / 2;
                                    const centerY = rect.top + rect.height / 2;
                                    const distance = Math.sqrt(Math.pow($parent.mouseX - centerX, 2) + Math.pow($parent.mouseY - centerY, 2));
                                    const maxDistance = 300;
                                    const scale = 1 + (1 - Math.min(distance, maxDistance) / maxDistance) * 0.2;
                                    return `scale(${scale})`; }}"
                                @mouseenter="showDetails = true"
                                @mouseleave="showDetails = false"
                                :style="getScale()"
                                class="relative border border-white/10 bg-white/[0.02] p-6 transition-all duration-300 hover:bg-white/[0.05] origin-center">

                                <div class="flex items-start justify-between mb-5">
                                    @if ($person->photo)
                                        <img src="{{ asset('storage/' . $person->photo) }}" alt="{{ $person->name }}"
                                            class="w-12 h-12 object-cover border border-white/10">
                                    @else
                                        <div class="w-12 h-12 border border-white/10 bg-white/[0.06] flex items-center justify-center text-sm font-black text-white shrink-0">
                                            {{ $initials }}
                                        </div>
                                    @endif

                                    @if ($person->groups)
                                        <span class="text-xs text-zinc-500 border border-white/10 bg-white/[0.02] px-2 py-1">
                                            {{ $person->groups->pluck('name')->join(', ') }}
                                        </span>
                                    @endif
                                </div>

                                <h3 class="text-base font-bold text-white leading-tight">{{ $person->name }}</h3>

                                <div x-show="showDetails"
                                    x-transition:enter="transition ease-out duration-300"
                                    x-transition:enter-start="opacity-0 transform scale-90"
                                    x-transition:enter-end="opacity-100 transform scale-100"
                                    x-transition:leave="transition ease-in duration-200"
                                    x-transition:leave-start="opacity-100 transform scale-100"
                                    x-transition:leave-end="opacity-0 transform scale-90"
                                    class="absolute inset-0 bg-black/80 backdrop-blur-sm p-6 flex flex-col justify-center items-center text-center">
                                    <p class="text-sm text-white font-bold">{{ $person->name }}</p>
                                    @if ($person->nickname)
                                        <p class="text-xs text-zinc-400">"{{ $person->nickname }}"</p>
                                    @endif
                                    @if ($person->groups->count())
                                        <p class="text-xs text-zinc-500 mt-1">Grupos: {{ $person->groups->pluck('name')->join(', ') }}</p>
                                    @endif
                                    @if ($person->notes)
                                        <p class="text-xs text-zinc-500 mt-2 line-clamp-3">{{ $person->notes }}</p>
                                    @endif
                                </div>

                            </div>
                        @endforeach

                    </div>

                @endif

            </div>

        </div>


        {{-- MODAL: Grupos --}}
        <div
            x-cloak
            x-show="groupsModal"
            x-init="$nextTick(() => lucide.createIcons())"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
            style="display: none;"
            @keydown.escape.window="groupsModal = false"
        >
            {{-- Backdrop --}}
            <div
                x-show="groupsModal"
                x-transition.opacity
                class="absolute inset-0 bg-black/80 backdrop-blur-sm"
                @click="groupsModal = false"
            ></div>

            {{-- Panel --}}
            <div
                @click.stop
                x-show="groupsModal"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative w-full max-w-md bg-zinc-950 border border-white/10 shadow-2xl rounded-2xl flex flex-col max-h-[90vh] overflow-hidden"
            >
                {{-- Header --}}
                <div class="px-6 py-5 border-b border-white/10 flex items-center justify-between shrink-0">
                    <div>
                        <span class="text-[10px] uppercase tracking-[0.3em] text-zinc-500 font-semibold">MindSocial</span>
                        <h2 class="text-lg font-black text-white mt-0.5">Gerenciar Grupos</h2>
                    </div>
                    <button @click="groupsModal = false"
                        class="text-zinc-500 hover:text-white transition p-1.5 rounded-lg hover:bg-white/5"
                        aria-label="Fechar">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Formulário de criar --}}
                <div class="px-6 pt-5 pb-5 border-b border-white/10 shrink-0">
                    <p class="text-[10px] uppercase tracking-widest text-zinc-600 font-semibold mb-3">Novo grupo</p>
                    <form method="POST" action="{{ route('mind-groups.store') }}" class="space-y-3">
                        @csrf

                        <input type="text" name="name" required placeholder="Nome do grupo"
                            class="w-full bg-white/[0.03] border border-white/10 text-white text-sm px-3 py-2.5 focus:outline-none focus:border-white/30 placeholder:text-zinc-600 transition rounded-lg">

                        <input type="text" name="description" placeholder="Descrição (opcional)"
                            class="w-full bg-white/[0.03] border border-white/10 text-white text-sm px-3 py-2.5 focus:outline-none focus:border-white/30 placeholder:text-zinc-600 transition rounded-lg">

                        {{-- Icon picker --}}
                        <div x-data="{ 
                            selectedIcon: 'users',
                            fixIcons() {
                                // Inicializa os ícones do Lucide
                                lucide.createIcons();
                                // Aguarda o DOM atualizar e remove os atributos de tamanho fixo
                                this.$nextTick(() => {
                                    this.$el.querySelectorAll('svg').forEach(svg => {
                                        svg.removeAttribute('width');
                                        svg.removeAttribute('height');
                                        // Garante que o SVG preencha o <i>
                                        svg.style.width = '100%';
                                        svg.style.height = '100%';
                                    });
                                });
                            }
                        }" 
                        x-init="fixIcons()" 
                        @click="fixIcons()">   <!-- Reaplica após selecionar outro ícone -->
                            <input type="hidden" name="icon" x-model="selectedIcon">
                            <label class="text-[10px] uppercase tracking-widest text-zinc-500 font-semibold block mb-2">Ícone</label>

                            <div class="flex w-full items-center gap-1.5">
                                @foreach(['users','heart','gamepad-2','book-open','briefcase','music','camera','home','star'] as $icon)
                                    <button
                                        type="button"
                                        @click="selectedIcon = '{{ $icon }}'"
                                        :class="selectedIcon === '{{ $icon }}'
                                            ? 'border-white bg-white/15 text-white'
                                            : 'border-white/10 text-zinc-600 hover:border-white/30 hover:text-zinc-300'"
                                        class="w-9 h-9 shrink-0 rounded-full border flex items-center justify-center transition box-border overflow-hidden p-2"
                                        title="{{ $icon }}"
                                    >
                                        <i data-lucide="{{ $icon }}" class="w-3 h-3 pointer-events-none block"></i>
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <button type="submit"
                            class="w-full py-2.5 bg-white text-black text-sm font-bold hover:scale-[1.02] transition rounded-lg">
                            Criar Grupo
                        </button>
                    </form>
                </div>

                {{-- Lista de grupos existentes --}}
                <div class="flex-1 min-h-0 overflow-y-auto px-6 py-4">
                    @if ($groups->isNotEmpty())
                        <p class="text-[10px] uppercase tracking-widest text-zinc-600 font-semibold mb-3">
                            {{ $groups->count() }} {{ $groups->count() === 1 ? 'grupo existente' : 'grupos existentes' }}
                        </p>
                    @endif

                    <div class="space-y-2">
                        @forelse ($groups as $group)
                            <div x-data="{ editing: false }" class="border border-white/10 bg-white/[0.02] rounded-xl overflow-hidden">

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
                                        <button @click="editing = true" type="button"
                                            class="px-2.5 py-1 text-xs text-zinc-400 hover:text-white bg-white/[0.04] hover:bg-white/[0.08] rounded-lg transition">
                                            Editar
                                        </button>
                                        <form method="POST" action="{{ route('mind-groups.destroy', $group->id) }}"
                                            onsubmit="return confirm('Excluir este grupo? Todas as associações serão removidas.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="px-2.5 py-1 text-xs text-red-400/70 hover:text-red-400 bg-red-400/[0.05] hover:bg-red-400/[0.10] rounded-lg transition">
                                                Excluir
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                {{-- Edit mode --}}
                                <form
                                    x-show="editing"
                                    x-transition
                                    x-data="{ selectedIcon: '{{ $group->icon }}' }"
                                    method="POST"
                                    action="{{ route('mind-groups.update', $group->id) }}"
                                    class="px-4 py-4 space-y-3 border-t border-white/5"
                                >
                                    @csrf
                                    @method('PUT')

                                    <input type="text" name="name" value="{{ $group->name }}" required
                                        class="w-full bg-black/40 border border-white/10 text-black text-sm px-3 py-2.5 focus:outline-none focus:border-white/30 rounded-lg">

                                    <input type="text" name="description" value="{{ $group->description }}" placeholder="Descrição"
                                        class="w-full bg-black/40 border border-white/10 text-black text-sm px-3 py-2.5 focus:outline-none focus:border-white/30 rounded-lg placeholder:text-zinc-700">

                                    <input type="hidden" name="icon" x-model="selectedIcon">

                                    {{-- Icon picker (edit) --}}
                                    <div x-data="{ 
                                        selectedIcon: 'users',
                                        fixIcons() {
                                            // Inicializa os ícones do Lucide
                                            lucide.createIcons();
                                            // Aguarda o DOM atualizar e remove os atributos de tamanho fixo
                                            this.$nextTick(() => {
                                                this.$el.querySelectorAll('svg').forEach(svg => {
                                                    svg.removeAttribute('width');
                                                    svg.removeAttribute('height');
                                                    // Garante que o SVG preencha o <i>
                                                    svg.style.width = '100%';
                                                    svg.style.height = '100%';
                                                });
                                            });
                                        }
                                    }" 
                                    x-init="fixIcons()" 
                                    @click="fixIcons()">   <!-- Reaplica após selecionar outro ícone -->
                                        <input type="hidden" name="icon" x-model="selectedIcon">
                                        <label class="text-[10px] uppercase tracking-widest text-zinc-500 font-semibold block mb-2">Ícone</label>

                                        <div class="flex w-full items-center gap-1.5">
                                            @foreach(['users','heart','gamepad-2','book-open','briefcase','music','camera','home','star'] as $icon)
                                                <button
                                                    type="button"
                                                    @click="selectedIcon = '{{ $icon }}'"
                                                    :class="selectedIcon === '{{ $icon }}'
                                                        ? 'border-white bg-white/15 text-white'
                                                        : 'border-white/10 text-zinc-600 hover:border-white/30 hover:text-zinc-300'"
                                                    class="w-7 h-7 shrink-0 rounded-full border flex items-center justify-center transition box-border overflow-hidden p-1"
                                                    title="{{ $icon }}"
                                                >
                                                    <i data-lucide="{{ $icon }}" class="w-3 h-3 pointer-events-none block"></i>
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="flex gap-2 pt-1">
                                        <button type="submit"
                                            class="px-4 py-2 bg-white text-black text-xs font-bold hover:scale-[1.02] transition rounded-lg">
                                            Salvar
                                        </button>
                                        <button type="button" @click="editing = false"
                                            class="px-4 py-2 border border-white/10 text-white text-xs hover:bg-white/5 transition rounded-lg">
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


        {{-- MODAL: Nova Pessoa --}}
        <div
            x-cloak
            x-show="personModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
            style="display:none"
            @keydown.escape.window="personModal = false; photoPreview = null"
        >
            {{-- Backdrop --}}
            <div x-show="personModal" x-transition.opacity
                class="absolute inset-0 bg-black/80 backdrop-blur-sm"
                @click="personModal = false; photoPreview = null"></div>

            {{-- Panel --}}
            <div
                @click.stop
                x-show="personModal"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative w-full max-w-2xl bg-zinc-950 border border-white/10 shadow-2xl rounded-2xl flex flex-col max-h-[92vh] overflow-hidden"
            >
                {{-- Header --}}
                <div class="px-6 py-5 border-b border-white/10 flex items-center justify-between shrink-0">
                    <div>
                        <span class="text-[10px] uppercase tracking-[0.3em] text-zinc-500 font-semibold">MindSocial</span>
                        <h2 class="text-lg font-black text-white mt-0.5">Nova Pessoa</h2>
                    </div>
                    <button @click="personModal = false; photoPreview = null"
                        class="text-zinc-500 hover:text-white transition p-1.5 rounded-lg hover:bg-white/5"
                        aria-label="Fechar">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="overflow-y-auto flex-1">
                    <form method="POST" action="{{ route('mind-people.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="p-6 space-y-6">

                            {{-- ── BLOCO TOPO: Foto + Campos básicos ── --}}
                            <div class="flex gap-5 items-start">

                                {{-- Foto --}}
                                <div class="shrink-0 flex flex-col items-center gap-2.5 sm:pt-5">
                                    <div class="relative group cursor-pointer w-24 h-24">
                                        <div class="w-24 h-24 rounded-full border-2 border-white/10 bg-white/[0.03] flex items-center justify-center overflow-hidden transition-all duration-300 group-hover:border-white/30">
                                            <template x-if="photoPreview">
                                                <img :src="photoPreview" class="w-full h-full object-cover object-center" alt="Prévia">
                                            </template>
                                            <template x-if="!photoPreview && !compressing">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-zinc-600 group-hover:text-zinc-400 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                            </template>
                                            <template x-if="compressing">
                                                <div class="absolute inset-0 bg-black/70 rounded-full flex items-center justify-center">
                                                    <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                                    </svg>
                                                </div>
                                            </template>
                                        </div>
                                        <input type="file" name="photo" accept="image/jpeg,image/png,image/webp"
                                            @change="handlePhotoChange($event)"
                                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                                        <div class="absolute inset-0 rounded-full bg-black/50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition pointer-events-none">
                                            <span class="text-white text-[11px] font-medium">Alterar</span>
                                        </div>
                                    </div>
                                    <p class="text-[10px] text-zinc-600 text-center leading-relaxed">
                                        JPG, PNG ou WEBP<br>máx. 3 MB
                                    </p>
                                </div>

                                {{-- Nome, Apelido e Aniversário --}}
                                <div class="flex-1 min-w-0 space-y-3">

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <label class="text-[10px] uppercase tracking-widest text-zinc-500 font-semibold block mb-1.5">
                                                Nome Completo *
                                            </label>
                                            <input type="text" name="name" required value="{{ old('name') }}"
                                                placeholder="Ex: João da Silva"
                                                class="w-full bg-white/[0.03] border border-white/10 text-white text-sm px-3 py-2.5 focus:outline-none focus:border-white/30 placeholder:text-zinc-700 transition rounded-lg">
                                        </div>
                                        <div>
                                            <label class="text-[10px] uppercase tracking-widest text-zinc-500 font-semibold block mb-1.5">
                                                Apelido
                                            </label>
                                            <input type="text" name="nickname" value="{{ old('nickname') }}"
                                                placeholder="Como o chama?"
                                                class="w-full bg-white/[0.03] border border-white/10 text-white text-sm px-3 py-2.5 focus:outline-none focus:border-white/30 placeholder:text-zinc-700 transition rounded-lg">
                                        </div>
                                    </div>

                                    {{-- Aniversário --}}
                                    <div>
                                        <label class="text-[10px] uppercase tracking-widest text-zinc-500 font-semibold block mb-2">
                                            Aniversário *
                                        </label>

                                        <div x-data="{
                                                dayOpen: false, monthOpen: false, yearOpen: false,
                                                selectedDay: '{{ old('birth_day', '1') }}',
                                                selectedMonth: '{{ old('birth_month', '1') }}',
                                                selectedYear: '{{ old('birth_year', '2000') }}',
                                                months: ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'],
                                                days: Array.from({length: 31}, (_, i) => i + 1),
                                                years: Array.from({length: 100}, (_, i) => {{ date('Y') }} - i),
                                                closeAll() { this.dayOpen = false; this.monthOpen = false; this.yearOpen = false; },
                                                selectDay(d) { this.selectedDay = d; this.dayOpen = false; },
                                                selectMonth(m) { this.selectedMonth = m; this.monthOpen = false; },
                                                selectYear(y) { this.selectedYear = y; this.yearOpen = false; }
                                            }"
                                            @click.away="closeAll()"
                                            class="flex items-start gap-2 w-full max-w-[340px]">

                                            {{-- Dia --}}
                                            <div class="relative flex-1">
                                                <button type="button" @click="dayOpen = !dayOpen; monthOpen = false; yearOpen = false"
                                                    class="w-full bg-white/[0.03] border border-white/10 text-sm px-3 py-2.5 rounded-lg text-left transition flex items-center justify-between hover:border-white/30 focus:outline-none focus:border-white/30 text-white">
                                                    <span x-text="selectedDay || 'Dia'"></span>
                                                    <svg class="w-3 h-3 text-zinc-500 transition-transform" :class="dayOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                                    </svg>
                                                </button>
                                                <div x-show="dayOpen" x-transition.opacity.scale.origin.top
                                                    class="absolute z-20 mt-1 w-full bg-zinc-900/80 backdrop-blur-md border border-white/10 rounded-lg shadow-2xl max-h-48 overflow-y-auto grid grid-cols-6 gap-1 p-2"
                                                    @click.outside="dayOpen = false">
                                                    <template x-for="d in days" :key="d">
                                                        <button type="button" @click="selectDay(d)"
                                                            class="text-xs py-1.5 rounded hover:bg-white/10 transition"
                                                            :class="selectedDay == d ? 'bg-white/20 text-white font-medium' : 'text-zinc-300'"
                                                            x-text="d"></button>
                                                    </template>
                                                </div>
                                            </div>

                                            {{-- Mês --}}
                                            <div class="relative flex-1">
                                                <button type="button" @click="monthOpen = !monthOpen; dayOpen = false; yearOpen = false"
                                                    class="w-full bg-white/[0.03] border border-white/10 text-sm px-3 py-2.5 rounded-lg text-left transition flex items-center justify-between hover:border-white/30 focus:outline-none focus:border-white/30 text-white">
                                                    <span x-text="selectedMonth ? months[selectedMonth - 1] : 'Mês'"></span>
                                                    <svg class="w-3 h-3 text-zinc-500 transition-transform" :class="monthOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                                    </svg>
                                                </button>
                                                <div x-show="monthOpen" x-transition.opacity.scale.origin.top
                                                    class="absolute z-20 mt-1 w-full bg-zinc-900/80 backdrop-blur-md border border-white/10 rounded-lg shadow-2xl max-h-48 overflow-y-auto p-1.5 space-y-0.5"
                                                    @click.outside="monthOpen = false">
                                                    <template x-for="(m, index) in months" :key="index">
                                                        <button type="button" @click="selectMonth(index + 1)"
                                                            class="w-full text-left text-xs px-3 py-2 rounded hover:bg-white/10 transition"
                                                            :class="selectedMonth == index + 1 ? 'bg-white/20 text-white font-medium' : 'text-zinc-300'"
                                                            x-text="m"></button>
                                                    </template>
                                                </div>
                                            </div>

                                            {{-- Ano --}}
                                            <div class="relative flex-1">
                                                <button type="button" @click="yearOpen = !yearOpen; dayOpen = false; monthOpen = false"
                                                    class="w-full bg-white/[0.03] border border-white/10 text-sm px-3 py-2.5 rounded-lg text-left transition flex items-center justify-between hover:border-white/30 focus:outline-none focus:border-white/30 text-white">
                                                    <span x-text="selectedYear || 'Ano'"></span>
                                                    <svg class="w-3 h-3 text-zinc-500 transition-transform" :class="yearOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                                    </svg>
                                                </button>
                                                <div x-show="yearOpen" x-transition.opacity.scale.origin.top
                                                    class="absolute z-20 mt-1 w-full bg-zinc-900/80 backdrop-blur-md border border-white/10 rounded-lg shadow-2xl max-h-48 overflow-y-auto p-2 grid grid-cols-4 gap-1"
                                                    @click.outside="yearOpen = false">
                                                    <template x-for="y in years" :key="y">
                                                        <button type="button" @click="selectYear(y)"
                                                            class="text-xs py-1.5 rounded hover:bg-white/10 transition"
                                                            :class="selectedYear == y ? 'bg-white/20 text-white font-medium' : 'text-zinc-300'"
                                                            x-text="y"></button>
                                                    </template>
                                                </div>
                                            </div>

                                            <!-- Campos hidden obrigatórios -->
                                            <input type="hidden" name="birth_day" x-model="selectedDay" required>
                                            <input type="hidden" name="birth_month" x-model="selectedMonth" required>
                                            <input type="hidden" name="birth_year" x-model="selectedYear" required>
                                        </div>
                                    </div>

                                </div>
                            </div>

                            {{-- Separador --}}
                            <div class="border-t border-white/[0.06]"></div>

                            {{-- ── BLOCO BAIXO: Notas + Grupos lado a lado ── --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                                {{-- Notas --}}
                                <div class="flex flex-col">
                                    <label class="text-[10px] uppercase tracking-widest text-zinc-500 font-semibold block mb-1.5">
                                        Notas
                                    </label>
                                    <textarea name="notes" rows="5"
                                        placeholder="Onde se conheceram, profissão, interesses..."
                                        class="flex-1 w-full bg-white/[0.03] border border-white/10 text-white text-sm px-3 py-2.5 focus:outline-none focus:border-white/30 placeholder:text-zinc-700 transition resize-none rounded-lg">{{ old('notes') }}</textarea>
                                </div>

                                {{-- Grupos --}}
                                <div class="flex flex-col">
                                    <label class="text-[10px] uppercase tracking-widest text-zinc-500 font-semibold block mb-1.5">
                                        Grupos
                                    </label>
                                    <div class="flex-1 border border-white/10 bg-white/[0.02] rounded-lg overflow-y-auto" style="max-height: 9.5rem;">
                                        @forelse ($groups as $group)
                                            <label class="flex items-center gap-2.5 text-sm text-zinc-400 cursor-pointer hover:text-white transition px-3 py-2 hover:bg-white/[0.03] first:pt-3 last:pb-3">
                                                <input type="checkbox" name="groups[]" value="{{ $group->id }}"
                                                    class="w-3.5 h-3.5 rounded border-white/20 bg-black/50 text-white focus:ring-0 focus:ring-offset-0 shrink-0">
                                                <div class="flex items-center gap-1.5 min-w-0">
                                                    <i data-lucide="{{ $group->icon }}" class="w-3.5 h-3.5 text-zinc-600 shrink-0"></i>
                                                    <span class="truncate">{{ $group->name }}</span>
                                                </div>
                                            </label>
                                        @empty
                                            <div class="h-full flex flex-col items-center justify-center py-6 text-center px-4">
                                                <p class="text-xs text-zinc-600 mb-2">Nenhum grupo disponível.</p>
                                                <button type="button" @click="groupsModal = true; personModal = false"
                                                    class="text-xs text-zinc-400 hover:text-white transition underline underline-offset-2">
                                                    Criar grupo
                                                </button>
                                            </div>
                                        @endforelse
                                    </div>
                                    @if ($groups->isNotEmpty())
                                        <button type="button" @click="groupsModal = true; personModal = false"
                                            class="mt-2 text-[11px] text-zinc-500 hover:text-white flex items-center gap-1 transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                            </svg>
                                            Gerenciar grupos
                                        </button>
                                    @endif
                                </div>

                            </div>

                        </div>

                        {{-- Footer com ações --}}
                        <div class="px-6 py-4 border-t border-white/10 flex gap-3 shrink-0 bg-zinc-950/60">
                            <button type="button" @click="personModal = false; photoPreview = null"
                                class="px-5 py-2.5 border border-white/10 text-white text-sm font-medium hover:bg-white/[0.04] transition rounded-lg">
                                Cancelar
                            </button>
                            <button type="submit"
                                class="flex-1 py-2.5 bg-white text-black font-bold text-sm hover:scale-[1.02] transition rounded-lg shadow-lg">
                                Salvar Pessoa
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>

    </div>

</x-app-layout>