@props(['groups'])

<template x-if="selectedPerson">
    <div class="space-y-5">

        <div class="flex items-start justify-between border-b border-white/10 pb-5">
            <div class="flex items-center gap-4">
                {{-- Foto editável --}}
                <div class="relative group cursor-pointer w-16 h-16 shrink-0">
                    <div class="w-16 h-16 rounded-full border border-white/10 bg-white/[0.03] flex items-center justify-center overflow-hidden transition-all duration-300 group-hover:border-white/30 shadow-inner">
                        <template x-if="photoPreview || selectedPerson.photo">
                            <img :src="photoPreview ? photoPreview : selectedPerson.photo" class="w-full h-full object-cover object-center" alt="Prévia">
                        </template>

                        <template x-if="!photoPreview && !selectedPerson.photo && !compressing">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-zinc-600 group-hover:text-zinc-400 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </template>

                        <template x-if="compressing">
                            <div class="absolute inset-0 bg-black/70 flex items-center justify-center">
                                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                </svg>
                            </div>
                        </template>
                    </div>

                    <input type="file" form="edit-person-form" name="photo" accept="image/jpeg,image/png,image/webp" @change="handlePhotoChange($event)" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                    <div class="absolute inset-0 rounded-full bg-black/50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition pointer-events-none">
                        <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                    </div>
                </div>

                <div class="min-w-0">
                    <p class="text-[10px] uppercase tracking-widest text-zinc-500 font-semibold mb-0.5">Editando</p>
                    <h2 class="text-xl font-bold text-white truncate max-w-[150px]" x-text="selectedPerson.name"></h2>
                    <p class="text-sm text-zinc-400 truncate max-w-[150px]" x-show="selectedPerson.nickname" x-text="'@' + selectedPerson.nickname"></p>
                </div>
            </div>

            <div class="flex items-center gap-2 -mr-2">
                {{-- Delete --}}
                <form
                    :action="`/mind-people/${selectedPerson.id}`"
                    method="POST"
                    onsubmit="return confirm('Excluir esta pessoa? Esta ação não poderá ser desfeita.')"
                >
                    @csrf
                    @method('DELETE')

                    <button
                        type="submit"
                        class="text-zinc-500 hover:text-red-400 bg-white/[0.03] hover:bg-red-500/10 rounded-lg transition p-2"
                        title="Excluir pessoa"
                    >
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M19 7L18.132 19.142A2 2 0 0116.138 21H7.862A2 2 0 015.868 19.142L5 7m5-3h4m-5 0a1 1 0 011-1h2a1 1 0 011 1m-4 0h4M4 7h16"
                            />
                        </svg>
                    </button>
                </form>

                {{-- Close --}}
                <button
                    type="button"
                    @click="selectedPerson = null; panelOpen = false; window.dispatchEvent(new CustomEvent('fisheye-unfocus'))"
                    class="text-zinc-500 hover:text-white bg-white/[0.03] hover:bg-white/10 rounded-lg transition p-2"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <form :action="`/mind-people/${selectedPerson.id}`" id="edit-person-form" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="text-[10px] uppercase tracking-widest text-zinc-500 font-semibold block mb-1.5">Nome Completo *</label>
                    <input type="text" name="name" x-model="selectedPerson.name" required class="w-full bg-white/[0.03] border border-white/10 px-3 py-2.5 text-sm text-white rounded-lg focus:outline-none focus:border-white/30 transition shadow-inner">
                </div>

                <div>
                    <label class="text-[10px] uppercase tracking-widest text-zinc-500 font-semibold block mb-1.5">Apelido</label>
                    <input type="text" name="nickname" x-model="selectedPerson.nickname" class="w-full bg-white/[0.03] border border-white/10 px-3 py-2.5 text-sm text-white rounded-lg focus:outline-none focus:border-white/30 transition shadow-inner">
                </div>

                <div>
                    <label class="text-[10px] uppercase tracking-widest text-zinc-500 font-semibold block mb-1.5">Aniversário</label>
                    <div
                        x-data="{
                            dayOpen: false, monthOpen: false, yearOpen: false,
                            months: ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'],
                            days: Array.from({ length: 31 }, (_, i) => i + 1),
                            years: Array.from({ length: 100 }, (_, i) => new Date().getFullYear() - i),
                            closeAll() { this.dayOpen = false; this.monthOpen = false; this.yearOpen = false; }
                        }"
                        @click.away="closeAll()"
                        class="flex gap-1"
                    >
                        <div class="relative flex-1">
                            <button type="button" @click="dayOpen = !dayOpen; monthOpen = false; yearOpen = false" class="w-full bg-white/[0.03] border border-white/10 text-xs px-2 py-2.5 rounded-lg text-left transition flex items-center justify-between text-white shadow-inner hover:border-white/30">
                                <span x-text="selectedPerson.birth_day || 'Dia'"></span>
                            </button>
                            <div x-show="dayOpen" x-transition class="absolute z-30 mt-1 w-[140px] bg-zinc-900/95 backdrop-blur-md border border-white/10 rounded-lg shadow-xl max-h-36 overflow-y-auto grid grid-cols-5 gap-1 p-1.5 scroll-smooth custom-scrollbar" style="left: 0;">
                                <template x-for="d in days" :key="d">
                                    <button type="button" @click="selectedPerson.birth_day = d; dayOpen = false" class="text-[10px] py-1.5 rounded hover:bg-white/10 transition" :class="selectedPerson.birth_day == d ? 'bg-white/20 text-white font-bold' : 'text-zinc-300'" x-text="d"></button>
                                </template>
                            </div>
                        </div>

                        <div class="relative flex-1">
                            <button type="button" @click="monthOpen = !monthOpen; dayOpen = false; yearOpen = false" class="w-full bg-white/[0.03] border border-white/10 text-xs px-2 py-2.5 rounded-lg text-left transition flex items-center justify-between text-white shadow-inner hover:border-white/30">
                                <span x-text="selectedPerson.birth_month ? months[selectedPerson.birth_month - 1] : 'Mês'"></span>
                            </button>
                            <div x-show="monthOpen" x-transition class="absolute z-30 mt-1 w-[90px] bg-zinc-900/95 backdrop-blur-md border border-white/10 rounded-lg shadow-xl max-h-36 overflow-y-auto flex flex-col p-1 scroll-smooth custom-scrollbar" style="left: 0;">
                                <template x-for="(m, index) in months" :key="index">
                                    <button type="button" @click="selectedPerson.birth_month = index + 1; monthOpen = false" class="text-xs text-left px-2 py-1.5 rounded hover:bg-white/10 transition" :class="selectedPerson.birth_month == index + 1 ? 'bg-white/20 text-white font-bold' : 'text-zinc-300'" x-text="m"></button>
                                </template>
                            </div>
                        </div>

                        <div class="relative flex-1">
                            <button type="button" @click="yearOpen = !yearOpen; dayOpen = false; monthOpen = false" class="w-full bg-white/[0.03] border border-white/10 text-xs px-2 py-2.5 rounded-lg text-left transition flex items-center justify-between text-white shadow-inner hover:border-white/30">
                                <span x-text="selectedPerson.birth_year || 'Ano'"></span>
                            </button>
                            <div x-show="yearOpen" x-transition class="absolute z-30 mt-1 w-[140px] bg-zinc-900/95 backdrop-blur-md border border-white/10 rounded-lg shadow-xl max-h-36 overflow-y-auto grid grid-cols-3 gap-1 p-1.5 scroll-smooth custom-scrollbar" style="right: 0;">
                                <template x-for="y in years" :key="y">
                                    <button type="button" @click="selectedPerson.birth_year = y; yearOpen = false" class="text-[10px] py-1.5 rounded hover:bg-white/10 transition" :class="selectedPerson.birth_year == y ? 'bg-white/20 text-white font-bold' : 'text-zinc-300'" x-text="y"></button>
                                </template>
                            </div>
                        </div>

                        <input type="hidden" name="birth_day" :value="selectedPerson.birth_day">
                        <input type="hidden" name="birth_month" :value="selectedPerson.birth_month">
                        <input type="hidden" name="birth_year" :value="selectedPerson.birth_year">
                    </div>
                </div>
            </div>

            <div>
                <label class="text-[10px] uppercase tracking-widest text-zinc-500 font-semibold block mb-1.5">Grupos</label>
                <div class="border border-white/10 bg-white/[0.02] rounded-lg p-2 max-h-28 overflow-y-auto grid grid-cols-2 gap-1 shadow-inner">
                    @foreach ($groups as $group)
                        <label class="flex items-center gap-2 text-xs text-zinc-400 cursor-pointer hover:text-white transition px-2 py-1.5 hover:bg-white/[0.03] rounded">
                            <input type="checkbox" name="groups[]" value="{{ $group->id }}"
                                :checked="selectedPerson.groups && selectedPerson.groups.some(g => (g.id || g) == {{ $group->id }})"
                                class="w-3.5 h-3.5 rounded border-white/20 bg-black/50 text-white focus:ring-0 focus:ring-offset-0 shrink-0">
                            <span class="truncate">{{ $group->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div>
                <label class="text-[10px] uppercase tracking-widest text-zinc-500 font-semibold block mb-1.5">Notas</label>
                <textarea name="notes" rows="3" x-model="selectedPerson.notes" class="w-full bg-white/[0.03] border border-white/10 px-3 py-2.5 text-sm text-white rounded-lg focus:outline-none focus:border-white/30 transition resize-none shadow-inner"></textarea>
            </div>

            <div class="pt-3">
                <button type="submit" class="w-full px-5 py-3 bg-white text-black font-semibold text-sm hover:scale-[1.02] transition rounded-lg shadow-lg">
                    Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</template>