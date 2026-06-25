@props(['groups'])

<x-modal
    name="person-modal"
    max-width="2xl"
    :show="$errors->person->any()"
    focusable
>
    <div class="bg-zinc-950 text-white flex flex-col max-h-[92vh]">
        {{-- Header --}}
        <div class="px-6 border-b border-white/10 flex items-center justify-between shrink-0">
            <div>
                <span class="text-[10px] uppercase tracking-[0.3em] text-zinc-500 font-semibold">MindSocial</span>
                <h2 class="text-lg font-black text-white mt-0.5">Nova Pessoa</h2>
            </div>

            <button
                type="button"
                x-on:click="$dispatch('close-modal', 'person-modal')"
                class="text-zinc-500 hover:text-white transition p-1.5 rounded-lg hover:bg-white/5"
                aria-label="Fechar"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="overflow-y-auto flex-1">
            <form
                method="POST"
                action="{{ route('mind-people.store') }}"
                enctype="multipart/form-data"
                x-data="{ submitting: false }"
                @submit="submitting = true"
            >
                @csrf

                <div class="p-6 space-y-6">

                    {{-- Bloco topo: foto + campos básicos --}}
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

                                <input
                                    type="file"
                                    name="photo"
                                    accept="image/jpeg,image/png,image/webp"
                                    @change="handlePhotoChange($event)"
                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                >

                                <div class="absolute inset-0 rounded-full bg-black/50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition pointer-events-none">
                                    <span class="text-white text-[11px] font-medium">Alterar</span>
                                </div>
                            </div>

                            <p class="text-[10px] text-zinc-600 text-center leading-relaxed">
                                JPG, PNG ou WEBP<br>máx. 3 MB
                            </p>
                            @error('photo', 'person')
                                <p class="text-[10px] text-red-400 text-center mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Nome, apelido e aniversário --}}
                        <div class="flex-1 min-w-0 space-y-3">

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="text-[10px] uppercase tracking-widest text-zinc-500 font-semibold block mb-1.5">
                                        Nome Completo *
                                    </label>
                                    <input
                                        type="text"
                                        name="name"
                                        required
                                        value="{{ $errors->person->any() ? old('name') : '' }}"
                                        placeholder="Ex: João da Silva"
                                        class="w-full bg-white/[0.03] border @error('name', 'person') border-red-500/50 @else border-white/10 @enderror text-white text-sm px-3 py-2.5 focus:outline-none focus:border-white/30 placeholder:text-zinc-700 transition rounded-lg"
                                    >
                                    @error('name', 'person')
                                        <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="text-[10px] uppercase tracking-widest text-zinc-500 font-semibold block mb-1.5">
                                        Apelido
                                    </label>
                                    <input
                                        type="text"
                                        name="nickname"
                                        value="{{ $errors->person->any() ? old('nickname') : '' }}"
                                        placeholder="Como o chama?"
                                        class="w-full bg-white/[0.03] border border-white/10 text-white text-sm px-3 py-2.5 focus:outline-none focus:border-white/30 placeholder:text-zinc-700 transition rounded-lg"
                                    >
                                </div>
                            </div>

                            {{-- Aniversário --}}
                            <div>
                                <label class="text-[10px] uppercase tracking-widest text-zinc-500 font-semibold block mb-2">
                                    Aniversário *
                                </label>

                                <div
                                    x-data="{
                                        dayOpen: false, monthOpen: false, yearOpen: false,
                                        selectedDay: '{{ $errors->person->any() ? old('birth_day', '1') : '1' }}',
                                        selectedMonth: '{{ $errors->person->any() ? old('birth_month', '1') : '1' }}',
                                        selectedYear: '{{ $errors->person->any() ? old('birth_year', '2000') : '2000' }}',
                                        months: ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'],
                                        days: Array.from({ length: 31 }, (_, i) => i + 1),
                                        years: Array.from({ length: 100 }, (_, i) => new Date().getFullYear() - i),
                                        closeAll() { this.dayOpen = false; this.monthOpen = false; this.yearOpen = false; },
                                        selectDay(d) { this.selectedDay = d; this.dayOpen = false; },
                                        selectMonth(m) { this.selectedMonth = m; this.monthOpen = false; },
                                        selectYear(y) { this.selectedYear = y; this.yearOpen = false; }
                                    }"
                                    @click.away="closeAll()"
                                    class="flex gap-1 w-full max-w-[340px]"
                                >
                                    {{-- Dia --}}
                                    <div class="relative flex-1">
                                        <button type="button" @click="dayOpen = !dayOpen; monthOpen = false; yearOpen = false" class="w-full bg-white/[0.03] border border-white/10 text-xs px-2 py-2.5 rounded-lg text-left transition flex items-center justify-between text-white shadow-inner hover:border-white/30">
                                            <span x-text="selectedDay || 'Dia'"></span>
                                        </button>
                                        <div x-show="dayOpen" x-transition class="absolute z-30 mt-1 w-[140px] bg-zinc-900/95 backdrop-blur-md border border-white/10 rounded-lg shadow-xl max-h-36 overflow-y-auto grid grid-cols-5 gap-1 p-1.5 scroll-smooth custom-scrollbar" style="left: 0;">
                                            <template x-for="d in days" :key="d">
                                                <button type="button" @click="selectDay(d)" class="text-[10px] py-1.5 rounded hover:bg-white/10 transition" :class="selectedDay == d ? 'bg-white/20 text-white font-bold' : 'text-zinc-300'" x-text="d"></button>
                                            </template>
                                        </div>
                                    </div>
                                    
                                    {{-- Mês --}}
                                    <div class="relative flex-1">
                                        <button type="button" @click="monthOpen = !monthOpen; dayOpen = false; yearOpen = false" class="w-full bg-white/[0.03] border border-white/10 text-xs px-2 py-2.5 rounded-lg text-left transition flex items-center justify-between text-white shadow-inner hover:border-white/30">
                                            <span x-text="selectedMonth ? months[selectedMonth - 1] : 'Mês'"></span>
                                        </button>
                                        <div x-show="monthOpen" x-transition class="absolute z-30 mt-1 w-[90px] bg-zinc-900/95 backdrop-blur-md border border-white/10 rounded-lg shadow-xl max-h-36 overflow-y-auto flex flex-col p-1 scroll-smooth custom-scrollbar" style="left: 0;">
                                            <template x-for="(m, index) in months" :key="index">
                                                <button type="button" @click="selectMonth(index + 1)" class="text-xs text-left px-2 py-1.5 rounded hover:bg-white/10 transition" :class="selectedMonth == index + 1 ? 'bg-white/20 text-white font-bold' : 'text-zinc-300'" x-text="m"></button>
                                            </template>
                                        </div>
                                    </div>

                                    {{-- Ano --}}
                                    <div class="relative flex-1">
                                        <button type="button" @click="yearOpen = !yearOpen; dayOpen = false; monthOpen = false" class="w-full bg-white/[0.03] border border-white/10 text-xs px-2 py-2.5 rounded-lg text-left transition flex items-center justify-between text-white shadow-inner hover:border-white/30">
                                            <span x-text="selectedYear || 'Ano'"></span>
                                        </button>
                                        <div x-show="yearOpen" x-transition class="absolute z-30 mt-1 w-[140px] bg-zinc-900/95 backdrop-blur-md border border-white/10 rounded-lg shadow-xl max-h-36 overflow-y-auto grid grid-cols-3 gap-1 p-1.5 scroll-smooth custom-scrollbar" style="right: 0;">
                                            <template x-for="y in years" :key="y">
                                                <button type="button" @click="selectYear(y)" class="text-[10px] py-1.5 rounded hover:bg-white/10 transition" :class="selectedYear == y ? 'bg-white/20 text-white font-bold' : 'text-zinc-300'" x-text="y"></button>
                                            </template>
                                        </div>
                                    </div>

                                    <input type="hidden" name="birth_day" :value="selectedDay">
                                    <input type="hidden" name="birth_month" :value="selectedMonth">
                                    <input type="hidden" name="birth_year" :value="selectedYear">
                                    @if ($errors->person->has('birth_day') || $errors->person->has('birth_month') || $errors->person->has('birth_year'))
                                        <p class="text-xs text-red-400 mt-1.5">
                                            {{ $errors->person->first('birth_day') ?: ($errors->person->first('birth_month') ?: $errors->person->first('birth_year')) }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Separador --}}
                    <div class="border-t border-white/[0.06]"></div>

                    {{-- Bloco baixo: notas + grupos --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                        {{-- Notas --}}
                        <div class="flex flex-col">
                            <label class="text-[10px] uppercase tracking-widest text-zinc-500 font-semibold block mb-1.5">
                                Notas
                            </label>

                            <textarea
                                name="notes"
                                rows="5"
                                placeholder="Onde se conheceram, profissão, interesses..."
                                class="flex-1 w-full bg-white/[0.03] border border-white/10 text-white text-sm px-3 py-2.5 focus:outline-none focus:border-white/30 placeholder:text-zinc-700 transition resize-none rounded-lg"
                            >{{ $errors->person->any() ? old('notes') : '' }}</textarea>
                        </div>

                        {{-- Grupos --}}
                        <div class="flex flex-col">
                            <label class="text-[10px] uppercase tracking-widest text-zinc-500 font-semibold block mb-1.5">
                                Grupos
                            </label>

                            <div class="flex-1 border border-white/10 bg-white/[0.02] rounded-lg overflow-y-auto" style="max-height: 9.5rem;">
                                @forelse ($groups as $group)
                                    <label class="flex items-center gap-2.5 text-sm text-zinc-400 cursor-pointer hover:text-white transition px-3 py-2 hover:bg-white/[0.03] first:pt-3 last:pb-3">
                                        <input
                                            type="checkbox"
                                            name="groups[]"
                                            value="{{ $group->id }}"
                                            class="w-3.5 h-3.5 rounded border-white/20 bg-black/50 text-white focus:ring-0 focus:ring-offset-0 shrink-0"
                                        >
                                        <div class="flex items-center gap-1.5 min-w-0">
                                            <span
                                                class="w-3.5 h-3.5 inline-block text-zinc-600 shrink-0"
                                                data-lucide="{{ $group->icon }}"
                                            ></span>
                                            <span class="truncate">{{ $group->name }}</span>
                                        </div>
                                    </label>
                                @empty
                                    <div class="h-full flex flex-col items-center justify-center py-6 text-center px-4">
                                        <p class="text-xs text-zinc-600 mb-2">Nenhum grupo disponível.</p>
                                        <button
                                            type="button"
                                            x-on:click="$dispatch('close-modal', 'person-modal'); $dispatch('open-modal', 'groups-modal')"
                                            class="text-xs text-zinc-400 hover:text-white transition underline underline-offset-2"
                                        >
                                            Criar grupo
                                        </button>
                                    </div>
                                @endforelse

                                @error('groups.*', 'person')
                                    <p class="text-xs text-red-400 mt-1.5">{{ $message }}</p>
                                @enderror
                            </div>

                            

                            @if ($groups->isNotEmpty())
                                <button
                                    type="button"
                                    x-on:click="$dispatch('close-modal', 'person-modal'); $dispatch('open-modal', 'groups-modal')"
                                    class="mt-2 text-[11px] text-zinc-500 hover:text-white flex items-center gap-1 transition"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Gerenciar grupos
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="px-6 py-4 border-t border-white/10 flex gap-3 shrink-0 bg-zinc-950/60">
                    <button
                        type="button"
                        x-on:click="$dispatch('close-modal', 'person-modal'); photoPreview = null"
                        class="px-5 py-2.5 border border-white/10 text-white text-sm font-medium hover:bg-white/[0.04] transition rounded-lg"
                    >
                        Cancelar
                    </button>

                    <button
                        type="submit"
                        :disabled="submitting"
                        class="flex-1 py-2.5 bg-white text-black font-bold text-sm hover:scale-[1.02] transition rounded-lg shadow-lg disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100 inline-flex items-center justify-center gap-2"
                    >
                        Salvar Pessoa
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-modal>