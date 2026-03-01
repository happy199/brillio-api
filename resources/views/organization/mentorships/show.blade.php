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

    <div class="relative min-h-[600px]">
        @if(!$organization->isPro())
        <div
            class="absolute inset-0 z-10 bg-white/60 backdrop-blur-[4px] rounded-lg flex flex-col items-center justify-center text-center p-8">
            <div class="bg-white p-8 rounded-xl shadow-2xl border border-gray-200 max-w-md sticky top-1/3">
                <div
                    class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-indigo-100 text-indigo-600 mb-6">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Fonctionnalité Pro</h3>
                <p class="text-gray-500 mb-8">
                    Les détails complets de la relation de mentorat sont réservés aux membres Pro.
                </p>
                <a href="{{ route('organization.subscriptions.index') }}"
                    class="inline-flex w-full justify-center items-center rounded-md bg-indigo-600 px-5 py-3 text-base font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-colors">
                    Passer au plan Pro
                </a>
            </div>
        </div>
        @endif

        <div
            class="grid grid-cols-1 gap-6 lg:grid-cols-3 {{ !$organization->isPro() ? 'filter blur-[6px] select-none pointer-events-none opacity-50' : '' }}">
            <!-- Mentee Info -->
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-medium text-gray-900">Élève (Menté)</h3>
                </div>
                <div class="p-6 text-center">
                    @if($mentorship->mentee && $mentorship->mentee->avatar_url)
                    <img class="h-24 w-24 rounded-full object-cover mx-auto" src="{{ $mentorship->mentee->avatar_url }}"
                        alt="">
                    @else
                    <div
                        class="h-24 w-24 rounded-full bg-organization-100 flex items-center justify-center text-organization-600 font-bold text-3xl mx-auto">
                        {{ substr($mentorship->mentee->name ?? 'U', 0, 1) }}
                    </div>
                    @endif
                    <h4 class="mt-4 text-xl font-bold text-gray-900">{{ $mentorship->mentee->name ?? 'Utilisateur' }}
                    </h4>
                    <p class="text-sm text-gray-500">{{ $mentorship->mentee->email ?? 'Email caché' }}</p>
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
                            @if($mentorship->status === 'accepted') bg-organization-100 text-organization-800 
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

                    @if($mentorship->status === 'accepted')
                    <div class="pt-4 border-t border-gray-100">
                        <button type="button"
                            onclick="openTerminateModal('{{ route('organization.mentorships.terminate', $mentorship) }}', '{{ $mentorship->mentee->name }}', '{{ $mentorship->mentor->name }}')"
                            class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Terminer la relation
                        </button>
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
                    @if($mentorship->mentor && $mentorship->mentor->avatar_url)
                    <img class="h-24 w-24 rounded-full object-cover mx-auto" src="{{ $mentorship->mentor->avatar_url }}"
                        alt="">
                    @else
                    <div
                        class="h-24 w-24 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-3xl mx-auto">
                        {{ substr($mentorship->mentor->name ?? 'M', 0, 1) }}
                    </div>
                    @endif
                    <h4 class="mt-4 text-xl font-bold text-gray-900">{{ $mentorship->mentor->name ?? 'Mentor' }}</h4>
                    <p class="text-sm text-gray-500">Mentor Certifié</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Terminate Modal -->
<div id="terminateModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog"
    aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"
            onclick="closeTerminateModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div
            class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
            <div>
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                    <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="mt-3 text-center sm:mt-5">
                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Terminer la relation de
                        mentorat</h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-500">
                            Vous allez mettre fin à la relation entre <span id="menteeName" class="font-bold"></span> et
                            <span id="mentorName" class="font-bold"></span>. Cette action est irréversible.
                        </p>
                    </div>
                </div>
            </div>
            <form id="terminateForm" method="POST" class="mt-5 sm:mt-6">
                @csrf
                <div class="mb-4 text-left">
                    <label for="diction_reason" class="block text-sm font-medium text-gray-700">Raison de l'arrêt
                        (obligatoire)</label>
                    <textarea name="diction_reason" id="diction_reason" rows="3" required
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm"
                        placeholder="Précisez la raison pour l'historique et les notifications..."></textarea>
                    <p class="mt-1 text-xs text-gray-400">Un email sera envoyé au jeune et au mentor avec cette
                        information.</p>
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="closeTerminateModal()"
                        class="flex-1 inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:text-sm">
                        Annuler
                    </button>
                    <button type="submit"
                        class="flex-1 inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:text-sm">
                        Confirmer l'arrêt
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openTerminateModal(url, mentee, mentor) {
        document.getElementById('terminateForm').action = url;
        document.getElementById('menteeName').textContent = mentee;
        document.getElementById('mentorName').textContent = mentor;
        document.getElementById('terminateModal').classList.remove('hidden');
    }

    function closeTerminateModal() {
        document.getElementById('terminateModal').classList.add('hidden');
    }
</script>
@endsection