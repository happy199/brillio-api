@extends('layouts.admin')

@section('title', 'Analytics')

@section('content')
<div class="mb-24">
    <!-- Header avec filtres -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Analytics</h1>
                <p class="text-gray-500 text-sm mt-1">Exploration multi-dimensionnelle et Smart Sourcing Engine</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="px-3 py-1 bg-indigo-50 text-indigo-700 rounded-full text-xs font-bold border border-indigo-100">
                    Live Data
                </span>
                <span class="text-xs text-gray-400">
                    Dernier scan : {{ now()->format('H:i') }}
                </span>
            </div>
        </div>

            <!-- Filtres Avancés (Smart Sourcing Engine) -->
            <form action="{{ route('admin.analytics.index') }}" method="GET" class="w-full mt-6 space-y-6" x-data='{
                situation: {!! json_encode($filters["situation"] ?? [], JSON_HEX_APOS) !!},
                interest: {!! json_encode($filters["interest"] ?? [], JSON_HEX_APOS) !!},
                country: {!! json_encode($filters["country"] ?? [], JSON_HEX_APOS) !!},
                goal: {!! json_encode($filters["goal"] ?? [], JSON_HEX_APOS) !!},
                channel: {!! json_encode($filters["channel"] ?? [], JSON_HEX_APOS) !!},
                personality: {!! json_encode($filters["personality"] ?? [], JSON_HEX_APOS) !!},
                tuition: {!! json_encode($filters["tuition"] ?? [], JSON_HEX_APOS) !!},
                salary: {!! json_encode($filters["salary"] ?? [], JSON_HEX_APOS) !!},
                startDate: "{{ $dateRange["start"]->format("Y-m-d") }}",
                endDate: "{{ $dateRange["end"]->format("Y-m-d") }}",
                
                get showTuition() {
                    let s = this.situation || [];
                    return s.length === 0 || s.some(val => ["college", "lycee", "etudiant"].includes(val));
                },
                get showSalary() {
                    let s = this.situation || [];
                    return s.length === 0 || s.some(val => ["emploi", "entrepreneur"].includes(val));
                },
                setPeriod(value) {
                    if (value === "custom") {
                        const customDates = document.getElementById("custom-dates");
                        if (customDates) {
                            customDates.scrollIntoView({ behavior: "smooth", block: "center" });
                            customDates.classList.add("ring-2", "ring-indigo-500", "ring-offset-2");
                            setTimeout(() => {
                                customDates.classList.remove("ring-2", "ring-indigo-500", "ring-offset-2");
                            }, 2000);
                        }
                        return;
                    }

                    const today = new Date();
                    const end = new Date(today);
                    let start = new Date(today);

                    switch (value) {
                        case "today": break;
                        case "3days": start.setDate(today.getDate() - 3); break;
                        case "week": start.setDate(today.getDate() - 7); break;
                        case "month": start.setDate(today.getDate() - 30); break;
                        case "quarter": start.setMonth(today.getMonth() - 3); break;
                        case "year": start.setFullYear(today.getFullYear() - 1); break;
                        case "all": start = new Date("2020-01-01"); break;
                    }

                    const format = (d) => {
                        const y = d.getFullYear();
                        const m = String(d.getMonth() + 1).padStart(2, '0');
                        const day = String(d.getDate()).padStart(2, '0');
                        return `${y}-${m}-${day}`;
                    };

                    this.startDate = format(start);
                    this.endDate = format(end);
                }
            }'>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-x-4 gap-y-6">
                    <!-- Période -->
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Période d'analyse</label>
                        <select name="preset" @change="setPeriod($event.target.value)"
                            class="w-full rounded-xl border-gray-200 text-sm focus:ring-primary-500 shadow-sm h-10">
                            <option value="today" {{ ($dateRange['preset'] ?? '' )=='today' ? 'selected' : '' }}>Aujourd'hui</option>
                            <option value="3days" {{ ($dateRange['preset'] ?? '' )=='3days' ? 'selected' : '' }}>3 derniers jours</option>
                            <option value="week" {{ ($dateRange['preset'] ?? '' )=='week' ? 'selected' : '' }}>7 derniers jours</option>
                            <option value="month" {{ ($dateRange['preset'] ?? 'month' )=='month' ? 'selected' : '' }}>30 derniers jours</option>
                            <option value="quarter" {{ ($dateRange['preset'] ?? '' )=='quarter' ? 'selected' : '' }}>3 derniers mois</option>
                            <option value="year" {{ ($dateRange['preset'] ?? '' )=='year' ? 'selected' : '' }}>Cette année</option>
                            <option value="all" {{ ($dateRange['preset'] ?? '' )=='all' ? 'selected' : '' }}>Tout</option>
                            <option value="custom" {{ ($dateRange['preset'] ?? '' )=='custom' ? 'selected' : '' }}>Personnalisé</option>
                        </select>
                    </div>

                    <!-- Situation -->
                    <div class="space-y-2" x-data='{ 
                        open: false, 
                        options: {!! json_encode($allSituations ?? [], JSON_HEX_APOS) !!},
                        get label() {
                            if (!this.situation || this.situation.length === 0) return "Toutes les situations";
                            if (this.situation.length === 1) return (this.options || {})[this.situation[0]] || this.situation[0];
                            return this.situation.length + " sélectionnées";
                        }
                    }' @click.away="open = false">
                        <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Situation</label>
                        <div class="relative">
                            <button type="button" @click="open = !open" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2 text-left text-sm flex items-center justify-between hover:border-primary-400 transition shadow-sm h-10">
                                <span x-text="label" class="truncate mr-2"></span>
                                <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="open" x-transition class="absolute z-50 mt-2 w-64 bg-white border border-gray-200 rounded-2xl shadow-xl p-2 max-h-80 overflow-y-auto">
                                <template x-for="(label, value) in options" :key="value">
                                    <label class="flex items-center gap-3 p-2 hover:bg-gray-50 rounded-xl cursor-pointer transition">
                                        <input type="checkbox" name="situation[]" :value="value" x-model="situation" class="rounded text-primary-600 focus:ring-primary-500 border-gray-300">
                                        <span class="text-sm text-gray-700" x-text="label"></span>
                                    </label>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Pays -->
                    <div class="space-y-2" x-data='{ 
                        open: false, 
                        options: {!! json_encode($allCountries ?? [], JSON_HEX_APOS) !!},
                        get label() {
                            if (!this.country || this.country.length === 0) return "Tous les pays";
                            if (this.country.length === 1) return this.country[0];
                            return this.country.length + " sélectionnés";
                        }
                    }' @click.away="open = false">
                        <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Pays</label>
                        <div class="relative">
                            <button type="button" @click="open = !open" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2 text-left text-sm flex items-center justify-between hover:border-primary-400 transition shadow-sm h-10">
                                <span x-text="label" class="truncate mr-2"></span>
                                <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="open" x-transition class="absolute z-50 mt-2 w-64 bg-white border border-gray-200 rounded-2xl shadow-xl p-2 max-h-80 overflow-y-auto">
                                <template x-for="item in options" :key="item">
                                    <label class="flex items-center gap-3 p-2 hover:bg-gray-50 rounded-xl cursor-pointer transition">
                                        <input type="checkbox" name="country[]" :value="item" x-model="country" class="rounded text-primary-600 focus:ring-primary-500 border-gray-300">
                                        <span class="text-sm text-gray-700" x-text="item"></span>
                                    </label>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- MBTI -->
                    <div class="space-y-2" x-data='{ 
                        open: false, 
                        options: {!! json_encode($allPersonalities ?? [], JSON_HEX_APOS) !!},
                        get label() {
                            if (!this.personality || this.personality.length === 0) return "Toutes";
                            if (this.personality.length === 1) return this.personality[0];
                            return this.personality.length + " sélectionnés";
                        }
                    }' @click.away="open = false">
                        <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Type MBTI</label>
                        <div class="relative">
                            <button type="button" @click="open = !open" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2 text-left text-sm flex items-center justify-between hover:border-primary-400 transition shadow-sm h-10">
                                <span x-text="label" class="truncate mr-2"></span>
                                <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="open" x-transition class="absolute z-50 mt-2 w-64 bg-white border border-gray-200 rounded-2xl shadow-xl p-2 max-h-80 overflow-y-auto">
                                <template x-for="item in options" :key="item">
                                    <label class="flex items-center gap-3 p-2 hover:bg-gray-50 rounded-xl cursor-pointer transition">
                                        <input type="checkbox" name="personality[]" :value="item" x-model="personality" class="rounded text-primary-600 focus:ring-primary-500 border-gray-300">
                                        <span class="text-sm text-gray-700" x-text="item"></span>
                                    </label>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Objectifs -->
                    <div class="space-y-2" x-data='{ 
                        open: false, 
                        options: {!! json_encode($allGoals ?? [], JSON_HEX_APOS) !!},
                        get label() {
                            if (!this.goal || this.goal.length === 0) return "Tous les objectifs";
                            return this.goal.length + " sélectionnés";
                        }
                    }' @click.away="open = false">
                        <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Objectifs</label>
                        <div class="relative">
                            <button type="button" @click="open = !open" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2 text-left text-sm flex items-center justify-between hover:border-primary-400 transition shadow-sm h-10">
                                <span x-text="label" class="truncate mr-2"></span>
                                <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="open" x-transition class="absolute z-50 mt-2 w-64 bg-white border border-gray-200 rounded-2xl shadow-xl p-2 max-h-80 overflow-y-auto">
                                <template x-for="(label, value) in options" :key="value">
                                    <label class="flex items-center gap-3 p-2 hover:bg-gray-50 rounded-xl cursor-pointer transition">
                                        <input type="checkbox" name="goal[]" :value="value" x-model="goal" class="rounded text-primary-600 focus:ring-primary-500 border-gray-300">
                                        <span class="text-sm text-gray-700" x-text="label"></span>
                                    </label>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Intérêts -->
                    <div class="space-y-2" x-data='{ 
                        open: false, 
                        options: {!! json_encode($allInterests ?? [], JSON_HEX_APOS) !!},
                        get label() {
                            if (!this.interest || this.interest.length === 0) return "Tous les intérêts";
                            return this.interest.length + " sélectionnés";
                        }
                    }' @click.away="open = false">
                        <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Intérêts</label>
                        <div class="relative">
                            <button type="button" @click="open = !open" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2 text-left text-sm flex items-center justify-between hover:border-primary-400 transition shadow-sm h-10">
                                <span x-text="label" class="truncate mr-2"></span>
                                <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="open" x-transition class="absolute z-50 mt-2 w-64 bg-white border border-gray-200 rounded-2xl shadow-xl p-2 max-h-80 overflow-y-auto">
                                <template x-for="(label, value) in options" :key="value">
                                    <label class="flex items-center gap-3 p-2 hover:bg-gray-50 rounded-xl cursor-pointer transition">
                                        <input type="checkbox" name="interest[]" :value="value" x-model="interest" class="rounded text-primary-600 focus:ring-primary-500 border-gray-300">
                                        <span class="text-sm text-gray-700" x-text="label"></span>
                                    </label>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Scolarité -->
                    <div class="space-y-2" x-show="showTuition" x-transition x-data='{ 
                        open: false, 
                        options: {
                            "-200000": "- 200.000 FCFA",
                            "200000-500000": "200k - 500k FCFA",
                            "500000-1000000": "500k - 1M FCFA",
                            "1000000-2000000": "1M - 2M FCFA",
                            "+2000000": "+ 2M FCFA"
                        },
                        get label() {
                            if (!this.tuition || this.tuition.length === 0) return "Tous les budgets";
                            return this.tuition.length + " sélectionnés";
                        }
                    }' @click.away="open = false">
                        <label class="text-xs font-bold text-indigo-600 uppercase tracking-wider">Scolarité (Budget)</label>
                        <div class="relative">
                            <button type="button" @click="open = !open" class="w-full bg-indigo-50 border border-indigo-100 rounded-xl px-4 py-2 text-left text-sm flex items-center justify-between hover:border-indigo-400 transition shadow-sm h-10">
                                <span x-text="label" class="truncate mr-2 text-indigo-700"></span>
                                <svg class="w-4 h-4 text-indigo-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="open" x-transition class="absolute z-50 mt-2 w-64 bg-white border border-gray-200 rounded-2xl shadow-xl p-2 max-h-80 overflow-y-auto">
                                <template x-for="(label, value) in options" :key="value">
                                    <label class="flex items-center gap-3 p-2 hover:bg-gray-50 rounded-xl cursor-pointer transition">
                                        <input type="checkbox" name="tuition[]" :value="value" x-model="tuition" class="rounded text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                        <span class="text-sm text-gray-700" x-text="label"></span>
                                    </label>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Salaire -->
                    <div class="space-y-2" x-show="showSalary" x-transition x-data='{ 
                        open: false, 
                        options: {
                            "-50000": "Moins de 50.000 FCFA",
                            "50000-100000": "50k - 100k FCFA",
                            "100000-250000": "100k - 250k FCFA",
                            "250000-500000": "250k - 500k FCFA",
                            "500000-1000000": "500k - 1M FCFA",
                            "1000000-3000000": "1M - 3M FCFA",
                            "+3000000": "+ 3M FCFA"
                        },
                        get label() {
                            if (!this.salary || this.salary.length === 0) return "Toutes les tranches";
                            return this.salary.length + " sélectionnés";
                        }
                    }' @click.away="open = false">
                        <label class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Salaire (Cible)</label>
                        <div class="relative">
                            <button type="button" @click="open = !open" class="w-full bg-emerald-50 border border-emerald-100 rounded-xl px-4 py-2 text-left text-sm flex items-center justify-between hover:border-emerald-400 transition shadow-sm h-10">
                                <span x-text="label" class="truncate mr-2 text-emerald-700"></span>
                                <svg class="w-4 h-4 text-emerald-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="open" x-transition class="absolute z-50 mt-2 w-64 bg-white border border-gray-200 rounded-2xl shadow-xl p-2 max-h-80 overflow-y-auto">
                                <template x-for="(label, value) in options" :key="value">
                                    <label class="flex items-center gap-3 p-2 hover:bg-gray-50 rounded-xl cursor-pointer transition">
                                        <input type="checkbox" name="salary[]" :value="value" x-model="salary" class="rounded text-emerald-600 focus:ring-emerald-500 border-gray-300">
                                        <span class="text-sm text-gray-700" x-text="label"></span>
                                    </label>
                                </template>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Custom Dates Row -->
                <div id="custom-dates" class="flex items-center gap-4 bg-gray-50 p-4 rounded-xl border border-gray-100">
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-semibold text-gray-400">DU</label>
                        <input type="date" name="start_date" id="start_date" x-model="startDate" value="{{ $dateRange['start']->format('Y-m-d') }}" class="rounded-lg border-gray-200 text-sm p-1.5 focus:ring-primary-500 shadow-sm">
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-semibold text-gray-400">AU</label>
                        <input type="date" name="end_date" id="end_date" x-model="endDate" value="{{ $dateRange['end']->format('Y-m-d') }}" class="rounded-lg border-gray-200 text-sm p-1.5 focus:ring-primary-500 shadow-sm">
                    </div>
                </div>

                <!-- Actions Final Row -->
                <div class="flex items-center justify-end gap-3 pb-2">
                    <a href="{{ route('admin.analytics.index') }}" class="h-11 px-6 flex items-center justify-center bg-gray-50 text-gray-600 rounded-xl hover:bg-gray-100 transition text-sm font-medium border border-gray-100" title="Réinitialiser">
                        Effacer
                    </a>
                    <button type="submit" class="h-11 px-10 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 text-sm font-bold shadow-lg shadow-indigo-200 transition-all hover:scale-[1.02] active:scale-[0.98]">
                        Appliquer les filtres
                    </button>
                </div>
            </form>
        </div>

        <!-- Export & Metadata Row -->
        <div class="bg-gray-50 border-t border-gray-100 px-6 py-4 rounded-b-xl flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <span class="text-xs font-bold text-gray-400 uppercase">Exports Master :</span>
                <div class="flex gap-2">
                    <a href="{{ route('admin.analytics.export-pdf', array_merge(request()->query(), ['type' => 'users'])) }}"
                        class="px-3 py-1.5 bg-white border border-gray-200 rounded-lg text-xs font-bold text-gray-600 hover:bg-gray-50 transition shadow-sm">
                        PDF Jeunes
                    </a>
                    <a href="{{ route('admin.analytics.export-pdf', array_merge(request()->query(), ['type' => 'mentors'])) }}"
                        class="px-3 py-1.5 bg-white border border-gray-200 rounded-lg text-xs font-bold text-gray-600 hover:bg-gray-50 transition shadow-sm">
                        PDF Mentors
                    </a>
                    <a href="{{ route('admin.analytics.export-csv', array_merge(request()->query(), ['type' => 'users'])) }}" 
                        class="px-3 py-1.5 bg-indigo-50 border border-indigo-100 rounded-lg text-xs font-bold text-indigo-700 hover:bg-indigo-100 transition shadow-sm flex items-center gap-2">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        CSV Smart Sourcing
                    </a>
                </div>
            </div>
            <div class="text-xs text-gray-500">
                Données du <span class="font-bold text-gray-900">{{ $dateRange['start']->format('d/m/Y') }}</span> au <span class="font-bold text-gray-900">{{ $dateRange['end']->format('d/m/Y') }}</span>
            </div>
        </div>

        <!-- Période actuelle -->
        <p class="text-sm text-gray-500 mt-3">
            Données du <strong>{{ $dateRange['start']->format('d/m/Y') }}</strong>
            au <strong>{{ $dateRange['end']->format('d/m/Y') }}</strong>
        </p>
    </div>

    <!-- Stats principales -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-xl p-6 shadow-sm mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Utilisateurs totaux</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($stats['total_users']) }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-green-600 font-medium">+{{ $stats['new_users_period'] }}</span>
                <span class="text-gray-500 ml-1">sur la période</span>
            </div>
        </div>

        <div class="bg-white rounded-xl p-6 shadow-sm mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Tests complétés</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($stats['total_tests']) }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-purple-600 font-medium">+{{ $stats['tests_period'] }}</span>
                <span class="text-gray-500 ml-1">sur la période ({{ $stats['test_completion_rate'] }}% taux)</span>
            </div>
        </div>

        <div class="bg-white rounded-xl p-6 shadow-sm mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Messages chat</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($stats['total_messages']) }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-green-600 font-medium">+{{ $stats['messages_period'] }}</span>
                <span class="text-gray-500 ml-1">sur la période ({{ $stats['conversations_period'] }} conv.)</span>
            </div>
        </div>

        <div class="bg-white rounded-xl p-6 shadow-sm mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Mentors actifs</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($stats['active_mentors']) }}</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-orange-600 font-medium">{{ $stats['youth_engagement']['mentorship_intent_rate'] }}%</span>
                <span class="text-gray-500 ml-1">veulent un mentor</span>
            </div>
        </div>
    </div>

    <!-- Graphique d'évolution -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <h3 class="font-semibold text-gray-900 mb-4">Évolution sur la période</h3>
        <div class="h-64">
            <canvas id="evolutionChart"></canvas>
        </div>
    </div>

    <!-- Main Demographic Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-6">
        <!-- Chart Situation -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6 flex flex-col border border-transparent hover:border-gray-100 transition-colors">
            <h3 class="font-bold text-gray-900 mb-1 text-sm uppercase tracking-wider">Démographie</h3>
            <p class="text-xs text-gray-500 mb-4">Répartition par situation actuelle</p>
            <div class="h-64 mt-auto">
                <canvas id="situationChart"></canvas>
            </div>
        </div>

        <!-- Chart Sources -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6 flex flex-col hover:shadow-sm transition-shadow">
            <h3 class="font-bold text-gray-900 mb-1 text-sm uppercase tracking-wider">Acquisition</h3>
            <p class="text-xs text-gray-500 mb-4">Canaux d'entrée sur la plateforme</p>
            <div class="h-64 mt-auto">
                <canvas id="sourceChart"></canvas>
            </div>
        </div>

        <!-- Chart Tuition -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6 flex flex-col hover:shadow-sm transition-shadow">
            <h3 class="font-bold text-indigo-600 mb-1 text-sm uppercase tracking-wider">Scolarité (Budget)</h3>
            <p class="text-xs text-gray-500 mb-4">Capacité de financement annuelle</p>
            <div class="h-64 mt-auto">
                <canvas id="tuitionChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Salary Analytics Grid (Smart Sourcing) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Chart Salary Target (Job Seekers) -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6 flex flex-col border-2 border-emerald-50 hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2 mb-1">
                <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                <h3 class="font-bold text-emerald-600 text-sm uppercase tracking-wider">Salaire (Cible)</h3>
                <span class="px-1.5 py-0.5 bg-emerald-100 text-emerald-700 rounded text-[10px] font-bold ml-auto uppercase tracking-tighter">Attentes</span>
            </div>
            <p class="text-xs text-gray-500 mb-6 font-medium">Recherche d'emploi (Smart Sourcing)</p>
            <div class="h-64 mt-auto">
                <canvas id="salaryChart"></canvas>
            </div>
        </div>

        <!-- Chart Salary Actual (Employed) -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6 flex flex-col border-2 border-blue-50 hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2 mb-1">
                <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                <h3 class="font-bold text-blue-600 text-sm uppercase tracking-wider">Salaire (Actuel)</h3>
                <span class="px-1.5 py-0.5 bg-blue-100 text-blue-700 rounded text-[10px] font-bold ml-auto uppercase tracking-tighter">Réel</span>
            </div>
            <p class="text-xs text-gray-500 mb-6 font-medium">En poste / Salariés (Marché réel)</p>
            <div class="h-64 mt-auto">
                <canvas id="actualSalaryChart"></canvas>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Heatmap Intérêts -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <h3 class="font-semibold text-gray-900 mb-4">Top 10 Centres d'Intérêt</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-2">
                @php
                    $interestLabels = [
                        'tech' => 'Technologie',
                        'design' => 'Design',
                        'business' => 'Business',
                        'marketing' => 'Marketing',
                        'communication' => 'Communication',
                        'science' => 'Sciences',
                        'arts' => 'Arts',
                        'health' => 'Santé',
                        'law' => 'Droit',
                        'finance' => 'Finance',
                        'education' => 'Education',
                        'autre' => 'Autre'
                    ];
                    $totalInterests = array_sum($stats['youth_engagement']['interests']) ?: 1;
                @endphp
                @forelse($stats['youth_engagement']['interests'] as $interest => $count)
                <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-600 w-24 truncate">{{ $interestLabels[$interest] ?? $interest }}</span>
                    <div class="flex-1 bg-gray-100 rounded-full h-2">
                        <div class="bg-indigo-500 h-2 rounded-full" style="width: {{ ($count / $totalInterests) * 100 }}%"></div>
                    </div>
                    <span class="text-xs text-gray-400 w-8 text-right font-medium">{{ $count }}</span>
                </div>
                @empty
                <p class="text-gray-500 text-center py-4">Pas encore de données</p>
                @endforelse
            </div>
        </div>

        <!-- Objectifs / Motivations -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <h3 class="font-semibold text-gray-900 mb-4">Objectifs à l'inscription</h3>
            <div class="space-y-3">
                @php
                    $goalLabels = [
                        'mentor' => 'Trouver un mentor',
                        'orientation' => 'Orientation scolaire',
                        'personnalite' => 'Test de personnalité',
                        'ia' => 'Conseiller IA',
                        'documents' => 'Gestion de documents',
                        'non_renseigne' => 'Non spécifié'
                    ];
                @endphp
                @foreach($stats['youth_engagement']['goals'] as $goal => $count)
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">{{ $goalLabels[$goal] ?? $goal }}</span>
                    <span class="text-sm font-semibold px-2 py-0.5 bg-gray-100 rounded text-gray-700">{{ $count }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Distribution des types de personnalité -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6 flex flex-col border border-transparent hover:border-gray-50 transition-colors">
            <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                Distribution des personnalités (période)
            </h3>
            <div class="space-y-4">
                @forelse($stats['personality_distribution'] as $type => $count)
                <div class="group">
                    <div class="flex items-center justify-between mb-1.5 px-1">
                        <div class="flex items-baseline gap-2">
                            <span class="text-xs font-bold text-indigo-600 bg-indigo-50 px-1.5 py-0.5 rounded uppercase tracking-wider">{{ $type }}</span>
                            <span class="text-sm font-medium text-gray-700">{{ $personalityLabels[$type]['label'] ?? 'Analyste' }}</span>
                        </div>
                        <span class="text-xs font-bold text-gray-500">{{ $count }}</span>
                    </div>
                    <div class="flex-1 bg-gray-100 rounded-full h-3 overflow-hidden">
                        <div class="bg-gradient-to-r from-indigo-500 to-purple-500 h-full rounded-full transition-all duration-500"
                            style="width: {{ $stats['tests_period'] > 0 ? ($count / $stats['tests_period'] * 100) : 0 }}%">
                        </div>
                    </div>
                </div>
                @empty
                <div class="flex flex-col items-center justify-center py-10 text-gray-400 bg-gray-50 rounded-xl border border-dashed border-gray-200">
                    <svg class="w-10 h-10 mb-2 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 9.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-sm">Aucune donnée sur cette période</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Distribution par pays -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <h3 class="font-semibold text-gray-900 mb-4">Utilisateurs par pays (période)</h3>
            <div class="space-y-3">
                @forelse($stats['users_by_country'] as $country)
                <div class="flex items-center gap-3">
                    <div class="w-24 text-sm text-gray-700 truncate">{{ $country->country ?? 'Non renseigné' }}</div>
                    <div class="flex-1 bg-gray-100 rounded-full h-4">
                        <div class="bg-gradient-to-r from-green-500 to-teal-500 h-4 rounded-full"
                            style="width: {{ $stats['new_users_period'] > 0 ? ($country->total / $stats['new_users_period'] * 100) : 0 }}%">
                        </div>
                    </div>
                    <div class="w-12 text-sm text-gray-500 text-right">{{ $country->total }}</div>
                </div>
                @empty
                <p class="text-gray-500 text-center py-4">Aucune donnée sur cette période</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Spécialisations des mentors -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <h3 class="font-semibold text-gray-900 mb-4">Spécialisations des mentors</h3>
            <div class="space-y-3">
                @forelse($stats['mentors_by_specialization'] as $spec)
                <div class="flex items-center gap-3">
                    <div class="w-32 text-sm text-gray-700 truncate">
                        {{ $specializations[$spec->specialization] ?? $spec->specialization ?? 'Non défini' }}</div>
                    <div class="flex-1 bg-gray-100 rounded-full h-4">
                        <div class="bg-gradient-to-r from-orange-500 to-red-500 h-4 rounded-full"
                            style="width: {{ $stats['active_mentors'] > 0 ? ($spec->total / $stats['active_mentors'] * 100) : 0 }}%">
                        </div>
                    </div>
                    <div class="w-12 text-sm text-gray-500 text-right">{{ $spec->total }}</div>
                </div>
                @empty
                <p class="text-gray-500 text-center py-4">Aucune donnée disponible</p>
                @endforelse
            </div>
        </div>

        <!-- Activité récente -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <h3 class="font-semibold text-gray-900 mb-4">Inscriptions récentes (période)</h3>
            <div class="space-y-4 max-h-80 overflow-y-auto">
                @forelse($stats['recent_signups'] as $user)
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-full flex items-center justify-center {{ $user->user_type === 'mentor' ? 'bg-orange-100' : 'bg-blue-100' }}">
                        <span
                            class="{{ $user->user_type === 'mentor' ? 'text-orange-600' : 'text-blue-600' }} font-semibold">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $user->name }}</p>
                        <p class="text-xs text-gray-500">{{ $user->email }}</p>
                    </div>
                    <div class="text-right">
                        <span
                            class="text-xs px-2 py-1 rounded-full {{ $user->user_type === 'mentor' ? 'bg-orange-100 text-orange-700' : 'bg-blue-100 text-blue-700' }}">
                            {{ $user->user_type === 'mentor' ? 'Mentor' : 'Jeune' }}
                        </span>
                        <p class="text-xs text-gray-400 mt-1">{{ $user->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                @empty
                <p class="text-gray-500 text-center py-4">Aucune inscription sur cette période</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Documents -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <h3 class="font-semibold text-gray-900 mb-4">Documents académiques (période)</h3>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div class="text-center p-4 bg-gray-50 rounded-lg">
                <p class="text-3xl font-bold text-gray-600">{{ $stats['documents']['total'] ?? 0 }}</p>
                <p class="text-sm text-gray-600 mt-1">Total (tous)</p>
            </div>
            <div class="text-center p-4 bg-blue-50 rounded-lg">
                <p class="text-3xl font-bold text-blue-600">{{ $stats['documents']['period'] ?? 0 }}</p>
                <p class="text-sm text-gray-600 mt-1">Sur la période</p>
            </div>
            <div class="text-center p-4 bg-green-50 rounded-lg">
                <p class="text-3xl font-bold text-green-600">{{ $stats['documents']['bulletin'] ?? 0 }}</p>
                <p class="text-sm text-gray-600 mt-1">Bulletins</p>
            </div>
            <div class="text-center p-4 bg-purple-50 rounded-lg">
                <p class="text-3xl font-bold text-purple-600">{{ $stats['documents']['releve_notes'] ?? 0 }}</p>
                <p class="text-sm text-gray-600 mt-1">Relevés</p>
            </div>
            <div class="text-center p-4 bg-orange-50 rounded-lg">
                <p class="text-3xl font-bold text-orange-600">{{ $stats['documents']['diplome'] ?? 0 }}</p>
                <p class="text-sm text-gray-600 mt-1">Diplômes</p>
            </div>
        </div>
    </div>
    <!-- Avis Utilisateurs (Popups) -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
                Avis et Retours Utilisateurs
            </h3>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Statistiques des Avis -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Moyenne Globale -->
                <div class="bg-gray-50 rounded-xl p-5 text-center">
                    <p class="text-sm text-gray-500 mb-1">Note moyenne globale</p>
                    <div class="flex items-center justify-center gap-2">
                        <span class="text-4xl font-bold text-gray-900">{{ number_format($stats['feedbacks']['average_rating'], 1) }}</span>
                        <span class="text-xl text-yellow-400">/ 5</span>
                    </div>
                    <p class="text-xs text-gray-400 mt-2">Basé sur {{ $stats['feedbacks']['total'] }} avis au total</p>
                </div>

                <!-- Répartition (Période) -->
                <div>
                    <h4 class="text-sm font-semibold text-gray-700 mb-3">Répartition sur la période ({{ $stats['feedbacks']['period'] }} avis)</h4>
                    <div class="space-y-2">
                        @for($i = 5; $i >= 1; $i--)
                            @php
                                $count = $stats['feedbacks']['rating_distribution'][$i] ?? 0;
                                $percentage = $stats['feedbacks']['period'] > 0 ? ($count / $stats['feedbacks']['period']) * 100 : 0;
                            @endphp
                            <div class="flex items-center gap-3 text-sm">
                                <div class="flex items-center gap-1 w-12">
                                    <span class="font-medium text-gray-700">{{ $i }}</span>
                                    <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                </div>
                                <div class="flex-1 bg-gray-100 rounded-full h-2">
                                    <div class="bg-yellow-400 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                </div>
                                <span class="w-8 text-right text-gray-500 text-xs">{{ $count }}</span>
                            </div>
                        @endfor
                    </div>
                </div>
            </div>

            <!-- Liste des Commentaires Récents -->
            <div class="lg:col-span-2">
                <h4 class="text-sm font-semibold text-gray-700 mb-4">Derniers commentaires reçus</h4>
                <div class="space-y-4 max-h-[400px] overflow-y-auto pr-2 custom-scrollbar">
                    @forelse($stats['feedbacks']['recent'] as $feedback)
                        <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                            <div class="flex justify-between items-start mb-2">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-xs">
                                        {{ strtoupper(substr($feedback->user->name ?? 'A', 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">{{ $feedback->user->name ?? 'Anonyme' }}</p>
                                        <div class="flex items-center gap-1">
                                            @for($j = 1; $j <= 5; $j++)
                                                <svg class="w-3 h-3 {{ $j <= $feedback->rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                            @endfor
                                            <span class="text-xs text-gray-400 ml-2">{{ $feedback->created_at->format('d M Y à H:i') }}</span>
                                        </div>
                                    </div>
                                </div>
                                <span class="bg-white border shadow-sm px-2 py-0.5 rounded text-[10px] uppercase font-bold tracking-wider text-gray-500">
                                    {{ $feedback->user->user_type ?? 'N/C' }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-700 italic mt-3 bg-white p-3 rounded-lg border border-gray-100">
                                "{{ $feedback->comment }}"
                            </p>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center h-32 text-gray-400 bg-gray-50 rounded-xl border border-dashed border-gray-200">
                            <svg class="w-8 h-8 mb-2 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                            <p class="text-sm">Aucun commentaire textuel reçu sur cette période</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script nonce="{{ request()->attributes->get('csp_nonce') }}" src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    // Graphique d'évolution
    const ctx = document.getElementById('evolutionChart').getContext('2d');
    const dailySignups = @json($stats['daily_signups']);
    const dailyTests = @json($stats['daily_tests']);
    const dailyMessages = @json($stats['daily_messages']);

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: Object.keys(dailySignups),
            datasets: [
                {
                    label: 'Inscriptions',
                    data: Object.values(dailySignups),
                    borderColor: '#3B82F6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Tests MBTI',
                    data: Object.values(dailyTests),
                    borderColor: '#8B5CF6',
                    backgroundColor: 'rgba(139, 92, 246, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Messages',
                    data: Object.values(dailyMessages),
                    borderColor: '#10B981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.4,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                x: {
                    display: true,
                    grid: {
                        display: false
                    },
                    ticks: {
                        maxTicksLimit: 10
                    }
                },
                y: {
                    display: true,
                    beginAtZero: true,
                    grid: {
                        color: '#f3f4f6'
                    }
                }
            }
        }
    });

    // Chart Tuition
    const tuiCtx = document.getElementById('tuitionChart').getContext('2d');
    const tuitions = @json($stats['youth_engagement']['tuition_ranges']);
    const tuiLabels = {
        'under_200': '- 200k',
        '200_500': '200k-500k',
        '500_1m': '500k-1M',
        '1m_2m': '1M-2M',
        'over_2m': '+ 2M',
        'non_renseigne': 'N/C'
    };
    const tuiData = ['under_200', '200_500', '500_1m', '1m_2m', 'over_2m', 'non_renseigne'];

    new Chart(tuiCtx, {
        type: 'pie',
        data: {
            labels: tuiData.map(k => tuiLabels[k]),
            datasets: [{
                data: tuiData.map(k => tuitions[k] || 0),
                backgroundColor: ['#6366F1', '#818CF8', '#A5B4FC', '#C7D2FE', '#E0E7FF', '#F3F4F6'],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { 
                legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } }
            }
        }
    });

    // Chart Salary (Expectations/Target)
    const salCtx = document.getElementById('salaryChart').getContext('2d');
    const salaries = @json($stats['youth_engagement']['target_salary_ranges']);
    const salLabels = {
        'under_50': '- 50k',
        '50_100': '50k-100k',
        '100_250': '100k-250k',
        '250_500': '250k-500k',
        '500_1m': '500k-1M',
        '1m_3m': '1M-3M',
        'over_3m': '+ 3M',
        'non_renseigne': 'N/C'
    };
    const salData = ['under_50', '50_100', '100_250', '250_500', '500_1m', '1m_3m', 'over_3m', 'non_renseigne'];

    new Chart(salCtx, {
        type: 'bar',
        data: {
            labels: salData.map(k => salLabels[k]),
            datasets: [{
                label: 'Candidats',
                data: salData.map(k => salaries[k] || 0),
                backgroundColor: '#10B981',
                borderRadius: 6,
                barThickness: 20
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { precision: 0 } },
                x: { grid: { display: false }, ticks: { font: { size: 9 } } }
            }
        }
    });

    // Chart Salary Actual (Employed)
    const actualSalCtx = document.getElementById('actualSalaryChart').getContext('2d');
    const actualSalaries = @json($stats['youth_engagement']['actual_salary_ranges']);

    new Chart(actualSalCtx, {
        type: 'bar',
        data: {
            labels: salData.map(k => salLabels[k]),
            datasets: [{
                label: 'Salariés',
                data: salData.map(k => actualSalaries[k] || 0),
                backgroundColor: '#3B82F6',
                borderRadius: 6,
                barThickness: 20
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { precision: 0 } },
                x: { grid: { display: false }, ticks: { font: { size: 9 } } }
            }
        }
    });

    // Chart Situation
    const sitCtx = document.getElementById('situationChart').getContext('2d');
    const situations = @json($stats['youth_engagement']['situations']);
    const sitLabels = {
        'college': 'Collège',
        'lycee': 'Lycée',
        'etudiant': 'Université',
        'recherche_emploi': 'En recherche',
        'emploi': 'En poste',
        'entrepreneur': 'Entrepreneur',
        'non_renseigne': 'N/C'
    };

    new Chart(sitCtx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(situations).map(k => sitLabels[k] || k),
            datasets: [{
                data: Object.values(situations),
                backgroundColor: ['#3B82F6', '#60A5FA', '#93C5FD', '#F59E0B', '#EF4444', '#10B981', '#94A3B8'],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: { 
                legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } }
            }
        }
    });

    // Chart Sources
    const srcCtx = document.getElementById('sourceChart').getContext('2d');
    const sources = @json($stats['youth_engagement']['sources']);
    const srcLabels = {
        'social_media': 'Réseaux',
        'friend': 'Ami',
        'school': 'École',
        'search': 'Google',
        'event': 'Event',
        'other': 'Autre',
        'non_renseigne': 'N/C'
    };

    new Chart(srcCtx, {
        type: 'pie',
        data: {
            labels: Object.keys(sources).map(k => srcLabels[k] || k),
            datasets: [{
                data: Object.values(sources),
                backgroundColor: ['#6366F1', '#EC4899', '#FACC15', '#14B8A6', '#F97316', '#94A3B8', '#D1D5DB'],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { 
                legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } }
            }
        }
    });
</script>
@endpush
@endsection