@extends('layouts.organization')

@section('title', 'Créer une relation de mentorat')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('organization.mentorships.index') }}" class="p-2 hover:bg-gray-100 rounded-full transition">
            <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Nouvelle relation de mentorat</h1>
            <p class="text-sm text-gray-700">Liez manuellement un jeune parrainé avec un mentor de votre organisation.
            </p>
        </div>
    </div>

    <form action="{{ route('organization.mentorships.store') }}" method="POST"
        class="bg-white shadow rounded-lg overflow-hidden">
        @csrf
        <div class="p-6 space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Select Mentee -->
                <div class="space-y-4">
                    <label class="block text-sm font-semibold text-gray-900">1. Sélectionner le jeune parrainé <span
                            class="text-red-500">*</span></label>
                    <p class="text-xs text-gray-500">Seuls les jeunes n'ayant pas de relation active s'affichent ici.
                    </p>

                    <div class="relative">
                        <select name="mentee_id" id="mentee_id" required
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">Choisir un jeune...</option>
                            @foreach($jeunes as $jeune)
                            <option value="{{ $jeune->id }}" {{ old('mentee_id')==$jeune->id ? 'selected' : '' }}>
                                {{ $jeune->name }} ({{ $jeune->email }})
                            </option>
                            @endforeach
                        </select>
                        @error('mentee_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Select Mentor -->
                <div class="space-y-4">
                    <label class="block text-sm font-semibold text-gray-900">2. Sélectionner le mentor <span
                            class="text-red-500">*</span></label>
                    <p class="text-xs text-gray-500">Liste des mentors liés à votre organisation.</p>

                    <div class="relative">
                        <select name="mentor_id" id="mentor_id" required
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">Choisir un mentor...</option>
                            @foreach($mentors as $mentor)
                            <option value="{{ $mentor->id }}" {{ old('mentor_id')==$mentor->id ? 'selected' : '' }}>
                                {{ $mentor->name }} - {{ $mentor->mentorProfile->current_position ?? 'N/A' }}
                            </option>
                            @endforeach
                        </select>
                        @error('mentor_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="bg-indigo-50 p-4 rounded-lg flex items-start gap-3">
                <svg class="w-6 h-6 text-indigo-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="text-sm text-indigo-800">
                    <p class="font-bold">Informations importantes :</p>
                    <ul class="list-disc list-inside mt-1 space-y-1">
                        <li>La relation sera créée immédiatement avec le statut <strong>"Accepté"</strong>.</li>
                        <li>Un email de notification sera envoyé automatiquement aux deux parties.</li>
                        <li>Ils pourront commencer à échanger par message dès la validation.</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="px-6 py-4 bg-gray-50 text-right border-t border-gray-200">
            <a href="{{ route('organization.mentorships.index') }}"
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-500 mr-4">
                Annuler
            </a>
            <button type="submit"
                class="inline-flex justify-center py-2 px-6 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
                Confirmer la création
            </button>
        </div>
    </form>
</div>
@endsection