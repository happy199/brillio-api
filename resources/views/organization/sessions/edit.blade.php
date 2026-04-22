@extends('layouts.organization')

@section('title', 'Modifier la séance')

@section('content')
@php
    $allMentorIds = array_merge([$session->mentor_id], $session->additionalMentors->pluck('id')->toArray());
    $menteeIds = $session->mentees->pluck('id')->toArray();
    $isGuest = $session->mentor->is_guest;
@endphp

<div class="max-w-4xl mx-auto" x-data="{ 
    instructorType: '{{ $isGuest ? 'guest' : 'mentor' }}',
    selectedMentors: {{ json_encode($allMentorIds) }},
    selectedMentees: {{ json_encode($menteeIds) }}
}">
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Modifier la séance</h1>
            <p class="mt-2 text-lg text-gray-600">Mettez à jour les détails de votre session de mentorat.</p>
        </div>
        <a href="{{ route('organization.sessions.show', $session) }}" class="text-organization-600 hover:text-organization-700 font-semibold flex items-center transition-colors">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Retour aux détails
        </a>
    </div>

    <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">
        <form action="{{ route('organization.sessions.update', $session) }}" method="POST" class="p-8 space-y-8">
            @csrf
            @method('PUT')

            <!-- Section 1: Détails de la séance -->
            <div class="space-y-6">
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label for="title" class="block text-sm font-bold text-gray-700 mb-2">Titre de la session <span class="text-red-500">*</span></label>
                        <input type="text" name="title" id="title" required
                            value="{{ old('title', $session->title) }}"
                            placeholder="Ex: Masterclass sur l'IA, Atelier CV..."
                            class="w-full bg-gray-50 border-gray-200 rounded-xl focus:ring-organization-500 focus:border-organization-500 p-4 transition-all shadow-sm">
                        @error('title') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-bold text-gray-700 mb-2">Description / Objectifs</label>
                        <textarea name="description" id="description" rows="3"
                            placeholder="Quel est l'objectif de cette séance ?"
                            class="w-full bg-gray-50 border-gray-200 rounded-xl focus:ring-organization-500 focus:border-organization-500 p-4 transition-all shadow-sm">{{ old('description', $session->description) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Section 2: Type d'intervenant -->
                <div class="space-y-4">
                    <label class="block text-sm font-bold text-gray-700">Type d'intervenant <span class="text-red-500">*</span></label>
                    <div class="flex p-1 bg-gray-100 rounded-xl">
                        <button type="button" 
                            @click="instructorType = 'mentor'"
                            :class="instructorType === 'mentor' ? 'bg-white shadow-sm text-organization-600' : 'text-gray-500 hover:text-gray-700'"
                            class="flex-1 py-2 text-sm font-bold rounded-lg transition-all">
                            Mentors classiques
                        </button>
                        <button type="button" 
                            @click="instructorType = 'guest'"
                            :class="instructorType === 'guest' ? 'bg-white shadow-sm text-organization-600' : 'text-gray-500 hover:text-gray-700'"
                            class="flex-1 py-2 text-sm font-bold rounded-lg transition-all">
                            Formateurs / Invités
                        </button>
                    </div>
                    <input type="hidden" name="instructor_type" :value="instructorType">
                </div>

                <!-- Section 3: Date & Temps -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2 sm:col-span-1">
                        <label for="scheduled_at" class="block text-sm font-bold text-gray-700 mb-2">Date et Heure <span class="text-red-500">*</span></label>
                        <input type="datetime-local" name="scheduled_at" id="scheduled_at" required
                            value="{{ old('scheduled_at', $session->scheduled_at->format('Y-m-d\TH:i')) }}"
                            min="{{ now()->format('Y-m-d\TH:i') }}"
                            class="w-full bg-gray-50 border-gray-200 rounded-xl focus:ring-organization-500 focus:border-organization-500 p-4 shadow-sm transition-all">
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <label for="duration_minutes" class="block text-sm font-bold text-gray-700 mb-2">Durée <span class="text-red-500">*</span></label>
                        <select name="duration_minutes" id="duration_minutes" required
                            class="w-full bg-gray-50 border-gray-200 rounded-xl focus:ring-organization-500 focus:border-organization-500 p-4 shadow-sm">
                            <option value="30" {{ $session->duration_minutes == 30 ? 'selected' : '' }}>30 min</option>
                            <option value="45" {{ $session->duration_minutes == 45 ? 'selected' : '' }}>45 min</option>
                            <option value="60" {{ $session->duration_minutes == 60 ? 'selected' : '' }}>1 heure</option>
                            <option value="90" {{ $session->duration_minutes == 90 ? 'selected' : '' }}>1h 30min</option>
                            <option value="120" {{ $session->duration_minutes == 120 ? 'selected' : '' }}>2 heures</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Section 4: Sélection des Intervenants (Dynamique) -->
            <div class="space-y-4">
                <label class="block text-sm font-bold text-gray-700">
                    Sélectionner <span x-text="instructorType === 'mentor' ? 'les mentors' : 'les invités'"></span> <span class="text-red-500">*</span>
                </label>
                
                <!-- Grille Mentors -->
                <div x-show="instructorType === 'mentor'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 max-h-72 overflow-y-auto p-4 bg-gray-50 rounded-2xl border border-gray-100 shadow-inner">
                    @foreach($standardMentors as $mentor)
                        <label class="relative flex items-center p-4 bg-white rounded-xl border border-gray-200 cursor-pointer hover:border-organization-200 hover:bg-organization-50 transition-all group overflow-hidden">
                            <input type="checkbox" name="mentor_ids[]" value="{{ $mentor->id }}" 
                                x-model="selectedMentors" 
                                :checked="selectedMentors.includes({{ $mentor->id }})"
                                class="h-5 w-5 text-organization-600 focus:ring-organization-500 border-gray-300 rounded transition-all">
                            <div class="ml-4 flex items-center">
                                <div class="h-10 w-10 rounded-full bg-organization-100 flex items-center justify-center text-organization-600 font-bold mr-3 border-2 border-white shadow-sm overflow-hidden">
                                    @if($mentor->avatar_url)
                                        <img src="{{ $mentor->avatar_url }}" class="h-full w-full object-cover">
                                    @else
                                        {{ substr($mentor->name, 0, 1) }}
                                    @endif
                                </div>
                                <span class="text-sm font-semibold text-gray-700 group-hover:text-organization-700 transition-colors">{{ $mentor->name }}</span>
                            </div>
                        </label>
                    @endforeach
                </div>

                <!-- Grille Invités -->
                <div x-show="instructorType === 'guest'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 max-h-72 overflow-y-auto p-4 bg-gray-50 rounded-2xl border border-gray-100 shadow-inner">
                    @foreach($guestMentors as $guest)
                        <label class="relative flex items-center p-4 bg-white rounded-xl border border-gray-200 cursor-pointer hover:border-indigo-200 hover:bg-indigo-50 transition-all group overflow-hidden">
                            <input type="checkbox" name="mentor_ids[]" value="{{ $guest->id }}" 
                                x-model="selectedMentors" 
                                :checked="selectedMentors.includes({{ $guest->id }})"
                                class="h-5 w-5 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded transition-all">
                            <div class="ml-4 flex items-center">
                                <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold mr-3 border-2 border-white shadow-sm overflow-hidden">
                                    @if($guest->avatar_url)
                                        <img src="{{ $guest->avatar_url }}" class="h-full w-full object-cover">
                                    @else
                                        {{ substr($guest->name, 0, 1) }}
                                    @endif
                                </div>
                                <span class="text-sm font-semibold text-gray-700 group-hover:text-indigo-700 transition-colors">{{ $guest->name }}</span>
                            </div>
                        </label>
                    @endforeach
                </div>
                @error('mentor_ids') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Section 5: Participants (Jeunes) -->
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-4">Sélectionner les jeunes participants <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 max-h-72 overflow-y-auto p-4 bg-gray-50 rounded-2xl border border-gray-100 shadow-inner">
                    @foreach($mentees as $mentee)
                        <label class="relative flex items-center p-4 bg-white rounded-xl border border-gray-200 cursor-pointer hover:border-organization-200 hover:bg-organization-50 transition-all group overflow-hidden">
                            <input type="checkbox" name="mentee_ids[]" value="{{ $mentee->id }}" 
                                x-model="selectedMentees"
                                :checked="selectedMentees.includes({{ $mentee->id }})"
                                class="h-5 w-5 text-organization-600 focus:ring-organization-500 border-gray-300 rounded transition-all">
                            <div class="ml-4 flex items-center">
                                <div class="h-10 w-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 font-bold mr-3 border-2 border-white shadow-sm overflow-hidden">
                                    @if($mentee->avatar_url)
                                        <img src="{{ $mentee->avatar_url }}" class="h-full w-full object-cover">
                                    @else
                                        {{ substr($mentee->name, 0, 1) }}
                                    @endif
                                </div>
                                <span class="text-sm font-semibold text-gray-700 group-hover:text-organization-700 transition-colors">{{ $mentee->name }}</span>
                            </div>
                        </label>
                    @endforeach
                </div>
                @error('mentee_ids') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end space-x-4 pt-8 border-t border-gray-100">
                <a href="{{ route('organization.sessions.show', $session) }}" 
                    class="px-8 py-4 text-sm font-bold text-gray-500 hover:text-gray-700 transition-colors">
                    Annuler
                </a>
                <button type="submit" 
                    class="px-10 py-4 bg-organization-600 text-white font-extrabold rounded-2xl hover:bg-organization-700 transition-all shadow-xl hover:shadow-2xl hover:-translate-y-0.5 active:translate-y-0 active:scale-95 flex items-center">
                    Mettre à jour la séance
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
