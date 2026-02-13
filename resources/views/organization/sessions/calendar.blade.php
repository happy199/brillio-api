@extends('layouts.organization')

@section('title', 'Calendrier des Séances')

@section('content')
<div class="container mx-auto px-4 py-8" x-data="organizationCalendar()">
    <div class="mb-8 flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <a href="{{ route('organization.sessions.index') }}"
                class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700">
                <svg class="mr-1 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Retour à la liste
            </a>
            <h1 class="text-2xl font-bold text-gray-900">Calendrier des Séances</h1>
        </div>
        <p class="text-gray-500 text-sm">Visualisez l'emploi du temps de tous vos jeunes.</p>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
        <!-- Navigation & View Select -->
        <div class="flex flex-col sm:flex-row items-center justify-between mb-6 gap-4">
            <div class="flex items-center gap-4">
                <h2 class="text-lg font-bold text-gray-900 capitalize" x-text="calendarTitle"></h2>
                <div class="flex bg-gray-100 rounded-lg p-1">
                    <button @click="changeView('month')" class="px-3 py-1 text-xs font-medium rounded-md transition"
                        :class="view === 'month' ? 'bg-white shadow text-gray-900' : 'text-gray-500 hover:text-gray-900'">
                        Mois
                    </button>
                    <button @click="changeView('week')" class="px-3 py-1 text-xs font-medium rounded-md transition"
                        :class="view === 'week' ? 'bg-white shadow text-gray-900' : 'text-gray-500 hover:text-gray-900'">
                        Semaine
                    </button>
                    <button @click="changeView('day')" class="px-3 py-1 text-xs font-medium rounded-md transition"
                        :class="view === 'day' ? 'bg-white shadow text-gray-900' : 'text-gray-500 hover:text-gray-900'">
                        Jour
                    </button>
                </div>
            </div>
            <div class="flex gap-2">
                <button @click="prev()" class="p-2 hover:bg-gray-100 rounded-lg text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7">
                        </path>
                    </svg>
                </button>
                <button @click="today()"
                    class="px-3 py-1 text-sm font-medium hover:bg-gray-100 rounded-lg text-gray-600 transition-colors">
                    Aujourd'hui
                </button>
                <button @click="next()" class="p-2 hover:bg-gray-100 rounded-lg text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            </div>
        </div>

        <div x-show="view === 'month'" x-cloak>
            <div class="grid grid-cols-7 mb-2">
                <template x-for="day in ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam']">
                    <div class="text-center text-xs font-medium text-gray-400 uppercase py-2" x-text="day"></div>
                </template>
            </div>
            <div class="grid grid-cols-7 gap-1">
                <template x-for="blank in blankDays">
                    <div class="h-24 bg-gray-50/50 rounded-lg"></div>
                </template>
                <template x-for="date in no_of_days">
                    <div class="min-h-[6rem] border border-gray-100 rounded-lg p-1 relative hover:border-organization-200 transition bg-white flex flex-col"
                        :class="{'bg-organization-50 border-organization-200': isToday(date)}">
                        <span class="text-sm font-medium text-gray-700 ml-1"
                            :class="{'text-organization-600 font-bold': isToday(date)}" x-text="date"></span>

                        <!-- Events -->
                        <div class="mt-1 flex flex-col gap-1 overflow-y-auto max-h-[60px]">
                            <template x-for="session in getSessionsForDate(date)">
                                <a :href="'/organization/sessions/' + session.id"
                                    class="text-[10px] truncate px-1.5 py-0.5 rounded border block w-full transition-colors"
                                    :style="`background-color: ${session.bgColor}; color: ${session.textColor}; border-color: ${session.borderColor}`">
                                    <span x-text="session.time + ' ' + session.title"></span>
                                </a>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Week View -->
        <div x-show="view === 'week'" x-cloak class="overflow-x-auto">
            <div class="min-w-[600px]">
                <div class="grid grid-cols-8 border-b border-gray-100">
                    <div class="p-2 text-xs text-gray-400 text-center border-r border-gray-100">Heure</div>
                    <template x-for="(day, index) in weekDays" :key="index">
                        <div class="p-2 text-center border-r border-gray-100"
                            :class="{'bg-organization-50': isDateToday(day.date)}">
                            <div class="text-xs text-gray-500 uppercase" x-text="day.dayName"></div>
                            <div class="font-bold text-gray-900"
                                :class="{'text-organization-600': isDateToday(day.date)}" x-text="day.dayNumber"></div>
                        </div>
                    </template>
                </div>
                <div class="relative grid grid-cols-8" style="height: 600px; overflow-y: auto;">
                    <!-- Time Slots -->
                    <div class="col-span-1 border-r border-gray-100 bg-gray-50">
                        <template x-for="hour in hours">
                            <div class="h-12 border-b border-gray-100 text-xs text-gray-400 text-center pt-1"
                                x-text="hour + ':00'"></div>
                        </template>
                    </div>
                    <!-- Days Columns -->
                    <template x-for="(day, dayIndex) in weekDays" :key="dayIndex">
                        <div class="col-span-1 border-r border-gray-100 relative h-full">
                            <!-- Grid Lines -->
                            <template x-for="_ in hours">
                                <div class="h-12 border-b border-gray-100"></div>
                            </template>

                            <!-- Sessions Overlay -->
                            <template x-for="session in getSessionsForFullDate(day.date)">
                                <a :href="'/organization/sessions/' + session.id"
                                    class="absolute w-[90%] left-[5%] text-[10px] p-1 rounded border z-10 overflow-hidden hover:z-20 shadow-sm transition"
                                    :style="getStyleForSlot(session.time, session.endTime) + `; background-color: ${session.bgColor}; color: ${session.textColor}; border-color: ${session.borderColor}`">
                                    <span class="font-bold block" x-text="session.time"></span>
                                    <span x-text="session.title"></span>
                                </a>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Day View -->
        <div x-show="view === 'day'" x-cloak
            class="overflow-y-auto h-[600px] relative border border-gray-100 rounded-lg">
            <div class="grid grid-cols-1 divide-y divide-gray-100">
                <template x-for="hour in hours">
                    <div class="h-20 flex group hover:bg-gray-50/50 transition-colors">
                        <div class="w-16 text-[10px] font-bold text-gray-400 text-right pr-4 py-2 border-r border-gray-100"
                            x-text="hour + ':00'"></div>
                        <div class="flex-1"></div>
                    </div>
                </template>

                <div class="absolute top-0 left-16 right-0 bottom-0 pointer-events-none">
                    <!-- Sessions -->
                    <template x-for="session in getSessionsForFullDate(currentDate)">
                        <a :href="'/organization/sessions/' + session.id"
                            class="absolute left-4 right-4 p-3 rounded-lg border pointer-events-auto shadow-sm hover:shadow-md transition-all flex flex-col justify-center"
                            :style="getStyleForDayView(session.time, session.endTime) + `; background-color: ${session.bgColor}; color: ${session.textColor}; border-color: ${session.borderColor}`">
                            <div class="text-[10px] font-bold opacity-70 mb-1"
                                x-text="session.time + ' - ' + session.endTime"></div>
                            <div class="text-sm font-bold leading-tight truncate" x-text="session.title"></div>
                            <div class="text-[10px] opacity-80" x-text="'Mentor: ' + session.mentor_name"></div>
                        </a>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    [x-cloak] {
        display: none !important;
    }

    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #f1f5f9;
        border-radius: 10px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #e2e8f0;
    }
