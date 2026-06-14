@php
    $startMode = old('name') || old('password_confirmation') || $errors->has('name') || $errors->has('password_confirmation')
        ? 'register'
        : 'login';
@endphp

<x-guest-layout>
    <style>[x-cloak] { display: none !important; }</style>

    <div
        x-data="{ mode: '{{ $startMode }}' }"
        class="relative min-h-screen overflow-hidden bg-black text-white"
    >
        {{-- Fundo --}}
        <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
            <div class="absolute -left-24 top-10 h-80 w-80 rounded-full bg-white/[0.04] blur-3xl"></div>
            <div class="absolute right-0 top-24 h-96 w-96 rounded-full bg-white/[0.03] blur-3xl"></div>
            <div class="absolute bottom-0 left-1/4 h-80 w-80 rounded-full bg-white/[0.03] blur-3xl"></div>
            <div class="absolute inset-0 opacity-[0.10] [background-image:linear-gradient(rgba(255,255,255,0.10)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.10)_1px,transparent_1px)] [background-size:72px_72px]"></div>
        </div>

        <main class="relative z-10 mx-auto flex min-h-screen max-w-7xl flex-col px-6 py-8 lg:px-8">
            <div class="mb-12 flex items-center justify-between">
                <a href="/" class="flex items-center gap-3 group" aria-label="Mind — início">
                    <img
                        src="/images/brain.svg"
                        alt="Mind"
                        class="h-10 w-auto"
                    >
                    <div class="leading-tight">
                        <span class="block text-xl font-black tracking-tight text-white">Mind</span>
                        <span class="block text-xs text-white/40">Seu segundo cérebro</span>
                    </div>
                </a>

                <span class="hidden text-xs uppercase tracking-[0.28em] text-white/30 sm:block">
                    Mind Palace
                </span>
            </div>

            <div class="grid flex-1 items-center gap-12 lg:grid-cols-[1.05fr_0.95fr]">
                {{-- Coluna esquerda --}}
                <section class="max-w-2xl">
                    <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3.5 py-1.5 text-xs font-medium text-white/70">
                        <span class="h-1.5 w-1.5 rounded-full bg-white"></span>
                        Um lugar para registrar o que importa
                    </div>

                    <h1 class="max-w-xl text-4xl font-semibold tracking-tight text-white sm:text-5xl lg:text-6xl">
                        Guarde o que <span class="text-white/70">passa pela sua mente</span>.
                    </h1>

                    <p class="mt-6 max-w-xl text-base leading-7 text-white/55 sm:text-lg">
                        Entre para organizar experiências, anotações e memórias em um espaço limpo, cronológico e pessoal.
                    </p>

                    <div class="mt-10 grid gap-4 sm:grid-cols-3">
                        <div class="rounded-3xl border border-white/10 bg-white/[0.04] p-5">
                            <span class="text-xs uppercase tracking-[0.22em] text-white/30">MindMemory</span>
                            <p class="mt-3 text-sm leading-6 text-white/55">Registre experiências, ideias e lembranças.</p>
                        </div>
                        <div class="rounded-3xl border border-white/10 bg-white/[0.04] p-5">
                            <span class="text-xs uppercase tracking-[0.22em] text-white/30">MindCollections</span>
                            <p class="mt-3 text-sm leading-6 text-white/55">Planeje, organize e crie conexões entre seus pensamentos.</p>
                        </div>
                        <div class="rounded-3xl border border-white/10 bg-white/[0.04] p-5">
                            <span class="text-xs uppercase tracking-[0.22em] text-white/30">MindPeople</span>
                            <p class="mt-3 text-sm leading-6 text-white/55">Conecte-se com pessoas, compartilhe memórias e colabore.</p>
                        </div>
                    </div>
                </section>

                {{-- Coluna direita --}}
                <section class="relative">
                    <div class="absolute inset-0 rounded-[2rem] bg-white/[0.04] blur-3xl"></div>

                    <div class="relative overflow-hidden rounded-[2rem] border border-white/10 bg-white/[0.04] p-6 shadow-2xl shadow-black/30 backdrop-blur-sm sm:p-8">
                        <div class="mb-6 flex rounded-2xl border border-white/10 bg-black/25 p-1">
                            <button
                                type="button"
                                @click="mode = 'login'"
                                :class="mode === 'login'
                                    ? 'bg-white text-black'
                                    : 'text-white/60 hover:text-white'"
                                class="flex-1 rounded-xl px-4 py-2.5 text-sm font-semibold transition-colors"
                            >
                                Entrar
                            </button>

                            <button
                                type="button"
                                @click="mode = 'register'"
                                :class="mode === 'register'
                                    ? 'bg-white text-black'
                                    : 'text-white/60 hover:text-white'"
                                class="flex-1 rounded-xl px-4 py-2.5 text-sm font-semibold transition-colors"
                            >
                                Cadastrar
                            </button>
                        </div>

                        {{-- Login --}}
                        <div x-show="mode === 'login'" x-cloak>
                            <div class="mb-6">
                                <h2 class="text-2xl font-semibold tracking-tight text-white">Entrar</h2>
                                <p class="mt-2 text-sm text-white/45">Acesse seu palácio mental.</p>
                            </div>

                            <x-auth-session-status class="mb-4" :status="session('status')" />

                            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                                @csrf

                                <div>
                                    <label for="login-email" class="block text-sm font-medium text-white/55">Email</label>
                                    <input
                                        id="login-email"
                                        type="email"
                                        name="email"
                                        value="{{ old('email') }}"
                                        required
                                        autofocus
                                        autocomplete="username"
                                        class="mt-1 block w-full rounded-2xl border border-white/10 bg-black/50 px-4 py-3 text-white placeholder-white/25 outline-none transition focus:border-white/30 focus:ring-0"
                                        placeholder="voce@email.com"
                                    >
                                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                                </div>

                                <div>
                                    <label for="login-password" class="block text-sm font-medium text-white/55">Senha</label>
                                    <input
                                        id="login-password"
                                        type="password"
                                        name="password"
                                        required
                                        autocomplete="current-password"
                                        class="mt-1 block w-full rounded-2xl border border-white/10 bg-black/50 px-4 py-3 text-white placeholder-white/25 outline-none transition focus:border-white/30 focus:ring-0"
                                        placeholder="••••••••"
                                    >
                                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                                </div>

                                <div class="flex items-center justify-between gap-4">
                                    <label for="remember_me" class="flex items-center gap-2 text-sm text-white/45">
                                        <input
                                            id="remember_me"
                                            type="checkbox"
                                            name="remember"
                                            class="rounded border-white/15 bg-black/50 text-white focus:ring-0"
                                        >
                                        Lembrar-me
                                    </label>

                                    @if (Route::has('password.request'))
                                        <a href="{{ route('password.request') }}" class="text-sm text-white/55 transition hover:text-white">
                                            Esqueceu a senha?
                                        </a>
                                    @endif
                                </div>

                                <button
                                    type="submit"
                                    class="mt-2 w-full rounded-2xl bg-white px-4 py-3 text-sm font-semibold text-black transition hover:bg-white/90"
                                >
                                    Entrar
                                </button>
                            </form>
                        </div>

                        {{-- Cadastro --}}
                        <div x-show="mode === 'register'" x-cloak>
                            <div class="mb-6">
                                <h2 class="text-2xl font-semibold tracking-tight text-white">Criar conta</h2>
                                <p class="mt-2 text-sm text-white/45">Comece a construir seu Mind Palace.</p>
                            </div>

                            <form method="POST" action="{{ route('register') }}" class="space-y-4">
                                @csrf

                                <div>
                                    <label for="register-name" class="block text-sm font-medium text-white/55">Nome</label>
                                    <input
                                        id="register-name"
                                        type="text"
                                        name="name"
                                        value="{{ old('name') }}"
                                        required
                                        autocomplete="name"
                                        class="mt-1 block w-full rounded-2xl border border-white/10 bg-black/50 px-4 py-3 text-white placeholder-white/25 outline-none transition focus:border-white/30 focus:ring-0"
                                        placeholder="Seu nome"
                                    >
                                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                                </div>

                                <div>
                                    <label for="register-email" class="block text-sm font-medium text-white/55">Email</label>
                                    <input
                                        id="register-email"
                                        type="email"
                                        name="email"
                                        value="{{ old('email') }}"
                                        required
                                        autocomplete="username"
                                        class="mt-1 block w-full rounded-2xl border border-white/10 bg-black/50 px-4 py-3 text-white placeholder-white/25 outline-none transition focus:border-white/30 focus:ring-0"
                                        placeholder="voce@email.com"
                                    >
                                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                                </div>

                                <div>
                                    <label for="register-password" class="block text-sm font-medium text-white/55">Senha</label>
                                    <input
                                        id="register-password"
                                        type="password"
                                        name="password"
                                        required
                                        autocomplete="new-password"
                                        class="mt-1 block w-full rounded-2xl border border-white/10 bg-black/50 px-4 py-3 text-white placeholder-white/25 outline-none transition focus:border-white/30 focus:ring-0"
                                        placeholder="••••••••"
                                    >
                                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                                </div>

                                <div>
                                    <label for="register-password_confirmation" class="block text-sm font-medium text-white/55">Confirmar senha</label>
                                    <input
                                        id="register-password_confirmation"
                                        type="password"
                                        name="password_confirmation"
                                        required
                                        autocomplete="new-password"
                                        class="mt-1 block w-full rounded-2xl border border-white/10 bg-black/50 px-4 py-3 text-white placeholder-white/25 outline-none transition focus:border-white/30 focus:ring-0"
                                        placeholder="••••••••"
                                    >
                                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                                </div>

                                <button
                                    type="submit"
                                    class="mt-2 w-full rounded-2xl bg-white px-4 py-3 text-sm font-semibold text-black transition hover:bg-white/90"
                                >
                                    Criar conta
                                </button>
                            </form>
                        </div>
                    </div>
                </section>
            </div>
        </main>
    </div>
</x-guest-layout>