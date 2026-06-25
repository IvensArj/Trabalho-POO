<?php

namespace App\Services;

use App\Models\MindEvent;
use App\Models\MindPerson;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class MindCalendarService
{
    public function buildCalendarPayload(int $year, int $month, ?Carbon $selectedDate = null): array
    {
        $today = Carbon::today('America/Fortaleza');
        $selectedDate = $selectedDate?->copy() ?? $today->copy();

        $people = MindPerson::query()
            ->where('user_id', auth()->id())
            ->whereNotNull('birth_day')
            ->whereNotNull('birth_month')
            ->whereNotNull('birth_year')
            ->with('groups')
            ->orderBy('name')
            ->get();

        return [
            'birthdaySources' => $this->buildBirthdaySources($people),
            'initialYear' => $year,
            'initialMonth' => $month - 1,
            'initialSelected' => $selectedDate->format('Y-m-d'),
        ];
    }

    private function buildBirthdaySources(Collection $people): array
    {
        return $people->map(function (MindPerson $person) {
            return [
                'person_id' => $person->id,
                'person_name' => $person->name,
                'nickname' => $person->nickname,
                'birth_day' => (int) $person->birth_day,
                'birth_month' => (int) $person->birth_month,
                'birth_year' => (int) $person->birth_year,
                'photo_url'   => $person->photo_url,
                'groups' => $person->groups->map(fn ($group) => [
                    'id' => $group->id,
                    'name' => $group->name,
                ])->values()->all(),
            ];
        })->values()->all();
    }

    /**
     * Retorna todos os eventos customizados do usuário em um determinado mês,
     * indexados pela data no formato Y-m-d.
     *
     * Estrutura: [ '2026-06-25' => [ {type,label,note,icon}, ... ], ... ]
     */
    public function getCustomEvents(int $year, int $month): array
    {
        return MindEvent::query()
            ->where('user_id', auth()->id())
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->orderBy('date')
            ->get()
            ->groupBy(fn ($e) => $e->date->format('Y-m-d'))
            ->map(fn ($events) => $events->map(fn ($e) => [
                'id'    => $e->id,
                'type'  => $e->type ?? 'event',
                'label' => $e->label,
                'note'  => $e->note,
                'icon'  => $e->icon ?? 'calendar',
                'date'  => $e->date->format('Y-m-d'),
            ])->values()->all())
            ->all();
    }
}