</style>

<script>
    @php
    $sessionsJson = $sessions -> map(function ($s) {
        $statusColors = [
            'confirmed' => ['bg' => '#f0fdf4', 'text' => '#166534', 'border' => '#dcfce7'],
            'completed' => ['bg' => '#eef2ff', 'text' => '#3730a3', 'border' => '#e0e7ff'],
            'cancelled' => ['bg' => '#fef2f2', 'text' => '#991b1b', 'border' => '#fee2e2'],
            'pending_payment' => ['bg' => '#fffbeb', 'text' => '#92400e', 'border' => '#fef3c7'],
            'proposed' => ['bg' => '#f9fafb', 'text' => '#374151', 'border' => '#f3f4f6'],
        ];
        $statusLabels = [
            'confirmed' => 'Confirmée',
            'completed' => 'Terminée',
            'cancelled' => 'Annulée',
            'pending_payment' => 'En attente',
            'proposed' => 'Proposée',
        ];

        $colors = $statusColors[$s -> status] ?? $statusColors['proposed'];
        $menteeNames = $s -> mentees -> pluck('name') -> implode(', ');

        return [
            'id' => $s -> id,
            'title' => $s -> title. " ($menteeNames)",
            'scheduled_at' => $s -> scheduled_at -> toIso8601String(),
            'time' => $s -> scheduled_at -> format('H:i'),
            'endTime' => $s -> scheduled_at -> copy() -> addMinutes($s -> duration_minutes) -> format('H:i'),
            'status' => $s -> status,
            'status_label' => $statusLabels[$s -> status] ?? $s -> status,
            'mentor_name' => $s -> mentor -> name,
            'bgColor' => $colors['bg'],
            'textColor' => $colors['text'],
            'borderColor' => $colors['border'],
        ];
    });
    @endphp

    function organizationCalendar() {
        return {
            view: 'month',
            currentDate: new Date(),
            monthNames: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
            dayNames: ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'],
            shortDayNames: ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'],
            blankDays: [],
            no_of_days: [],
            weekDays: [],
            hours: Array.from({ length: 15 }, (_, i) => i + 8), // 8:00 to 22:00

            sessions: @json($sessionsJson),

            init() {
                this.renderCalendar();
            },

            calendarTitle() {
                if (this.view === 'month') {
                    return this.monthNames[this.currentDate.getMonth()] + ' ' + this.currentDate.getFullYear();
                } else if (this.view === 'week') {
                    const start = this.getStartOfWeek(this.currentDate);
                    const end = new Date(start);
                    end.setDate(end.getDate() + 6);

                    if (start.getMonth() === end.getMonth()) {
                        return start.getDate() + ' - ' + end.getDate() + ' ' + this.monthNames[start.getMonth()] + ' ' + start.getFullYear();
                    }
                    return start.getDate() + ' ' + this.monthNames[start.getMonth()] + ' - ' + end.getDate() + ' ' + this.monthNames[end.getMonth()] + ' ' + end.getFullYear();
                } else {
                    return this.dayNames[this.currentDate.getDay()] + ' ' + this.currentDate.getDate() + ' ' + this.monthNames[this.currentDate.getMonth()] + ' ' + this.currentDate.getFullYear();
                }
            },

            renderCalendar() {
                if (this.view === 'month') {
                    this.getNoOfDays();
                } else if (this.view === 'week') {
                    this.getWeekDays();
                }
            },

            changeView(newView) {
                this.view = newView;
                this.renderCalendar();
            },

            prev() {
                if (this.view === 'month') {
                    this.currentDate = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth() - 1, 1);
                } else if (this.view === 'week') {
                    this.currentDate.setDate(this.currentDate.getDate() - 7);
                    this.currentDate = new Date(this.currentDate);
                } else {
                    this.currentDate.setDate(this.currentDate.getDate() - 1);
                    this.currentDate = new Date(this.currentDate);
                }
                this.renderCalendar();
            },

            next() {
                if (this.view === 'month') {
                    this.currentDate = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth() + 1, 1);
                } else if (this.view === 'week') {
                    this.currentDate.setDate(this.currentDate.getDate() + 7);
                    this.currentDate = new Date(this.currentDate);
                } else {
                    this.currentDate.setDate(this.currentDate.getDate() + 1);
                    this.currentDate = new Date(this.currentDate);
                }
                this.renderCalendar();
            },

            today() {
                this.currentDate = new Date();
                this.renderCalendar();
            },

            isToday(date) {
                const today = new Date();
                const d = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth(), date);
                return today.toDateString() === d.toDateString();
            },

            isDateToday(dateObj) {
                const today = new Date();
                return today.toDateString() === dateObj.toDateString();
            },

            getSessionsForDate(date) {
                const targetDate = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth(), date).toDateString();
                return this.sessions.filter(session => {
                    const sessionDate = new Date(session.scheduled_at).toDateString();
                    return sessionDate === targetDate;
                });
            },

            getSessionsForFullDate(dateObj) {
                const targetDate = dateObj.toDateString();
                return this.sessions.filter(session => {
                    const sessionDate = new Date(session.scheduled_at).toDateString();
                    return sessionDate === targetDate;
                });
            },

            getNoOfDays() {
                let firstDayOfMonth = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth(), 1).getDay();
                let daysInMonth = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth() + 1, 0).getDate();

                let blankDaysArray = [];
                for (var i = 0; i < firstDayOfMonth; i++) {
                    blankDaysArray.push(i);
                }

                let daysArray = [];
                for (var i = 1; i <= daysInMonth; i++) {
                    daysArray.push(i);
                }

                this.blankDays = blankDaysArray;
                this.no_of_days = daysArray;
            },

            getStartOfWeek(date) {
                const d = new Date(date);
                const day = d.getDay();
                // Ajuster pour que la semaine commence le lundi si nécessaire, mais ici on garde dimanche comme le jeune
                const diff = d.getDate() - day;
                return new Date(d.setDate(diff));
            },

            getWeekDays() {
                const start = this.getStartOfWeek(this.currentDate);
                let days = [];
                for (let i = 0; i < 7; i++) {
                    let d = new Date(start);
                    d.setDate(d.getDate() + i);
                    days.push({
                        dayName: this.shortDayNames[i],
                        dayNumber: d.getDate(),
                        date: d
                    });
                }
                this.weekDays = days;
            },

            getStyleForSlot(startTime, endTime) {
                const rowHeight = 48; // h-12
                const baseHour = 8;
                const startH = parseInt(startTime.split(':')[0]);
                const startM = parseInt(startTime.split(':')[1]);
                const endH = parseInt(endTime.split(':')[0]);
                const endM = parseInt(endTime.split(':')[1]);

                const top = ((startH - baseHour) * rowHeight) + ((startM / 60) * rowHeight);
                const durationMins = ((endH * 60) + endM) - ((startH * 60) + startM);
                const height = (durationMins / 60) * rowHeight;

                return `top: ${top}px; height: ${height}px;`;
            },

            getStyleForDayView(startTime, endTime) {
                const rowHeight = 80; // h-20
                const baseHour = 8;
                const startH = parseInt(startTime.split(':')[0]);
                const startM = parseInt(startTime.split(':')[1]);
                const endH = parseInt(endTime.split(':')[0]);
                const endM = parseInt(endTime.split(':')[1]);

                const top = ((startH - baseHour) * rowHeight) + ((startM / 60) * rowHeight);
                const durationMins = ((endH * 60) + endM) - ((startH * 60) + startM);
                const height = (durationMins / 60) * rowHeight;

                return `top: ${top}px; height: ${height}px;`;
            }
        }
    }
</script>
@endsection