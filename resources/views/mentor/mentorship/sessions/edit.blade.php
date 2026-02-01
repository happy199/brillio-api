@extends('layouts.mentor')

@section('title', 'Modifier la séance')

@section('content')
    <div class="max-w-2xl mx-auto space-y-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('mentor.mentorship.sessions.show', $session) }}"
                class="p-2 hover:bg-gray-100 rounded-full transition text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                    </path>
                </svg>
            </a>
            <h1 class="text-2xl font-bold text-gray-900">Modifier la séance</h1>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
            <form action="{{ route('mentor.mentorship.sessions.update', $session) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Titre -->
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Titre de la séance</label>
                    <input type="text" name="title" id="title" required value="{{ old('title', $session->title) }}"
                        class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    @error('title') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" id="description" rows="3"
                        class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('description', $session->description) }}</textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Date et Heure -->
                    <div>
                        <label for="scheduled_at" class="block text-sm font-medium text-gray-700 mb-1">Date et Heure</label>
                        <input type="datetime-local" name="scheduled_at" id="scheduled_at" required
                            value="{{ old('scheduled_at', $session->scheduled_at->format('Y-m-d\TH:i')) }}"
                            class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        @error('scheduled_at') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <!-- Durée -->
                    <div>
                        <label for="duration_minutes" class="block text-sm font-medium text-gray-700 mb-1">Durée
                            (minutes)</label>
                        <select name="duration_minutes" id="duration_minutes" required
                            class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="15" {{ $session->duration_minutes == 15 ? 'selected' : '' }}>15 min</option>
                            <option value="30" {{ $session->duration_minutes == 30 ? 'selected' : '' }}>30 min</option>
                            <option value="45" {{ $session->duration_minutes == 45 ? 'selected' : '' }}>45 min</option>
                            <option value="60" {{ $session->duration_minutes == 60 ? 'selected' : '' }}>1 heure</option>
                            <option value="90" {{ $session->duration_minutes == 90 ? 'selected' : '' }}>1h 30</option>
                            <option value="120" {{ $session->duration_minutes == 120 ? 'selected' : '' }}>2 heures</option>
                        </select>
                    </div>
                </div>

                <!-- Prix -->
                @if($session->is_paid)
                    <div>
                        <label for="price" class="block text-sm font-medium text-gray-700 mb-1">Prix (FCFA)</label>
                        <div class="relative rounded-md shadow-sm">
                            <input type="number" name="price" id="price" required min="500" step="100"
                                value="{{ old('price', (int) $session->price) }}"
                                class="w-full border-gray-300 rounded-lg pr-12 focus:ring-indigo-500 focus:border-indigo-500">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">FCFA</span>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">
                            Attention : Modifier le prix ne demandera pas un nouveau paiement si la séance est déjà
                            payée/confirmée.
                        </p>
                    </div>
                @endif

                <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-100">
                    <a href="{{ route('mentor.mentorship.sessions.show', $session) }}"
                        class="text-gray-600 hover:text-gray-800 font-medium px-4 py-2">
                        Annuler
                    </a>
                    <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg font-bold transition shadow-md shadow-indigo-100">
                        Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection