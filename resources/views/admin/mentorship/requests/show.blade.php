@extends('layouts.admin')

@section('title', 'Détails Demande Mentorat')
@section('header', 'Détails Demande Mentorat')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Navigation -->
        <a href="{{ route('admin.mentorship.requests') }}"
            class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                </path>
            </svg>
            Retour aux demandes
        </a>

        <!-- Main Card -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Demande #{{ $mentorship->id }}</h2>
                    <p class="text-sm text-gray-500">Créée le {{ $mentorship->created_at->format('d/m/Y à H:i') }}</p>
                </div>
                <span
                    class="px-3 py-1 text-sm font-bold rounded-full 
                    {{ $mentorship->status === 'accepted' ? 'bg-green-100 text-green-800' :
        ($mentorship->status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                    {{ ucfirst($mentorship->status) }}
                </span>
            </div>

            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Mentor Info -->
                <div>
                    <h3 class="font-bold text-lg text-gray-900 mb-4 border-b pb-2">Mentor</h3>
                    <div class="flex items-center space-x-4">
                        <img src="{{ $mentorship->mentor->avatar_url }}" alt="" class="w-16 h-16 rounded-full object-cover">
                        <div>
                            <p class="font-bold text-gray-900">{{ $mentorship->mentor->name }}</p>
                            <p class="text-sm text-gray-500">{{ $mentorship->mentor->email }}</p>
                            <a href="{{ route('admin.mentors.show', $mentorship->mentor) }}"
                                class="text-xs text-indigo-600 hover:underline">Voir Profil Admin</a>
                        </div>
                    </div>
                </div>

                <!-- Mentee Info -->
                <div>
                    <h3 class="font-bold text-lg text-gray-900 mb-4 border-b pb-2">Jeune (Menté)</h3>
                    <div class="flex items-center space-x-4">
                        <img src="{{ $mentorship->mentee->avatar_url }}" alt="" class="w-16 h-16 rounded-full object-cover">
                        <div>
                            <p class="font-bold text-gray-900">{{ $mentorship->mentee->name }}</p>
                            <p class="text-sm text-gray-500">{{ $mentorship->mentee->email }}</p>
                            <a href="{{ route('admin.users.show', $mentorship->mentee) }}"
                                class="text-xs text-indigo-600 hover:underline">Voir Profil Admin</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Message/Motivation -->
            <div class="p-6 bg-gray-50 border-t border-gray-200">
                <h3 class="font-bold text-gray-900 mb-2">Message de motivation</h3>
                <div class="bg-white p-4 rounded border border-gray-200 text-gray-700 italic">
                    "{{ $mentorship->message }}"
                </div>
            </div>

            <!-- Actions -->
            @if($mentorship->status === 'pending')
                <div class="p-6 border-t border-gray-200 bg-gray-50 flex justify-end gap-3">
                    <span class="text-sm text-gray-500 italic">Cette demande est en attente d'action de la part du
                        mentor.</span>
                </div>
            @endif
        </div>
    </div>
@endsection