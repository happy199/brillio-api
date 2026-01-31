@extends('layouts.mentor')

@section('title', 'Mon Calendrier')

@section('content')
    <div class="container mx-auto px-4 py-8" x-data="mentorCalendar()">
        <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Mon Calendrier & Disponibilités</h1>
                <p class="text-gray-500 mt-1">Gérez vos créneaux et visualisez vos séances.</p>
            </div>
            <div class="mt-4 md:mt-0 flex gap-4">
                <a href="{{ route('mentor.mentorship.sessions.create') }}"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-lg font-medium transition flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Planifier une séance
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Calendar Visualization (Left - 2/3) -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <!-- Navigation & View Select -->
                    <div class="flex flex-col sm:flex-row items-center justify-between mb-6 gap-4">
                        <div class="flex items-center gap-4">
                            <h2 class="text-lg font-bold text-gray-900 capitalize" x-text="calendarTitle"></h2>
                            <div class="flex bg-gray-100 rounded-lg p-1">
                                <button @click="changeView('month')" 
                                    class="px-3 py-1 text-xs font-medium rounded-md transition"
                                    :class="view === 'month' ? 'bg-white shadow text-gray-900' : 'text-gray-500 hover:text-gray-900'">
                                    Mois
                                </button>
                                <button @click="changeView('week')" 
                                    class="px-3 py-1 text-xs font-medium rounded-md transition"
                                    :class="view === 'week' ? 'bg-white shadow text-gray-900' : 'text-gray-500 hover:text-gray-900'">
                                    Semaine
                                </button>
                                <button @click="changeView('day')" 
                                    class="px-3 py-1 text-xs font-medium rounded-md transition"
                                    :class="view === 'day' ? 'bg-white shadow text-gray-900' : 'text-gray-500 hover:text-gray-900'">
                                    Jour
                                </button>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button @click="prev()" class="p-2 hover:bg-gray-100 rounded-lg text-gray-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                            </button>
                            <button @click="today()" class="px-3 py-1 text-sm font-medium hover:bg-gray-100 rounded-lg text-gray-600">
                                Aujourd'hui
                            </button>
                            <button @click="next()" class="p-2 hover:bg-gray-100 rounded-lg text-gray-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
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
                                    <span class="text-sm font-medium text-gray-700 ml-1" :class="{'text-indigo-600 font-bold': isToday(date)}" x-text="date"></span>
                                    
                                    <!-- Events Points -->
                                     <div class="mt-1 flex flex-col gap-1 overflow-y-auto max-h-[60px]">
                                        <!-- Sessions -->
                                        <template x-for="session in getSessionsForDate(date)">
                                             <a :href="'/espace-mentor/sessions/' + session.id"
                                                class="text-[10px] truncate px-1.5 py-0.5 rounded border block w-full"
                                                :class="session.status === 'confirmed' ? 'bg-green-100 text-green-700 border-green-200' : 'bg-blue-100 text-blue-700 border-blue-200'">
                                                <span x-text="session.time + ' ' + session.title"></span>
                                            </a>
                                        </template>

                                        <!-- Availabilities (Just dots or small indicators in month view to avoid clutter) -->
                                        <div x-show="hasAvailability(date)" class="text-[10px] px-1 py-0.5 bg-purple-50 text-purple-600 rounded border border-purple-100 text-center">
                                            Dispo
                                        </div>
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
                                    <div class="p-2 text-center border-r border-gray-100" :class="{'bg-indigo-50': isDateToday(day.date)}">
                                        <div class="text-xs text-gray-500 uppercase" x-text="day.dayName"></div>
                                        <div class="font-bold text-gray-900" x-text="day.dayNumber"></div>
                                    </div>
                                </template>
                            </div>
                            <div class="relative grid grid-cols-8" style="height: 600px; overflow-y: auto;">
                                <!-- Time Slots -->
                                <div class="col-span-1 border-r border-gray-100 bg-gray-50">
                                    <template x-for="hour in hours">
                                        <div class="h-12 border-b border-gray-100 text-xs text-gray-400 text-center pt-1" x-text="hour + ':00'"></div>
                                    </template>
                                </div>
                                <!-- Days Columns -->
                                <template x-for="(day, dayIndex) in weekDays" :key="dayIndex">
                                    <div class="col-span-1 border-r border-gray-100 relative h-full">
                                         <!-- Grid Lines -->
                                        <template x-for="_ in hours">
                                            <div class="h-12 border-b border-gray-100"></div>
                                        </template>

                                        <!-- Availabilities Overlay -->
                                        <template x-for="slot in getAvailabilitiesForDay(day.date)">
                                            <div class="absolute w-[90%] left-[5%] bg-purple-100 border border-purple-200 text-purple-700 rounded p-1 text-[10px] flex items-center justify-center opacity-80 hover:opacity-100 transition z-0"
                                                :style="getStyleForSlot(slot.start_time, slot.end_time)">
                                                <span x-text="'Dispo'"></span>
                                            </div>
                                        </template>
                                        
                                        <!-- Sessions Overlay -->
                                        <template x-for="session in getSessionsForFullDate(day.date)">
                                            <a :href="'/espace-mentor/sessions/' + session.id"
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
                                    <div class="w-16 text-xs text-gray-400 text-right pr-4 py-2 border-r border-gray-100 sticky left-0 bg-white group-hover:bg-gray-50 z-10" x-text="hour + ':00'"></div>
                                    <div class="flex-1 relative p-1">
                                         <!-- Items logic analogous to Week View but simpler since it's one column -->
                                         <!-- TODO: Refactor overlay logic to be shared if possible, duplicated here for simplicity -->
                                    </div>
                                </div>
                            </template>
                            
                            <!-- Day View Overlays Container (since grid rows are fixed height, we ideally use absolute positioning based on time) -->
                            <div class="absolute top-0 left-16 right-0 bottom-0 pointer-events-none">
                                 <!-- Availabilities -->
                                 <template x-for="slot in getAvailabilitiesForDay(currentDate)">
                                    <div class="absolute left-2 right-2 bg-purple-100 border border-purple-200 text-purple-700 rounded p-2 text-xs opacity-70 pointer-events-auto"
                                         :style="getStyleForDayView(slot.start_time, slot.end_time)">
                                        <span class="font-bold">Disponible</span>
                                        <span x-text="slot.start_time + ' - ' + slot.end_time"></span>
                                    </div>
                                </template>

                                <!-- Sessions -->
                                <template x-for="session in getSessionsForFullDate(currentDate)">
                                    <a :href="'/espace-mentor/sessions/' + session.id"
                                        class="absolute left-10 right-10 p-2 rounded border pointer-events-auto shadow hover:shadow-md transition"
                                        :class="session.status === 'confirmed' ? 'bg-green-100 text-green-700 border-green-200' : 'bg-blue-100 text-blue-700 border-blue-200'"
                                        :style="getStyleForDayView(session.time, session.endTime)">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <div class="font-bold" x-text="session.title"></div>
                                                <div class="text-xs" x-text="'Avec ' + session.mentees"></div>
                                            </div>
                                            <div class="text-xs font-mono" x-text="session.time + ' - ' + session.endTime"></div>
                                        </div>
                                    </a>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Sessions List -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="font-bold text-gray-900 mb-4">Prochaines Séances</h3>
                    
                    @php
                        $pendingRequests = $upcomingSessions->where('status', 'proposed');
                        $confirmedSessions = $upcomingSessions->whereIn('status', ['confirmed', 'accepted']);
                    @endphp

                    @if($pendingRequests->isNotEmpty())
                        <div class="mb-6">
                            <h4 class="text-xs font-bold text-yellow-600 uppercase tracking-wide mb-3">Demandes en attente</h4>
                            <div class="space-y-3">
                                @foreach($pendingRequests as $session)
                                    <div class="flex items-center gap-4 p-3 bg-yellow-50 rounded-lg border border-yellow-100">
                                        <div class="bg-white text-yellow-600 rounded-lg p-2.5 flex flex-col items-center min-w-[60px] shadow-sm">
                                            <span class="text-xs font-bold uppercase">{{ $session->scheduled_at->format('M') }}</span>
                                            <span class="text-xl font-bold">{{ $session->scheduled_at->format('d') }}</span>
                                        </div>
                                        <div class="flex-1">
                                            <div class="flex justify-between items-start">
                                                <h4 class="font-bold text-gray-900">{{ $session->title }}</h4>
                                                <span class="px-2 py-0.5 text-[10px] font-bold bg-yellow-200 text-yellow-800 rounded-full">DEMANDE</span>
                                            </div>
                                            <p class="text-sm text-gray-600">{{ $session->scheduled_at->format('H:i') }} - {{ $session->scheduled_at->addMinutes($session->duration_minutes)->format('H:i') }}</p>
                                            <p class="text-xs text-gray-500 mt-1">Avec {{ $session->mentees->pluck('name')->join(', ') }}</p>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <form action="{{ route('mentor.mentorship.sessions.accept', $session) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="text-green-600 hover:text-green-800 p-2 hover:bg-green-100 rounded-full transition" title="Accepter">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                </button>
                                            </form>
                                            <form action="{{ route('mentor.mentorship.sessions.refuse', $session) }}" method="POST" onsubmit="return confirm('Refuser cette demande ?');">
                                                @csrf
                                                <button type="submit" class="text-red-600 hover:text-red-800 p-2 hover:bg-red-100 rounded-full transition" title="Refuser">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                </button>
                                            </form>
                                            <a href="{{ route('mentor.mentorship.sessions.show', $session) }}" class="text-yellow-600 hover:text-yellow-800 p-2 hover:bg-yellow-100 rounded-full transition" title="Voir détails">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($confirmedSessions->isEmpty() && $pendingRequests->isEmpty())
                        <p class="text-gray-500 text-sm">Aucune séance prévue prochainement.</p>
                    @elseif($confirmedSessions->isNotEmpty())
                         <h4 class="text-xs font-bold text-indigo-600 uppercase tracking-wide mb-3">Séances confirmées</h4>
                        <div class="space-y-3">
                            @foreach($confirmedSessions as $session)
                                <div
                                    class="flex items-center gap-4 p-3 hover:bg-gray-50 rounded-lg transition border border-transparent hover:border-gray-100">
                                    <div
                                        class="bg-indigo-100 text-indigo-600 rounded-lg p-2.5 flex flex-col items-center min-w-[60px]">
                                        <span class="text-xs font-bold uppercase">{{ $session->scheduled_at->format('M') }}</span>
                                        <span class="text-xl font-bold">{{ $session->scheduled_at->format('d') }}</span>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="font-bold text-gray-900">{{ $session->title }}</h4>
                                        <p class="text-sm text-gray-500">{{ $session->scheduled_at->format('H:i') }} -
                                            {{ $session->scheduled_at->addMinutes($session->duration_minutes)->format('H:i') }} •
                                            Avec {{ $session->mentees->pluck('name')->join(', ') }}
                                        </p>
                                    </div>
                                    <a href="{{ route('mentor.mentorship.sessions.show', $session) }}"
                                        class="text-gray-400 hover:text-indigo-600">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                                            </path>
                                        </svg>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Availability Settings (Right - 1/3) -->
            <div class="space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Gérer mes disponibilités
                    </h3>

                    <form action="{{ route('mentor.mentorship.availability.store') }}" method="POST" x-ref="form">
                        @csrf
                        <div class="space-y-4 mb-6">
                            <template x-for="(slot, index) in localAvailabilities" :key="index">
                                <div class="bg-gray-50 rounded-lg p-3 relative group border border-gray-200">
                                    <button type="button" @click="removeSlot(index)"
                                        class="absolute top-2 right-2 text-gray-400 hover:text-red-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>

                                    <!-- Type Selection -->
                                    <div class="mb-2">
                                        <label class="inline-flex items-center text-xs text-gray-600 mb-1">
                                            <input type="checkbox" x-model="slot.is_recurring" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 h-3 w-3 mr-1">
                                            Récurrent (Hebdomadaire)
                                        </label>
                                        <input type="hidden" :name="'availabilities['+index+'][is_recurring]'" :value="slot.is_recurring ? '1' : '0'">
                                    </div>

                                    <div class="grid grid-cols-1 gap-2">
                                        <!-- Recurring Logic -->
                                        <div x-show="slot.is_recurring">
                                            <select :name="'availabilities['+index+'][day_of_week]'" x-model="slot.day_of_week"
                                                class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-white py-1">
                                                <option value="1">Lundi</option>
                                                <option value="2">Mardi</option>
                                                <option value="3">Mercredi</option>
                                                <option value="4">Jeudi</option>
                                                <option value="5">Vendredi</option>
                                                <option value="6">Samedi</option>
                                                <option value="0">Dimanche</option>
                                            </select>
                                        </div>

                                        <!-- Punctual Logic -->
                                        <div x-show="!slot.is_recurring">
                                            <input type="date" :name="'availabilities['+index+'][specific_date]'" x-model="slot.specific_date"
                                                class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-white py-1">
                                        </div>

                                        <div class="flex items-center gap-2">
                                            <input type="time" :name="'availabilities['+index+'][start_time]'" x-model="slot.start_time"
                                                class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-white py-1">
                                            <span class="text-gray-400">-</span>
                                            <input type="time" :name="'availabilities['+index+'][end_time]'" x-model="slot.end_time"
                                                class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-white py-1">
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <button type="button" @click="addSlot()"
                                class="w-full border-2 border-dashed border-gray-300 rounded-lg p-3 text-gray-500 hover:border-indigo-500 hover:text-indigo-600 transition flex items-center justify-center gap-2 text-sm font-medium">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                Ajouter un créneau
                            </button>
                        </div>

                        <button type="submit"
                            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2.5 px-4 rounded-lg font-medium transition shadow-sm">
                            Enregistrer les changements
                        </button>
                    </form>
                </div>
                
                <!-- Legend -->
                <div class="flex flex-wrap gap-4 text-xs text-gray-600 px-2">
                     <div class="flex items-center gap-1"><span class="w-3 h-3 rounded-full border border-purple-500 bg-purple-100"></span> Disponibilité</div>
                     <div class="flex items-center gap-1"><span class="w-3 h-3 rounded-full border border-blue-500 bg-blue-100"></span> Séance planifiée</div>
                     <div class="flex items-center gap-1"><span class="w-3 h-3 rounded-full border border-green-500 bg-green-100"></span> Séance confirmée</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        @php
            $sessionsJson = $upcomingSessions->map(function ($s) {
                return [
                    'id' => $s->id,
                    'title' => $s->title,
                    'scheduled_at' => $s->scheduled_at->toIso8601String(),
                    'time' => $s->scheduled_at->format('H:i'),
                    'endTime' => $s->scheduled_at->copy()->addMinutes($s->duration_minutes)->format('H:i'),
                    'status' => $s->status,
                    'mentees' => $s->mentees->pluck('name')->join(', '),
                ];
            });

            // Prepare Availabilities for JS
            $availabilitiesMapped = $availabilities->map(function ($a) {
                return [
                    'is_recurring' => (bool)$a->is_recurring,
                    'day_of_week' => $a->day_of_week,
                    'specific_date' => $a->specific_date ? $a->specific_date->format('Y-m-d') : null,
                    'start_time' => \Carbon\Carbon::parse($a->start_time)->format('H:i'),
                    'end_time' => \Carbon\Carbon::parse($a->end_time)->format('H:i'),
                ];
            });

            // Default if empty
            if ($availabilitiesMapped->isEmpty()) {
                $availabilitiesMapped = collect([[
                    'is_recurring' => true, 
                    'day_of_week' => 1, 
                    'specific_date' => null,
                    'start_time' => '09:00', 
                    'end_time' => '17:00'
                ]]);
            }
        @endphp

        function mentorCalendar() {
            return {
                view: 'month', // month, week, day
                currentDate: new Date(),
                monthNames: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
                dayNames: ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'],
                shortDayNames: ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'],
                blankDays: [],
                no_of_days: [],
                weekDays: [],
                hours: Array.from({length: 24}, (_, i) => i), // 0 to 23

                sessions: @json($sessionsJson),
                
                // Form data
                localAvailabilities: @json($availabilitiesMapped),

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
                        
                        // Si même mois
                        if (start.getMonth() === end.getMonth()) {
                            return start.getDate() + ' - ' + end.getDate() + ' ' + this.monthNames[start.getMonth()] + ' ' + start.getFullYear();
                        }
                        // Si chevauchemeent mois/année
                        return start.getDate() + ' ' + this.monthNames[start.getMonth()] + ' - ' + end.getDate() + ' ' + this.monthNames[end.getMonth()] + ' ' + end.getFullYear();
                    } else {
                        // Day View
                        return this.dayNames[this.currentDate.getDay()] + ' ' + this.currentDate.getDate() + ' ' + this.monthNames[this.currentDate.getMonth()] + ' ' + this.currentDate.getFullYear();
                    }
                },

                renderCalendar() {
                    if (this.view === 'month') {
                        this.getNoOfDays();
                    } else if (this.view === 'week') {
                        this.getWeekDays();
                    }
                    // Day view doesn't need pre-calculation of grid
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
                        this.currentDate = new Date(this.currentDate); // trigger reactivity
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

                // --- Availability & Session Logic ---

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

                // Check if date has ANY availability (Recurring OR Punctual)
                hasAvailability(date) {
                    const checkDate = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth(), date);
                    const dayOfWeek = checkDate.getDay(); // 0-6
                    const dateString = this.formatDateYMD(checkDate);

                    return this.localAvailabilities.some(slot => {
                        if (slot.is_recurring) {
                            return parseInt(slot.day_of_week) === dayOfWeek;
                        } else {
                            return slot.specific_date === dateString;
                        }
                    });
                },

                getAvailabilitiesForDay(dateObj) {
                     const dayOfWeek = dateObj.getDay();
                     const dateString = this.formatDateYMD(dateObj);

                     return this.localAvailabilities.filter(slot => {
                         if (slot.is_recurring) {
                            return parseInt(slot.day_of_week) === dayOfWeek;
                        } else {
                            return slot.specific_date === dateString;
                        }
                     });
                },

                // --- Grid Construction Helpers ---

                getNoOfDays() {
                    let daysInMonth = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth() + 1, 0).getDate();
                    let dayOfWeek = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth(), 1).getDay();
                    
                    let blankDaysArray = [];
                    for (var i = 1; i <= dayOfWeek; i++) { // start from 1 not 0 if week starts Sunday? JS getDay 0=Sunday.
                        // Standard calendar starts Sunday=0. If we want Monday start, logic changes.
                        // Assuming Sunday start for simplicity matching JS
                        // Actually, UI shows 'Dim' first.
                        blankDaysArray.push(i);
                    }
                    // Wait, if 1st is Sunday (0), loop 1<=0 false. No blanks. Correct.
                    // If 1st is Monday (1), loop 1<=1. One blank (Sunday). Correct.

                    // If user wants Monday start:
                    // let dayOfWeek = (new Date(...).getDay() + 6) % 7; // Mon=0, Sun=6

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
                    const diff = d.getDate() - day; // Adjust for Sunday (0) being start
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

                // --- Visualization Helpers ---

                formatDateYMD(date) {
                    // Manually format to avoid TZ issues
                    const offset = date.getTimezoneOffset();
                    const d = new Date(date.getTime() - (offset*60*1000));
                    return d.toISOString().split('T')[0];
                },

                getStyleForSlot(startTime, endTime) {
                    // Convert HH:MM to percentage or pixel position
                    // Height of day container is fixed (e.g. 600px for 24h? or 1h=Height)
                    // Grid has 1h = 48px or similar (h-12 is 3rem = 48px)
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
                    // Similar to week view but day view items are 80px high (h-20)
                    // Wait, day view implementation above uses a list of hours. 
                    // To place absolute items correctly, we need to map to that list.
                    const rowHeight = 80;
                    const startH = parseInt(startTime.split(':')[0]);
                    const startM = parseInt(startTime.split(':')[1]);
                    const endH = parseInt(endTime.split(':')[0]);
                    const endM = parseInt(endTime.split(':')[1]);

                    const top = (startH * rowHeight) + ((startM / 60) * rowHeight);
                    const durationMins = ((endH * 60) + endM) - ((startH * 60) + startM);
                    const height = (durationMins / 60) * rowHeight;
                    
                    return `top: ${top}px; height: ${height}px;`;
                },

                // --- Form Helpers ---

                addSlot() {
                    this.localAvailabilities.push({ 
                        is_recurring: true, 
                        day_of_week: 1, 
                        specific_date: new Date().toISOString().split('T')[0],
                        start_time: '09:00', 
                        end_time: '17:00' 
                    });
                },

                removeSlot(index) {
                    this.localAvailabilities.splice(index, 1);
                }
            }
        }
    </script>
@endsection