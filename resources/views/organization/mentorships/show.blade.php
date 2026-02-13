@extends('layouts.organization')

@section('title', 'Détails du Mentorat')

@section('content')
<div class="space-y-6">
    <div class="flex items-center space-x-4">
        <a href="{{ route('organization.mentorships.index') }}"
            class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700">
            <svg class="mr-1 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Retour à la liste
        </a>
        <h1 class="text-2xl font-bold text-gray-900">Détails du Mentorat</h1>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Mentee Info -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-medium text-gray-900">Élève (Menté)</h3>
            </div>
            <div class="p-6 text-center">
                @if($mentorship->mentee->avatar_url)
                <img class="h-24 w-24 rounded-full object-cover mx-auto" src="{{ $mentorship->mentee->avatar_url }}"
                    alt="">
                @else
                <div
                    class="h-24 w-24 rounded-full bg-organization-100 flex items-center justify-center text-organization-600 font-bold text-3xl mx-auto">
                    {{ substr($mentorship->mentee->name, 0, 1) }}
                </div>
                @endif
                <h4 class="mt-4 text-xl font-bold text-gray-900">{{ $mentorship->mentee->name }}</h4>
                <p class="text-sm text-gray-500">{{ $mentorship->mentee->email }}</p>
                <div class="mt-4">
                    <a href="{{ route('organization.users.show', $mentorship->mentee) }}"
                        class="text-sm font-medium text-organization-600 hover:text-organization-500">
                        Voir profil complet
                    </a>
                </div>
            </div>
        </div>

        <!-- Relationship Info -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-medium text-gray-900">Relation de Mentorat</h3>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <span class="block text-sm font-medium text-gray-500">Statut</span>
                    <span class="mt-1 px-2.5 py-0.5 rounded-full text-sm font-semibold 
                        @if($mentorship->status === 'accepted') bg-green-100 text-green-800 
                        @elseif($mentorship->status === 'pending') bg-yellow-100 text-yellow-800
                        @elseif($mentorship->status === 'refused') bg-red-100 text-red-800
                        @else bg-gray-100 text-gray-800 @endif">
                        @switch($mentorship->status)
                        @case('accepted') Actif / Accepté @break
                        @case('pending') En attente @break
                        @case('refused') Refusé @break
                        @case('disconnected') Terminé @break
                        @default {{ $mentorship->status }}
                        @endswitch
                    </span>
                </div>
                <div>
                    <span class="block text-sm font-medium text-gray-500">Date de début</span>
                    <p class="mt-1 text-sm text-gray-900">{{ $mentorship->created_at->format('d/m/Y') }}</p>
                </div>
                @if($mentorship->request_message)
                <div>
                    <span class="block text-sm font-medium text-gray-500">Message de demande</span>
                    <p class="mt-1 text-sm text-gray-900 italic">"{{ $mentorship->request_message }}"</p>
                </div>
                @endif
                @if($mentorship->refusal_reason)
                <div class="p-3 bg-red-50 rounded-md">
                    <span class="block text-sm font-medium text-red-800">Raison du refus</span>
                    <p class="mt-1 text-sm text-red-700">{{ $mentorship->refusal_reason }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Mentor Info -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-medium text-gray-900">Mentor</h3>
            </div>
            <div class="p-6 text-center">
                @if($mentorship->mentor->avatar_url)
                <img class="h-24 w-24 rounded-full object-cover mx-auto" src="{{ $mentorship->mentor->avatar_url }}"
                    alt="">
                @else
                <div
                    class="h-24 w-24 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-3xl mx-auto">
                    {{ substr($mentorship->mentor->name, 0, 1) }}
                </div>
                @endif
                <h4 class="mt-4 text-xl font-bold text-gray-900">{{ $mentorship->mentor->name }}</h4>
                <p class="text-sm text-gray-500">Mentor Certifié</p>
            </div>
        </div>
    </div>
</div>
@endsection