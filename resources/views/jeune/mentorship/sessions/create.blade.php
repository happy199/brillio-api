@extends('layouts.jeune')

@section('title', 'Réserver une séance')

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="mb-8">
            <a href="{{ route('jeune.mentorship.index') }}"
                class="text-sm text-gray-500 hover:text-indigo-600 flex items-center mb-4">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Retour à mes mentors
            </a>
            <h1 class="text-2xl font-bold text-gray-900">Réserver une séance avec {{ $mentor->name }}</h1>
            <p class="text-gray-500 mt-1">Choisissez un créneau qui vous convient.</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 md:p-8">
            <form action="{{ route('jeune.sessions.store') }}" method="POST">
                @csrf
                <input type="hidden" name="mentor_id" value="{{ $mentor->id }}">

                <!-- Title -->
                <div class="mb-6">
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Sujet de la séance <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="title" id="title"
                        class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="Ex: Revue de CV, Préparation entretien..." required>
                </div>

                <!-- Description -->
                <div class="mb-6">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description / Questions
                        (Optionnel)</label>
                    <textarea name="description" id="description" rows="3"
                        class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="Décrivez ce que vous attendez de cette séance..."></textarea>
                </div>

                <!-- Date & Time -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="scheduled_at" class="block text-sm font-medium text-gray-700 mb-2">Date et Heure <span
                                class="text-red-500">*</span></label>
                        <input type="datetime-local" name="scheduled_at" id="scheduled_at"
                            class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            required>
                        <p class="text-xs text-gray-500 mt-1">Vérifiez les disponibilités du mentor.</p>
                    </div>
                    <div>
                        <label for="duration_minutes" class="block text-sm font-medium text-gray-700 mb-2">Durée <span
                                class="text-red-500">*</span></label>
                        <select name="duration_minutes" id="duration_minutes"
                            class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="30">30 min</option>
                            <option value="45">45 min</option>
                            <option value="60" selected>1h (60 min)</option>
                            <option value="90">1h30 (90 min)</option>
                        </select>
                    </div>
                </div>

                <!-- Availabilities Info -->
                <div class="mb-8 bg-blue-50 rounded-lg p-4 border border-blue-100">
                    <h3 class="font-medium text-blue-900 mb-2">Disponibilités habituelles du mentor :</h3>
                    @if($availabilities->isEmpty())
                        <p class="text-sm text-blue-700">Ce mentor n'a pas renseigné de disponibilités spécifiques. Proposez un
                            créneau, il confirmera s'il est disponible.</p>
                    @else
                        <ul class="text-sm text-blue-700 list-disc list-inside space-y-1">
                            @foreach($availabilities as $availability)
                                @php
                                    $days = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
                                    $dayName = $availability->day_of_week !== null ? $days[$availability->day_of_week] : 'Date spécifique';
                                @endphp
                                <li>
                                    <strong>{{ $dayName }}</strong> : {{ substr($availability->start_time, 0, 5) }} -
                                    {{ substr($availability->end_time, 0, 5) }}
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                <!-- Submit -->
                <div class="flex justify-end gap-3 pt-6 border-t border-gray-100">
                    <a href="{{ route('jeune.mentors') }}"
                        class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 px-6 py-2.5 rounded-lg font-medium transition">
                        Annuler
                    </a>
                    <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-lg font-medium transition shadow-sm">
                        Envoyer la demande
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection