<x-app-layout>

    {{-- Página cobre qualquer fundo do layout --}}
    <div class="relative min-h-screen bg-black overflow-hidden">

        {{-- Background: grid + orbs --}}
        

        <div class="relative z-10 max-w-7xl mx-auto px-8 py-14">

            {{-- Greeting --}}
            <div class="flex justify-between items-start mb-20">

                <div>
                    <span class="text-xs uppercase tracking-[0.3em] text-zinc-600">
                        Mind Palace
                    </span>

                    <h1 class="
                        text-6xl
                        md:text-7xl
                        lg:text-8xl
                        font-black
                        leading-none
                        tracking-tight
                        mt-5
                    ">
                        @php
                            $h   = now()->hour;
                            $saudacao = $h < 12 ? 'Bom dia' : ($h < 18 ? 'Boa tarde' : 'Boa noite');
                        @endphp
                        {{ $saudacao }},<br>
                        {{ Auth::user()->name }}.
                    </h1>

                    <p class="text-zinc-600 mt-5 text-sm tracking-wide">
                        {{ now()->isoFormat('dddd, D [de] MMMM [de] Y') }}
                    </p>
                </div>

                <div class="flex items-center gap-6">
                    <video class="h-24" src="videos/brain.mp4" autoplay muted loop></video>
                    <div class="text-right hidden md:flex flex-col items-end gap-1 pt-1">
                        <span class="text-xs text-zinc-700 font-mono">{{ date('H:i') }}</span>
                        <span class="text-xs text-zinc-800">{{ date('Y') }}</span>
                    </div>
                </div>

            </div>

            {{-- Módulos --}}
            <div class="mb-6">
                <span class="text-xs uppercase tracking-[0.3em] text-zinc-600">
                    Módulos
                </span>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-px bg-white/10 mb-5">

                <a href="#" class="bg-black p-8 hover:bg-white/[0.04] transition-colors group">
                    <span class="text-xs text-zinc-700">01</span>
                    <h3 class="text-xl font-black mt-4 mb-3 group-hover:text-white transition-colors">
                        MindMemory
                    </h3>
                    <p class="text-zinc-500 text-sm mb-10 leading-relaxed">
                        Registre memórias,<br>experiências e pensamentos.
                    </p>
                    <p class="text-4xl font-black">{{ $memoriesCount ?? '—' }}</p>
                    <p class="text-xs text-zinc-700 mt-1">memórias</p>
                </a>

                <a href="#" class="bg-black p-8 hover:bg-white/[0.04] transition-colors group">
                    <span class="text-xs text-zinc-700">02</span>
                    <h3 class="text-xl font-black mt-4 mb-3 group-hover:text-white transition-colors">
                        MindCalendar
                    </h3>
                    <p class="text-zinc-500 text-sm mb-10 leading-relaxed">
                        Visualize sua jornada<br>ao longo do tempo.
                    </p>
                    <p class="text-4xl font-black">{{ $eventsCount ?? '—' }}</p>
                    <p class="text-xs text-zinc-700 mt-1">eventos</p>
                </a>

                <a href="#" class="bg-black p-8 hover:bg-white/[0.04] transition-colors group">
                    <span class="text-xs text-zinc-700">03</span>
                    <h3 class="text-xl font-black mt-4 mb-3 group-hover:text-white transition-colors">
                        MindArchive
                    </h3>
                    <p class="text-zinc-500 text-sm mb-10 leading-relaxed">
                        Guarde tudo em<br>um único lugar.
                    </p>
                    <p class="text-4xl font-black">{{ $archiveCount ?? '—' }}</p>
                    <p class="text-xs text-zinc-700 mt-1">arquivos</p>
                </a>

                <a href="#" class="bg-black p-8 hover:bg-white/[0.04] transition-colors group">
                    <span class="text-xs text-zinc-700">04</span>
                    <h3 class="text-xl font-black mt-4 mb-3 group-hover:text-white transition-colors">
                        MindJourney
                    </h3>
                    <p class="text-zinc-500 text-sm mb-10 leading-relaxed">
                        Descubra padrões e<br>acompanhe seu crescimento.
                    </p>
                    <p class="text-4xl font-black">{{ $insightsCount ?? '—' }}</p>
                    <p class="text-xs text-zinc-700 mt-1">insights</p>
                </a>

            </div>

            {{-- Linha inferior: MindSocial + Ação rápida --}}
            <div class="grid lg:grid-cols-3 gap-4">

                {{-- MindSocial --}}
                <div class="lg:col-span-2 relative overflow-hidden border border-white/10 bg-white/[0.02] p-8">

                    {{-- Halftone --}}
                    <div
                        class="absolute inset-0 opacity-[0.03] pointer-events-none"
                        style="
                            background-image: radial-gradient(rgba(255,255,255,.6) 1px, transparent 1px);
                            background-size: 14px 14px;
                        ">
                    </div>

                    <div class="relative z-10">
                        <div class="flex items-center justify-between mb-6">
                            <span class="text-xs uppercase tracking-[0.3em] text-zinc-600">
                                MindSocial
                            </span>
                            <a
                                href="{{ route('mind-social.index') }}"
                                class="text-xs text-zinc-600 hover:text-white transition">
                                Ver tudo →
                            </a>
                        </div>

                        <p class="text-zinc-400 text-sm max-w-sm leading-relaxed mb-8">
                            Pessoas, conexões e relações que fazem parte da sua história.
                        </p>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="border border-white/10 bg-white/[0.03] px-6 py-5">
                                <p class="text-xs text-zinc-700 uppercase tracking-widest">Pessoas</p>
                                <p class="text-4xl font-black mt-3">{{ $peopleCnt ?? '—' }}</p>
                            </div>
                            <div class="border border-white/10 bg-white/[0.03] px-6 py-5">
                                <p class="text-xs text-zinc-700 uppercase tracking-widest">Grupos</p>
                                <p class="text-4xl font-black mt-3">{{ $groupsCnt ?? '—' }}</p>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Ação rápida --}}
                <div class="border border-white/10 bg-white/[0.02] p-8 flex flex-col justify-between">

                    <div>
                        <span class="text-xs uppercase tracking-[0.3em] text-zinc-600">
                            Ação rápida
                        </span>
                        <h3 class="text-2xl font-black mt-5 mb-2 leading-tight">
                            O que você<br>quer registrar?
                        </h3>
                        <p class="text-zinc-600 text-sm">
                            Capture antes que passe.
                        </p>
                    </div>

                    <div class="flex flex-col gap-3 mt-10">
                        <a
                            href="#"
                            class="
                                px-5 py-3
                                bg-white
                                text-black
                                font-bold
                                text-sm
                                text-center
                                hover:scale-[1.02]
                                transition
                            ">
                            + Nova memória
                        </a>
                        <a
                            href="{{ route('mind-social.index') }}"
                            class="
                                px-5 py-3
                                border border-white/20
                                text-sm
                                text-center
                                hover:border-white
                                transition
                            ">
                            Ver MindSocial
                        </a>
                    </div>

                </div>

            </div>

        </div>

    </div>

</x-app-layout>

{{--
    NOTAS PARA O CONTROLLER (DashboardController@index):
    Passe as seguintes variáveis para a view:
    - $memoriesCount  → contagem de memórias do usuário
    - $eventsCount    → contagem de eventos
    - $archiveCount   → contagem de arquivos
    - $insightsCount  → contagem de insights
    - $peopleCnt      → Auth::user()->people()->count()
    - $groupsCnt      → Auth::user()->groups()->count()
    Enquanto os módulos não estiverem prontos, os '—' aparecem automaticamente via ?? '—'.
--}}