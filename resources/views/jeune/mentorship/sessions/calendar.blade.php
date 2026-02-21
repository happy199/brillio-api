@extends('layouts.jeune')

@section('title', 'Mon Calendrier')

@section('content')
<div class="container mx-auto px-4 py-8" x-data="jeuneCalendar()">
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Mon Calendrier</h1>
            <p class="text-gray-500 mt-1">Visualisez vos séances de mentorat.</p>
        </div>

        <a href="{{ route('jeune.sessions.index') }}"
            class="text-sm font-medium text-indigo-600 hover:text-indigo-800 flex items-center gap-1">
            Vue Liste <span aria-hidden="true">&rarr;</span>
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
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
                <button @click="prev()" class="p-2 hover:bg-gray-100 rounded-lg text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7">
                        </path>
                    </svg>
                </button>
                <button @click="today()"
                    class="px-3 py-1 text-sm font-medium hover:bg-gray-100 rounded-lg text-gray-600">
                    Aujourd'hui
                </button>
                <button @click="next()" class="p-2 hover:bg-gray-100 rounded-lg text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Month View -->
        <div x-show="view === 'month'">
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
                    <div class="min-h-[6rem] border border-gray-100 rounded-lg p-1 relative hover:border-indigo-200 transition bg-white flex flex-col"
                        :class="{'bg-indigo-50 border-indigo-200': isToday(date)}">
                        <span class="text-sm font-medium text-gray-700 ml-1"
                            :class="{'text-indigo-600 font-bold': isToday(date)}" x-text="date"></span>

                        <!-- Events Points -->
                        <div class="mt-1 flex flex-col gap-1 overflow-y-auto max-h-[60px]">
                            <!-- Sessions -->
                            <template x-for="session in getSessionsForDate(date)">
                                <a :href="'/espace-jeune/mentorat/seances/' + session.id"
                                    class="text-[10px] truncate px-1.5 py-0.5 rounded border block w-full"
                                    :class="session.status === 'confirmed' ? 'bg-green-100 text-green-700 border-green-200' : 'bg-blue-100 text-blue-700 border-blue-200'">
                                    <span x-text="session.time + ' ' + session.title"></span>
                                </a>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Week View -->
        <div x-show="view === 'week'" class="overflow-x-auto">
            <div class="min-w-[600px]">
                <div class="grid grid-cols-8 border-b border-gray-200">
                    <div class="p-2 text-xs text-gray-400 text-center border-r border-gray-100">Heure</div>
                    <template x-for="(day, index) in weekDays" :key="index">
                        <div class="p-2 text-center border-r border-gray-100"
                            :class="{'bg-indigo-50': isDateToday(day.date)}">
                            <div class="text-xs text-gray-500 uppercase" x-text="day.dayName"></div>
                            <div class="font-bold text-gray-900" x-text="day.dayNumber"></div>
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
                                <a :href="'/espace-jeune/mentorat/seances/' + session.id"
                                    class="absolute w-[90%] left-[5%] text-[10px] p-1 rounded border z-10 overflow-hidden hover:z-20 shadow-sm transition"
                                    :class="session.status === 'confirmed' ? 'bg-green-100 text-green-700 border-green-200' : 'bg-blue-100 text-blue-700 border-blue-200'"
                                    :style="getStyleForSlot(session.time, session.endTime)">
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
        <div x-show="view === 'day'" class="overflow-y-auto h-[600px] relative border border-gray-100 rounded-lg">
            <div class="grid grid-cols-1 divide-y divide-gray-100">
                <template x-for="hour in hours">
                    <div class="h-20 flex group hover:bg-gray-50 transition relative">
                        <div class="w-16 text-xs text-gray-400 text-right pr-4 py-2 border-r border-gray-100 sticky left-0 bg-white group-hover:bg-gray-50 z-10"
                            x-text="hour + ':00'"></div>
                        <div class="flex-1 relative p-1">
                            <!-- Grid lines -->
                        </div>
                    </div>
                </template>

                <div class="absolute top-0 left-16 right-0 bottom-0 pointer-events-none">
                    <!-- Sessions -->
                    <template x-for="session in getSessionsForFullDate(currentDate)">
                        <a :href="'/espace-jeune/mentorat/seances/' + session.id"
                            class="absolute left-10 right-10 p-2 rounded border pointer-events-auto shadow hover:shadow-md transition"
                            :class="session.status === 'confirmed' ? 'bg-green-100 text-green-700 border-green-200' : 'bg-blue-100 text-blue-700 border-blue-200'"
                            :style="getStyleForDayView(session.time, session.endTime)">
                            <div class="flex justify-between items-start">
                                <div>
                                    <div class="font-bold" x-text="session.title"></div>
                                    <div class="text-xs" x-text="'Avec ' + session.mentor_name"></div>
                                </div>
                                <div class="text-xs font-mono" x-text="session.time + ' - ' + session.endTime"></div>
                            </div>
                        </a>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    @php
    $sessionsJson = $sessions -> map(function ($s) {
        return [
            'id' => $s -> id,
            'title' => $s -> title,
            'scheduled_at' => $s -> scheduled_at -> toIso8601String(),
            'time' => $s -> scheduled_at -> format('H:i'),
            'endTime' => $s -> scheduled_at -> copy() -> addMinutes($s -> duration_minutes) -> format('H:i'),
            'status' => $s -> status,
            'mentor_name' => $s -> mentor -> name,
        ];
    });
    @endphp

    function jeuneCalendar() {
        return {
            view: 'month',
            currentDate: new Date(),
            monthNames: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
            dayNames: ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'],
            shortDayNames: ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'],
            blankDays: [],
            no_of_days: [],
            weekDays: [],
            hours: Array.from({ length: 24 }, (_, i) => i),

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
                let daysInMonth = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth() + 1, 0).getDate();
                let dayOfWeek = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth(), 1).getDay();

                let blankDaysArray = [];
                for (var i = 1; i <= dayOfWeek; i++) {
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
                const rowHeight = 48;
                const startH = parseInt(startTime.split(':')[0]);
                const startM = parseInt(startTime.split(':')[1]);
                const endH = parseInt(endTime.split(':')[0]);
                const endM = parseInt(endTime.split(':')[1]);

                const top = (startH * rowHeight) + ((startM / 60) * rowHeight);
                const durationMins = ((endH * 60) + endM) - ((startH * 60) + startM);
                const height = (durationMins / 60) * rowHeight;

                return `top: ${top}px; height: ${height}px;`;
            },

            getStyleForDayView(startTime, endTime) {
                const rowHeight = 80;
                const startH = parseInt(startTime.split(':')[0]);
                const startM = parseInt(startTime.split(':')[1]);
                const endH = parseInt(endTime.split(':')[0]);
                const endM = parseInt(endTime.split(':')[1]);

                const top = (startH * rowHeight) + ((startM / 60) * rowHeight);
                const durationMins = ((endH * 60) + endM) - ((startH * 60) + startM);
                const height = (durationMins / 60) * rowHeight;

                return `top: ${top}px; height: ${height}px;`;
            }
        }
    }
</script>
@endsection