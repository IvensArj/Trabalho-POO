@props([
    'people'
])

@php
    $peopleData = $people->map(function ($person) {
        return [
            'id'          => $person->id,
            'name'        => $person->name,
            'nickname'    => $person->nickname,
            'notes'       => $person->notes,
            'birth_day'   => $person->birth_day,
            'birth_month' => $person->birth_month,
            'birth_year'  => $person->birth_year,
            'photo'       => $person->photo ? asset('storage/' . $person->photo) : null,
            'groups'      => $person->groups->map(fn($g) => ['id' => $g->id, 'name' => $g->name])->toArray(),
        ];
    })->values()->toArray();
@endphp

<script>
    window.people = @json($peopleData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
</script>

<style>
.halftone-bg {
    position: absolute;
    inset: 0;

    background:
        radial-gradient(
            circle at center,
            rgba(255,255,255,.025) 0%,
            rgba(255,255,255,.01) 30%,
            transparent 70%
        );

    animation: pulseGlow 8s ease-in-out infinite alternate;
}

@keyframes pulseGlow {
    from {
        opacity: .5;
        transform: scale(1);
    }
    to {
        opacity: 1;
        transform: scale(1.05);
    }
}
</style>

<div class="relative w-full h-full overflow-hidden rounded-2xl">
    <canvas
        id="fisheye-canvas"
        class="absolute inset-0 w-full h-full block"
    ></canvas>
</div>

@vite('resources/js/fisheye.js')