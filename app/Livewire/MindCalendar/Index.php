<?php

namespace App\Livewire\MindCalendar;

use App\Models\MindEvent;
use App\Models\MindGroup;
use App\Services\MindCalendarService;
use Carbon\Carbon;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class Index extends Component
{
    public array $birthdaySources = [];
    public string $initialSelected;
    public array $customEvents = [];
    public int $initialYear;
    public int $initialMonth;
    public array $allGroups = [];

    public bool $isCreating = false;
    public bool $isUpdating = false;
    public bool $isDeleting = false;

    #[Locked]
    public int $userId;

    public function mount(MindCalendarService $calendarService): void
    {
        $this->userId = (int) auth()->id();

        $today = Carbon::today('America/Fortaleza');

        $payload = $calendarService->buildCalendarPayload(
            year: $today->year,
            month: $today->month,
            selectedDate: $today,
        );

        $this->birthdaySources = $payload['birthdaySources'];
        $this->initialYear = $payload['initialYear'];
        $this->initialMonth = $payload['initialMonth'];
        $this->initialSelected = $payload['initialSelected'];

        $this->reloadCustomEvents($this->initialYear, $this->initialMonth + 1);

        $this->allGroups = MindGroup::where('user_id', $this->userId)
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    public function render()
    {
        return view('livewire.mindcalendar.index', [
            'birthdaySources' => $this->birthdaySources,
            'customEvents'    => $this->customEvents,
            'initialYear'     => $this->initialYear,
            'initialMonth'    => $this->initialMonth,
            'initialSelected' => $this->initialSelected,
            'allGroups'       => $this->allGroups,
        ]);
    }

    #[On('mind-calendar:month-changed')]
    public function reloadCustomEvents(int $year, int $month): void
    {
        $year  = max(1970, min(2100, $year));
        $month = max(1, min(12, $month));

        $service = app(MindCalendarService::class);
        $this->customEvents = $service->getCustomEvents($year, $month);

        $this->initialYear  = $year;
        $this->initialMonth = $month - 1;
    }

    public function createEvent(string $date, string $label, ?string $note, string $icon): void
    {
        if ($this->isCreating) {
            return;
        }

        $this->isCreating = true;

        try {
            $note = $note !== null ? trim($note) : null;
            if ($note === '') {
                $note = null;
            }

            Validator::make([
                'date'  => $date,
                'label' => $label,
                'note'  => $note,
                'icon'  => $icon,
            ], [
                'date'  => 'required|date|after_or_equal:today',
                'label' => 'required|string|max:255',
                'note'  => 'nullable|string|max:500',
                'icon'  => 'required|string|max:50',
            ], [
                'date.after_or_equal' => 'A data não pode ser no passado.',
                'label.required'      => 'Informe um nome para o evento.',
            ])->validate();

            // Idempotência
            $recentDuplicate = MindEvent::where('user_id', $this->userId)
                ->where('date', $date)
                ->where('label', $label)
                ->where('created_at', '>=', now()->subSeconds(5))
                ->first();

            if ($recentDuplicate) {
                $this->dispatch('mind-calendar:event-created', date: $date, payload: $this->payloadForEvent($recentDuplicate));
                return;
            }

            $event = DB::transaction(function () use ($date, $label, $note, $icon) {
                return MindEvent::create([
                    'user_id' => $this->userId,
                    'date'    => $date,
                    'type'    => 'event',
                    'label'   => $label,
                    'note'    => $note,
                    'icon'    => $icon ?: 'calendar',
                ]);
            });

            $this->dispatch('mind-calendar:event-created', date: $date, payload: $this->payloadForEvent($event));
        } finally {
            $this->isCreating = false;
        }
    }

    public function updateEvent(int $eventId, string $oldDate, string $date, string $label, ?string $note, string $icon): void
    {
        if ($this->isUpdating) {
            return;
        }

        $this->isUpdating = true;

        try {
            $note = $note !== null ? trim($note) : null;
            if ($note === '') {
                $note = null;
            }

            Validator::make([
                'date'  => $date,
                'label' => $label,
                'note'  => $note,
                'icon'  => $icon,
            ], [
                'date'  => 'required|date|after_or_equal:today',
                'label' => 'required|string|max:255',
                'note'  => 'nullable|string|max:500',
                'icon'  => 'required|string|max:50',
            ], [
                'date.after_or_equal' => 'A data não pode ser no passado.',
                'label.required'      => 'Informe um nome para o evento.',
            ])->validate();

            $event = MindEvent::where('user_id', $this->userId)->findOrFail($eventId);
            $event->update([
                'date'  => $date,
                'label' => $label,
                'note'  => $note,
                'icon'  => $icon,
            ]);

            $this->dispatch('mind-calendar:event-updated', oldDate: $oldDate, payload: $this->payloadForEvent($event));
        } finally {
            $this->isUpdating = false;
        }
    }

    public function deleteEvent(int $eventId): void
    {
        if ($this->isDeleting) {
            return;
        }

        $this->isDeleting = true;

        try {
            $event = MindEvent::where('user_id', $this->userId)->findOrFail($eventId);
            $date = $event->date->format('Y-m-d');
            $event->delete();

            $this->dispatch('mind-calendar:event-deleted', eventId: $eventId, date: $date);
        } finally {
            $this->isDeleting = false;
        }
    }

    private function payloadForEvent(MindEvent $event): array
    {
        return [
            'id'    => $event->id,
            'type'  => 'event',
            'label' => $event->label,
            'note'  => $event->note,
            'icon'  => $event->icon ?? 'calendar',
            'date'  => $event->date instanceof \DateTimeInterface
                ? $event->date->format('Y-m-d')
                : (string) $event->date,
        ];
    }
}