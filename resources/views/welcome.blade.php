<x-guest-layout>
    
    {{-- Background --}}
    <div class="fixed inset-0 overflow-hidden pointer-events-none">

        {{-- Grid --}}
        <div
            class="absolute inset-0 opacity-15
            bg-[linear-gradient(to_right,#ffffff20_1px,transparent_1px),
            linear-gradient(to_bottom,#ffffff20_1px,transparent_1px)]
            bg-[size:80px_80px]">
        </div>

        {{-- Orbs --}}
        <div class="absolute top-20 left-20 w-72 h-72 bg-white/5 rounded-full blur-3xl"></div>

        <div class="absolute bottom-20 right-20 w-96 h-96 bg-white/5 rounded-full blur-3xl"></div>

        <div class="absolute top-1/2 left-1/2 w-80 h-80 bg-white/[0.03] rounded-full blur-3xl"></div>

    </div>

    <main class="relative z-10">

        <section class="min-h-screen flex items-center">

            <div class="max-w-7xl mx-auto px-8 w-full">

                {{-- Header --}}
                <div class="flex justify-between items-center mb-6">

                    <span class="text-sm font-bold">
                        <?= date('M'); ?>
                    </span>

                    <img
                        src="/images/mind-logo.svg"
                        alt="Mind"
                        class="h-24">

                    <span class="text-sm font-bold">
                        <?= date('Y'); ?>
                    </span>

                </div>

                {{-- Hero --}}
                <div class="grid lg:grid-cols-2 gap-20 items-center">

                    {{-- Texto --}}
                    <div>

                        <div class="mb-8">
                            <span class="text-xs uppercase tracking-[0.3em] text-zinc-500">
                                Mind Palace
                            </span>
                        </div>

                        <h1 class="
                            text-6xl
                            md:text-7xl
                            lg:text-8xl
                            font-black
                            leading-none
                            tracking-tight
                            mb-8
                        ">
                            O seu<br>
                            segundo cérebro.
                        </h1>

                        <p class="
                            text-zinc-400
                            text-lg
                            md:text-xl
                            leading-relaxed
                            max-w-xl
                            mb-10
                        ">
                            Capture experiências, organize memórias,
                            acompanhe sua evolução e construa um
                            espaço digital feito para guardar tudo
                            o que importa.
                        </p>

                        <div class="flex flex-wrap gap-4">

                            <a
                                href="{{ route('authenticate') }}"
                                class="
                                    px-8 py-4
                                    bg-white
                                    text-black
                                    font-bold
                                    hover:scale-105
                                    transition
                                ">
                                Começar
                            </a>

                            <a
                                href="{{ route('authenticate') }}"
                                class="
                                    px-8 py-4
                                    border
                                    border-white/20
                                    hover:border-white
                                    transition
                                ">
                                Entrar
                            </a>

                        </div>

                    </div>

                    {{-- Brain --}}
                    <div class="flex justify-center">

                        <img
                            src="/images/brain.svg"
                            alt="Brain"
                            class="
                                w-full
                                max-w-md
                                opacity-95
                            ">

                    </div>

                </div>

            </div>

        </section>

        {{-- Modules --}}
        <section class="pb-32">

            <div class="max-w-7xl mx-auto px-8">

                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-px bg-white/10">

                    <div class="bg-black p-8">
                        <span class="text-xs text-zinc-600">01</span>

                        <h3 class="text-2xl font-bold mt-4 mb-3">
                            MindMemory
                        </h3>

                        <p class="text-zinc-400">
                            Registre memórias,
                            experiências e pensamentos.
                        </p>
                    </div>

                    <div class="bg-black p-8">
                        <span class="text-xs text-zinc-600">02</span>

                        <h3 class="text-2xl font-bold mt-4 mb-3">
                            MindCollections
                        </h3>

                        <p class="text-zinc-400">
                            Planeje projetos, organize ideias
                            e crie conexões entre seus pensamentos.
                        </p>
                    </div>

                    <div class="bg-black p-8">
                        <span class="text-xs text-zinc-600">03</span>

                        <h3 class="text-2xl font-bold mt-4 mb-3">
                            MindPeople
                        </h3>

                        <p class="text-zinc-400">
                            Conecte-se com pessoas,
                            compartilhe memórias e colabore.
                        </p>
                    </div>

                    <div class="bg-black p-8">
                        <span class="text-xs text-zinc-600">04</span>

                        <h3 class="text-2xl font-bold mt-4 mb-3">
                            MindInsights
                        </h3>

                        <p class="text-zinc-400">
                            Descubra padrões
                            e acompanhe seu crescimento.
                        </p>
                    </div>

                </div>

            </div>

        </section>

    </main>

</x-guest-layout>