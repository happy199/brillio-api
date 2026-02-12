@extends('layouts.admin')

@section('title', 'Détails Séance de Mentorat')
@section('header', 'Détails Séance de Mentorat')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Navigation -->
        <a href="{{ route('admin.mentorship.sessions') }}"
            class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                </path>
            </svg>
            Retour aux séances
        </a>

        <!-- Header Stats -->
        <div
            class="bg-white rounded-lg shadow-md p-6 border-l-4 {{ $session->status === 'cancelled' ? 'border-red-500' : ($session->status === 'completed' ? 'border-blue-500' : 'border-green-500') }} flex justify-between items-start">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $session->title }}</h1>
                <p class="text-gray-500 flex items-center mt-1">
                    <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                        </path>
                    </svg>
                    {{ $session->scheduled_at->format('l d F Y à H:i') }} ({{ $session->duration_minutes }} min)
                </p>
            </div>
            <div class="text-right">
                <span class="px-3 py-1 text-sm font-bold rounded-full 
                    {{ $session->status === 'cancelled' ? 'bg-red-100 text-red-800' :
        ($session->status === 'completed' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
                    {{ ucfirst($session->status) }}
                </span>
                <div class="mt-2 text-sm text-gray-500">
                    Type:
                    @if($session->is_paid)
                        <span class="font-bold text-purple-600">Payant ({{ number_format($session->price, 0) }} FCFA)</span>
                    @else
                        <span class="font-bold text-green-600">Gratuit</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Participants -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Mentor -->
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <h3 class="font-bold text-gray-500 text-xs uppercase tracking-wide mb-4">Mentor</h3>
                <div class="flex items-center space-x-4">
                    <img src="{{ $session->mentor->avatar_url }}" alt="" class="w-12 h-12 rounded-full object-cover">
                    <div>
                        <p class="font-bold text-gray-900">{{ $session->mentor->name }}</p>
                        <a href="{{ route('admin.mentors.show', $session->mentor) }}"
                            class="text-xs text-indigo-600 hover:underline">Voir Profil</a>
                    </div>
                </div>
            </div>

            <!-- Mentees -->
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <h3 class="font-bold text-gray-500 text-xs uppercase tracking-wide mb-4">Participants (Mentés)</h3>
                <div class="space-y-3">
                    @foreach($session->mentees as $mentee)
                        <div class="flex items-center space-x-4">
                            <img src="{{ $mentee->avatar_url }}" alt="" class="w-12 h-12 rounded-full object-cover">
                            <div>
                                <p class="font-bold text-gray-900">{{ $mentee->name }}</p>
                                <a href="{{ route('admin.users.show', $mentee) }}"
                                    class="text-xs text-indigo-600 hover:underline">Voir Profil</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Description & Report -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                <h3 class="font-bold text-gray-900">Information & Compte Rendu</h3>
            </div>
            <div class="p-6 space-y-6">
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Description de la séance</h4>
                    <p class="mt-1 text-gray-900">{{ $session->description ?? 'Aucune description.' }}</p>
                </div>

                <div class="border-t border-gray-100 pt-4">
                    <h4 class="text-sm font-medium text-gray-500">Compte Rendu (Mentor)</h4>
                    @if($session->report_content)
                        <div class="mt-2 bg-gray-50 p-4 rounded-lg text-gray-800 whitespace-pre-line">
                            {{ $session->report_content }}
                        </div>
                    @else
                        <p class="mt-1 text-gray-400 italic">Aucun compte rendu soumis pour le moment.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection