<div
    x-data="{
        // -------- Estado Alpine --------
        customEvents: {},
        year: {{ $initialYear }},
        month: {{ $initialMonth }},
        selectedDate: '{{ $initialSelected }}',
        birthdaySources: @js($birthdaySources),

        currentYearEvents: {},

        // Form de criação
        newEventDate: '',
        newEventLabel: '',
        newEventNote: '',
        newEventIcon: 'calendar',
        showCreateModal: false,

        // Form de edição
        showEditModal: false,
        editingEventId: null,
        editingEventOldDate: '',
        editEventDate: '',
        editEventLabel: '',
        editEventNote: '',
        editEventIcon: 'calendar',
        savingEdit: false,

        saving: false,

        MONTHS: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
            'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
        WEEKDAYS: ['Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira',
            'Quinta-feira', 'Sexta-feira', 'Sábado'],

        // -------- Helpers de data --------
        formatDate(y, m, d) {
            return `${y}-${String(m).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
        },
        localDateString(date) {
            return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
        },
        get minDate() {
            const d = new Date();
            d.setHours(0, 0, 0, 0);
            return this.localDateString(d);
        },
        addDays(date, days) {
            const d = new Date(date + 'T00:00:00');
            d.setDate(d.getDate() + days);
            return this.localDateString(d);
        },

        // -------- Inicialização --------
        init() {
            this.saving = false;
            this.customEvents = @js($customEvents);
            this.rebuildEvents();

            this.$watch('customEvents', () => {
                this.rebuildEvents();
            });

            this.$watch('year', () => this.notifyMonthChanged());
            this.$watch('month', () => this.notifyMonthChanged());

            this.$nextTick(() => this.createLucideIcons());

            this.$watch('showCreateModal', () => {
                this.$nextTick(() => this.createLucideIcons());
            });

            this.$watch('showEditModal', () => {
                this.$nextTick(() => this.createLucideIcons());
            });

            // Listeners para atualização otimista da UI
            window.addEventListener('mind-calendar:event-created', (e) => {
                const { date, payload } = e.detail;
                if (!this.customEvents[date]) this.customEvents[date] = [];
                this.customEvents[date].push(payload);
                this.showCreateModal = false;
                this.resetForm();
            });

            window.addEventListener('mind-calendar:event-updated', (e) => {
                const { oldDate, payload } = e.detail;
                // Remove da data antiga
                if (this.customEvents[oldDate]) {
                    this.customEvents[oldDate] = this.customEvents[oldDate].filter(ev => ev.id !== payload.id);
                    if (this.customEvents[oldDate].length === 0) delete this.customEvents[oldDate];
                }
                // Adiciona na nova data
                if (!this.customEvents[payload.date]) this.customEvents[payload.date] = [];
                this.customEvents[payload.date].push(payload);
                this.showEditModal = false;
            });

            window.addEventListener('mind-calendar:event-deleted', (e) => {
                const { eventId, date } = e.detail;
                if (this.customEvents[date]) {
                    this.customEvents[date] = this.customEvents[date].filter(ev => ev.id !== eventId);
                    if (this.customEvents[date].length === 0) delete this.customEvents[date];
                }
                this.showEditModal = false;
            });
        },

        notifyMonthChanged() {
            const monthOneBased = this.month + 1;
            if (window.Livewire && typeof this.$wire !== 'undefined') {
                this.$wire.call('reloadCustomEvents', this.year, monthOneBased);
            }
        },

        rebuildEvents() {
            const events = {};

            // 1) Aniversários
            for (const person of this.birthdaySources) {
                const day = Number(person.birth_day);
                const month = Number(person.birth_month);
                const birthYear = Number(person.birth_year);
                if (!day || !month || !birthYear) continue;

                const dateKey = this.formatDate(this.year, month, day);
                const age = this.year - birthYear;
                const label = person.nickname
                    ? `Aniversário de ${person.person_name} (@${person.nickname})`
                    : `Aniversário de ${person.person_name}`;

                if (!events[dateKey]) events[dateKey] = [];
                events[dateKey].push({
                    type: 'birthday',
                    person_id: person.person_id,
                    label,
                    note: age > 0 ? `${age} anos` : null,
                    age: age > 0 ? age : null,
                    photo_url: person.photo_url,
                    date: dateKey, // necessário para edição
                });
            }

            // 2) Eventos customizados
            if (this.customEvents && typeof this.customEvents === 'object') {
                for (const [date, dayEvents] of Object.entries(this.customEvents)) {
                    if (!Array.isArray(dayEvents)) continue;
                    if (!events[date]) events[date] = [];
                    // Garante que cada evento customizado tenha a propriedade 'date'
                    const enriched = dayEvents.map(e => ({ ...e, date: e.date || date }));
                    events[date].push(...enriched);
                }
            }

            this.currentYearEvents = events;
        },

        createLucideIcons() {
            if (typeof window.createLucideIcons === 'function') {
                window.createLucideIcons();
            }
        },

        resetForm() {
            this.newEventLabel = '';
            this.newEventNote = '';
            this.newEventIcon = 'calendar';
            this.newEventDate = this.selectedDate;
        },

        // -------- Getters --------
        get cells() {
            const firstDay = new Date(this.year, this.month, 1).getDay();
            const daysInMonth = new Date(this.year, this.month + 1, 0).getDate();
            const prevMonthDays = new Date(this.year, this.month, 0).getDate();
            const todayStr = this.localDateString(new Date());
            const cells = [];

            for (let i = 0; i < 42; i++) {
                let day, dateStr, currentMonth;

                if (i < firstDay) {
                    day = prevMonthDays - firstDay + 1 + i;
                    const pm = this.month === 0 ? 12 : this.month;
                    const py = this.month === 0 ? this.year - 1 : this.year;
                    dateStr = this.formatDate(py, pm, day);
                    currentMonth = false;
                } else if (i >= firstDay + daysInMonth) {
                    day = i - firstDay - daysInMonth + 1;
                    const nm = this.month === 11 ? 1 : this.month + 2;
                    const ny = this.month === 11 ? this.year + 1 : this.year;
                    dateStr = this.formatDate(ny, nm, day);
                    currentMonth = false;
                } else {
                    day = i - firstDay + 1;
                    dateStr = this.formatDate(this.year, this.month + 1, day);
                    currentMonth = true;
                }

                const events = this.currentYearEvents[dateStr] || [];
                cells.push({
                    date: dateStr,
                    day,
                    currentMonth,
                    today: dateStr === todayStr,
                    events,
                });
            }
            return cells;
        },

        get stats() {
            const allEvents = Object.values(this.currentYearEvents).flat();
            return {
                memories:   allEvents.filter(e => e.type === 'memory').length,
                birthdays: allEvents.filter(e => e.type === 'birthday').length,
                events:    allEvents.filter(e => e.type === 'event').length,
            };
        },

        get upcoming() {
            const today = this.localDateString(new Date());
            return this.cells
                .filter(c => c.currentMonth && c.date >= today && c.events.length)
                .slice(0, 4)
                .map(c => {
                    const ev = c.events[0];
                    return {
                        date: c.date,
                        day: new Date(c.date + 'T00:00:00').getDate(),
                        label: ev.label,
                        subtitle: ev.age
                            ? `${ev.age} anos`
                            : (ev.type === 'event' ? 'Evento' : 'Aniversário'),
                    };
                });
        },

        get selectedEvents() {
            return this.currentYearEvents[this.selectedDate] || [];
        },
        get selectedDay() {
            const [, , d] = this.selectedDate.split('-');
            return Number(d);
        },
        get selectedWeekday() {
            const d = new Date(this.selectedDate + 'T00:00:00');
            return this.WEEKDAYS[d.getDay()];
        },
        get selectedMonthYear() {
            const [y, m] = this.selectedDate.split('-');
            return `${this.MONTHS[Number(m) - 1]} ${y}`;
        },
        get monthLabel() {
            return this.MONTHS[this.month];
        },

        // -------- Ações --------
        changeMonth(delta) {
            this.month += delta;
            if (this.month < 0) {
                this.month = 11;
                this.year--;
            } else if (this.month > 11) {
                this.month = 0;
                this.year++;
            }
            this.notifyMonthChanged();
        },
        selectDay(dateStr) {
            this.selectedDate = dateStr;
            const [y, m] = dateStr.split('-');
            this.year = Number(y);
            this.month = Number(m) - 1;
        },
        goToToday() {
            const today = new Date();
            this.year = today.getFullYear();
            this.month = today.getMonth();
            this.selectedDate = this.localDateString(today);
        },

        openCreateModal() {
            this.newEventDate = this.selectedDate < this.minDate
                ? this.minDate
                : this.selectedDate;
            this.newEventLabel = '';
            this.newEventNote = '';
            this.newEventIcon = 'calendar';
            this.showCreateModal = true;
            this.createLucideIcons();
        },

        openEvent(event) {
            if (event.type === 'birthday') {
                window.location.href = '{{ route('mind-social.index') }}';
            } else {
                this.openEditModal(event);
            }
        },

        openEditModal(event) {
            // Aniversários não são editáveis, redireciona para mind-social
            if (event.type === 'birthday') {
                window.location.href = '{{ route('mind-social.index') }}';
                return;
            }
            this.editingEventId = event.id;
            this.editingEventOldDate = event.date;
            this.editEventDate = event.date;
            this.editEventLabel = event.label;
            this.editEventNote = event.note || '';
            this.editEventIcon = event.icon || 'calendar';
            this.showEditModal = true;
            this.$nextTick(() => this.createLucideIcons());
        },

        saveEditEvent() {
            if (!this.editEventLabel || !this.editEventLabel.trim()) return;
            if (!this.$wire || this.savingEdit || this.$wire.isUpdating) return;
            this.savingEdit = true;
            this.$wire.updateEvent(
                this.editingEventId,
                this.editingEventOldDate,
                this.editEventDate,
                this.editEventLabel.trim(),
                this.editEventNote,
                this.editEventIcon
            ).finally(() => {
                this.savingEdit = false;
            });
        },

        deleteEventAction() {
            if (!this.$wire) return;
            if (confirm('Excluir este evento permanentemente?')) {
                this.$wire.deleteEvent(this.editingEventId);
            }
        },

        saveEvent() {
            if (!this.newEventLabel || !this.newEventLabel.trim()) {
                alert('Por favor, informe um nome para o evento.');
                return;
            }
            if (!this.$wire || this.saving || this.$wire.isCreating) return;
            this.saving = true;
            this.$wire.createEvent(
                this.newEventDate,
                this.newEventLabel.trim(),
                this.newEventNote,
                this.newEventIcon
            )
            .then(() => {
                // sucesso – o evento criado é tratado pelo listener 'mind-calendar:event-created'
            })
            .catch(error => {
                // Exibe uma mensagem genérica ou os erros de validação retornados
                if (error.response && error.response.data && error.response.data.errors) {
                    const messages = Object.values(error.response.data.errors).flat().join('\n');
                    alert('Erro de validação:\n' + messages);
                } else {
                    alert('Não foi possível criar o evento. Tente novamente.');
                }
            })
            .finally(() => {
                this.saving = false;
            });
        },
    }"
    class="min-h-screen bg-black text-white"
>
    @vite(['resources/css/mindcalendar.css'])

    @include('mindcalendar.partials.header')

    <div class="max-w-[1440px] mx-auto px-5 lg:px-10 py-4 relative z-10">
        <div class="grid lg:grid-cols-[1fr_320px] xl:grid-cols-[1fr_360px] gap-2 items-start">
            <div class="relative">
                @include('mindcalendar.partials.calendar')
            </div>

            <div class="space-y-2 lg:sticky lg:top-6">
                @include('mindcalendar.partials.day-panel')
                @include('mindcalendar.partials.upcoming')
            </div>
        </div>
    </div>

    @include('mindcalendar.partials.create-event-modal')
    @include('mindcalendar.partials.edit-event-modal')
</div>