@extends('layouts.admin')

@section('title', 'Détails du Retrait')
@section('header', 'Détails du Retrait #' . $payout->id)

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.payouts.index') }}" class="text-blue-600 hover:text-blue-800 flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                </path>
            </svg>
            Retour à la liste
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Informations Mentor -->
        <div class="md:col-span-2">
            <div class="bg-white rounded-xl shadow overflow-hidden mb-6">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="font-bold text-gray-800">Informations du Mentor</h3>
                </div>
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <div
                            class="w-12 h-12 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-xl mr-4">
                            {{ substr($payout->mentorProfile->user->name, 0, 1) }}
                        </div>
                        <div>
                            <div class="font-bold text-lg text-gray-900">{{ $payout->mentorProfile->user->name }}</div>
                            <div class="text-gray-500">{{ $payout->mentorProfile->user->email }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="font-bold text-gray-800">Détails de la demande</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Montant brut</p>
                            <p class="font-bold text-gray-900">{{ number_format($payout->amount, 0, ',', ' ') }} FCFA
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Frais Brillio</p>
                            <p class="text-red-600 font-medium">{{ number_format($payout->fee, 0, ',', ' ') }} FCFA</p>
                        </div>
                        <div class="col-span-2 py-2 border-t border-gray-50 mt-2">
                            <p class="text-sm text-gray-500">Montant net à verser</p>
                            <p class="text-2xl font-bold text-indigo-600">{{ number_format($payout->net_amount, 0, ',',
                                ' ') }} FCFA</p>
                        </div>
                        <div class="mt-4">
                            <p class="text-sm text-gray-500">Méthode de paiement</p>
                            <p class="font-bold text-gray-900 uppercase">{{ $payout->payment_method }}</p>
                        </div>
                        <div class="mt-4">
                            <p class="text-sm text-gray-500">Pays & Numéro</p>
                            <p class="font-bold text-gray-900">{{ $payout->country_code }} ({{ $payout->dial_code }}) {{
                                $payout->phone_number }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions & Statut -->
        <div class="md:col-span-1">
            <div class="bg-white rounded-xl shadow overflow-hidden mb-6">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="font-bold text-gray-800">Statut du retrait</h3>
                </div>
                <div class="p-6">
                    <div class="mb-4">
                        @switch($payout->status)
                        @case('pending')
                        <span class="px-3 py-1 text-sm font-bold rounded-full bg-yellow-100 text-yellow-800">En
                            attente</span>
                        @break
                        @case('processing')
                        <span class="px-3 py-1 text-sm font-bold rounded-full bg-blue-100 text-blue-800">En cours</span>
                        @break
                        @case('completed')
                        <span
                            class="px-3 py-1 text-sm font-bold rounded-full bg-green-100 text-green-800">Complété</span>
                        @break
                        @case('failed')
                        <span class="px-3 py-1 text-sm font-bold rounded-full bg-red-100 text-red-800">Échoué</span>
                        @break
                        @endswitch
                    </div>

                    <div class="text-sm text-gray-500 mb-4">
                        Demande faite le : <br>
                        <span class="text-gray-900 font-medium">{{ $payout->created_at->format('d/m/Y \à H:i') }}</span>
                    </div>

                    @if($payout->is_manual)
                    <div class="p-3 bg-orange-50 border border-orange-200 rounded-lg text-orange-700 text-xs mb-4">
                        <i class="fas fa-info-circle mr-1"></i> Ce retrait nécessite un traitement
                        <strong>manuel</strong> (Hors Bénin).
                    </div>
                    @else
                    <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg text-blue-700 text-xs mb-4">
                        <i class="fas fa-robot mr-1"></i> Traitement <strong>automatique</strong> via Moneroo.
                    </div>
                    @endif

                    @if($payout->is_manual && in_array($payout->status, ['pending', 'processing']))
                    <div class="space-y-3 mt-6">
                        <form action="{{ route('admin.payouts.approve', $payout) }}" method="POST"
                            onsubmit="return confirm('Avez-vous bien effectué le virement manuel pour ce mentor ?')">
                            @csrf
                            <button type="submit"
                                class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition">
                                Marquer comme Payé
                            </button>
                        </form>

                        <button @click="$dispatch('open-modal', 'reject-modal')"
                            class="w-full bg-red-50 text-red-600 hover:bg-red-100 font-bold py-2 px-4 rounded-lg transition border border-red-200">
                            Rejeter la demande
                        </button>
                    </div>
                    @elseif($payout->status === 'pending' && !$payout->is_manual)
                    <div class="p-3 bg-gray-50 border border-gray-200 rounded-lg text-gray-600 text-xs mt-6">
                        Le retrait est en attente du déclenchement du job de traitement.
                    </div>
                    @endif
                </div>
            </div>

            @if($payout->error_message)
            <div class="bg-red-50 border border-red-200 rounded-xl p-4">
                <h4 class="text-red-800 font-bold text-sm mb-1 uppercase">Erreur / Motif rejet</h4>
                <p class="text-red-600 text-sm">{{ $payout->error_message }}</p>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal de Rejet -->
<div x-data="{ open: false }" @open-modal.window="if($event.detail === 'reject-modal') open = true" x-show="open"
    class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true" @click="open = false">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div
            class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form action="{{ route('admin.payouts.reject', $payout) }}" method="POST">
                @csrf
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Rejeter la demande de retrait</h3>
                    <p class="text-sm text-gray-500 mb-4">Veuillez indiquer le motif du rejet. Le mentor sera remboursé
                        automatiquement de ses crédits et de son solde.</p>
                    <div>
                        <label for="reason" class="block text-sm font-medium text-gray-700 mb-1">Motif du rejet</label>
                        <textarea name="reason" id="reason" rows="3" required
                            class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-red-500 outline-none"></textarea>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                    <button type="submit"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none sm:w-auto sm:text-sm">
                        Confirmer le rejet
                    </button>
                    <button type="button" @click="open = false"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm">
                        Annuler
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection