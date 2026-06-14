<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Mind') }}</title>

    {{-- Figtree com todos os pesos --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
        });

        document.addEventListener('alpine:navigated', () => {
            lucide.createIcons();
        });
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-black text-white antialiased overflow-x-hidden">

    {{-- Navegação --}}
    <nav
        class="border-b border-white/10 bg-black sticky top-0 z-40"
        x-data="{ mobileOpen: false }">

        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="flex items-center justify-between h-14">

                {{-- Logo --}}
                <a
                    href="{{ route('dashboard') }}"
                    class="flex items-center">
                    <img
                        src="/images/brain.svg"
                        alt="Mind"
                        class="h-7">
                    <p class="font-bold text-lg">MIND</p>
                </a>

                {{-- Módulos — desktop --}}
                <div class="hidden md:flex items-center gap-1">

                    <a
                        href="{{ route('dashboard') }}"
                        class="
                            px-3 py-1.5 text-xs transition
                            {{ request()->routeIs('dashboard')
                                ? 'text-white'
                                : 'text-zinc-600 hover:text-zinc-300' }}
                        ">
                        Início
                    </a>

                    <span class="text-white/10 select-none">|</span>

                    <a
                        href="#"
                        class="px-3 py-1.5 text-xs text-zinc-700 cursor-not-allowed"
                        title="Em breve">
                        MindMemory
                    </a>

                    <a
                        href="#"
                        class="px-3 py-1.5 text-xs text-zinc-700 cursor-not-allowed"
                        title="Em breve">
                        MindCalendar
                    </a>

                    <a
                        href="#"
                        class="px-3 py-1.5 text-xs text-zinc-700 cursor-not-allowed"
                        title="Em breve">
                        MindArchive
                    </a>

                    <a
                        href="#"
                        class="px-3 py-1.5 text-xs text-zinc-700 cursor-not-allowed"
                        title="Em breve">
                        MindJourney
                    </a>

                    <a
                        href="{{ route('mind-social.index') }}"
                        class="
                            px-3 py-1.5 text-xs transition
                            {{ request()->routeIs('mind-social.*', 'mind-people.*', 'mind-groups.*')
                                ? 'text-white'
                                : 'text-zinc-600 hover:text-zinc-300' }}
                        ">
                        MindSocial
                    </a>

                </div>

                {{-- Usuário — desktop --}}
                <div class="hidden md:flex items-center gap-4">
                    <span class="text-xs text-zinc-700">
                        {{ Auth::user()->name }}
                    </span>
                    <form
                        method="POST"
                        action="{{ route('logout') }}">
                        @csrf
                        <button
                            type="submit"
                            class="text-xs text-zinc-600 hover:text-white transition">
                            Sair
                        </button>
                    </form>
                </div>

                {{-- Hamburger — mobile --}}
                <button
                    @click="mobileOpen = !mobileOpen"
                    class="md:hidden text-zinc-500 hover:text-white transition p-1">
                    <svg
                        class="w-5 h-5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path
                            x-show="!mobileOpen"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.5"
                            d="M4 6h16M4 12h16M4 18h16"/>
                        <path
                            x-show="mobileOpen"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.5"
                            d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>

            </div>
        </div>

        {{-- Menu mobile --}}
        <div
            x-show="mobileOpen"
            x-transition
            class="md:hidden border-t border-white/10 bg-black"
            style="display:none">

            <div class="max-w-7xl mx-auto px-6 py-4 space-y-1">

                <a
                    href="{{ route('dashboard') }}"
                    class="block px-3 py-2.5 text-sm {{ request()->routeIs('dashboard') ? 'text-white' : 'text-zinc-500' }}">
                    Início
                </a>

                <a
                    href="{{ route('mind-social.index') }}"
                    class="block px-3 py-2.5 text-sm {{ request()->routeIs('mind-social.*') ? 'text-white' : 'text-zinc-500' }}">
                    MindSocial
                </a>

                <div class="pt-4 mt-4 border-t border-white/10 flex items-center justify-between">
                    <span class="text-xs text-zinc-700">{{ Auth::user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-xs text-zinc-500 hover:text-white transition">
                            Sair
                        </button>
                    </form>
                </div>

            </div>
        </div>

    </nav>

    {{-- Conteúdo da página --}}
    <main>
        {{ $slot }}
    </main>

</body>
</html>