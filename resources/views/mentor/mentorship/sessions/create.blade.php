@extends('layouts.mentor')

@section('title', 'Planifier une séance')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-3xl mx-auto">
            <div class="mb-8">
                <a href="{{ route('mentor.mentorship.index') }}"
                    class="text-sm text-gray-500 hover:text-indigo-600 flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Retour aux mentés
                </a>
                <h1 class="text-2xl font-bold text-gray-900">Nouvelle Session de Mentorat</h1>
                <p class="text-gray-500 mt-1">Planifiez une séance vidéo avec un ou plusieurs mentés.</p>
            </div>

            @if($mentees->isEmpty())
                <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6 text-center">
                    <div class="text-yellow-800 font-medium mb-2">Aucun menté actif</div>
                    <p class="text-yellow-600 text-sm mb-4">Vous devez avoir au moins un menté actif (demande acceptée) pour
                        planifier une séance.</p>
                    <a href="{{ route('mentor.mentorship.index') }}"
                        class="inline-block bg-yellow-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-yellow-700">Gérer
                        mes demandes</a>
                </div>
            @else
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 md:p-8">
                    <form action="{{ route('mentor.mentorship.sessions.store') }}" method="POST" x-data="{ isPaid: false }">
                        @csrf

                        <!-- Title -->
                        <div class="mb-6">
                            <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Titre de la séance <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="title" id="title"
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-3"
                                placeholder="Ex: Point hebdomadaire, Revue de projet..." required>
                        </div>

                        <!-- Description -->
                        <div class="mb-6">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description / Ordre du
                                jour</label>
                            <textarea name="description" id="description" rows="3"
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-3"
                                placeholder="Détails optionnels..."></textarea>
                        </div>

                        <!-- Participants -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Participants (Mentés) <span
                                    class="text-red-500">*</span></label>
                            <div
                                class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-48 overflow-y-auto border border-gray-200 rounded-lg p-3">
                                @foreach($mentees as $mentee)
                                    <label class="flex items-center gap-3 p-2 hover:bg-gray-50 rounded-lg cursor-pointer">
                                        <input type="checkbox" name="mentee_ids[]" value="{{ $mentee->id }}"
                                            class="rounded text-indigo-600 focus:ring-indigo-500 border-gray-300 h-4 w-4">
                                        <div class="flex items-center gap-3">
                                            <img src="{{ $mentee->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($mentee->name) }}"
                                                class="w-8 h-8 rounded-full bg-gray-200">
                                            <span class="text-gray-900 font-medium text-sm">{{ $mentee->name }}</span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                            <p class="text-xs text-gray-500 mt-2">Sélectionnez au moins une personne.</p>
                        </div>

                        <!-- Date & Time -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label for="scheduled_at" class="block text-sm font-medium text-gray-700 mb-2">Date et Heure
                                    <span class="text-red-500">*</span></label>
                                <input type="datetime-local" name="scheduled_at" id="scheduled_at"
                                    class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-3"
                                    min="{{ now()->format('Y-m-d\TH:i') }}" required>
                            </div>
                            <div>
                                <label for="duration_minutes" class="block text-sm font-medium text-gray-700 mb-2">Durée
                                    (minutes) <span class="text-red-500">*</span></label>
                                <select name="duration_minutes" id="duration_minutes"
                                    class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-3">
                                    <option value="30">30 min</option>
                                    <option value="45">45 min</option>
                                    <option value="60" selected>1h (60 min)</option>
                                    <option value="90">1h30 (90 min)</option>
                                    <option value="120">2h (120 min)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Payment -->
                        <div class="mb-8 bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <div class="flex items-center justify-between mb-4">
                                <label class="flex items-center cursor-pointer">
                                    <div class="relative">
                                        <input type="checkbox" name="is_paid" value="1" class="sr-only" x-model="isPaid">
                                        <div class="w-10 h-6 bg-gray-200 rounded-full shadow-inner transition"
                                            :class="{'bg-indigo-600': isPaid}"></div>
                                        <div class="dot absolute w-4 h-4 bg-white rounded-full shadow left-1 top-1 transition transform"
                                            :class="{'translate-x-full': isPaid}"></div>
                                    </div>
                                    <span class="ml-3 text-sm font-medium text-gray-900">Séance payante ?</span>
                                </label>
                            </div>

                            <div x-show="isPaid" x-cloak class="transition">
                                <label for="price" class="block text-sm font-medium text-gray-700 mb-2">Prix de la séance (FCFA)
                                    <span class="text-red-500">*</span></label>
                                <div class="relative rounded-md shadow-sm max-w-xs">
                                    <input type="number" name="price" id="price" min="500" step="100"
                                        class="w-full border-gray-300 rounded-lg focus:border-indigo-500 focus:ring-indigo-500 pr-12 p-3"
                                        placeholder="5000">
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">FCFA</span>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500 mt-2">
                                    <span class="font-bold text-indigo-600">Note:</span> Une commission de 10% sera appliquée.
                                    Vous recevrez 90% du montant.
                                </p>
                            </div>
                        </div>

                        <!-- Submit -->
                        <div class="flex justify-end gap-3 pt-6 border-t border-gray-100">
                            <a href="{{ route('mentor.mentorship.calendar') }}"
                                class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 px-6 py-2.5 rounded-lg font-medium transition">
                                Annuler
                            </a>
                            <button type="submit"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-lg font-medium transition shadow-sm">
                                Planifier la séance
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>
@endsection