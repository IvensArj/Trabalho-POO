@props([
    'name' => 'icon',
    'selected' => 'users',
    'icons' => ['users', 'heart', 'gamepad-2', 'book-open', 'briefcase', 'music', 'camera', 'home', 'star'],
])

<div x-data="{ icon: @js($selected) }">
    <input type="hidden" name="{{ $name }}" x-model="icon">

    <label class="text-[10px] uppercase tracking-widest text-zinc-500 font-semibold block mb-2">Ícone</label>

    <div class="flex flex-wrap gap-1.5">
        @foreach ($icons as $i)
            <button
                type="button"
                @click="icon = '{{ $i }}'"
                :class="icon === '{{ $i }}'
                    ? 'border-white bg-white/15 text-white'
                    : 'border-white/10 text-zinc-600 hover:border-white/30 hover:text-zinc-300'"
                class="w-8 h-8 shrink-0 rounded-full border flex items-center justify-center transition"
                title="{{ $i }}"
            >
                <i data-lucide="{{ $i }}" class="w-3.5 h-3.5 pointer-events-none"></i>
            </button>
        @endforeach
    </div>
</div>