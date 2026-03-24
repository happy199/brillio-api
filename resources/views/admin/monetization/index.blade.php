@extends('layouts.admin')

@section('title', 'Monétisation')

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Monétisation & Crédits</h1>
            <p class="text-gray-600">Gérez l'économie de la plateforme</p>
        </div>
        <a href="{{ route('admin.monetization.coupons') }}"
            class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition">
            Gérer les Coupons
        </a>
    </div>

    <!-- Stats Rapides -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Crédits Achetés -->
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-sm font-medium text-gray-500">Crédits Achetés (Total)</p>
                    <div class="flex items-baseline gap-2">
                        <p class="text-4xl font-bold text-gray-900 mt-1">{{ number_format($totalCreditsPurchased) }}</p>
                        <span class="text-xs font-semibold text-gray-400 uppercase">Crédits</span>
                    </div>
                </div>
                <div class="p-3 bg-green-50 rounded-full">
                    <span class="text-2xl">💰</span>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-2 pt-4 border-t border-gray-50">
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wide">Jeunes</p>
                    <p class="text-sm font-bold text-gray-800">{{ number_format($creditsPurchasedJeune) }}</p>
                    <p class="text-[10px] font-medium text-green-600">≈ {{ number_format($fcfaPurchasedJeune, 0, ',', '
                        ') }}</p>
                </div>
                <div class="text-center">
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wide">Mentors</p>
                    <p class="text-sm font-bold text-gray-800">{{ number_format($creditsPurchasedMentor) }}</p>
                    <p class="text-[10px] font-medium text-green-600">≈ {{ number_format($fcfaPurchasedMentor, 0, ',', '
                        ') }}</p>
                </div>
                <div class="text-right">
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wide">Orgs</p>
                    <p class="text-sm font-bold text-gray-800">{{ number_format($creditsPurchasedOrg) }}</p>
                    <p class="text-[10px] font-medium text-green-600">≈ {{ number_format($fcfaPurchasedOrg, 0, ',', ' ')
                        }}</p>
                </div>
            </div>
        </div>

        <!-- Crédits Consommés -->
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-sm font-medium text-gray-500">Crédits Consommés (Total)</p>
                    <div class="flex items-baseline gap-2">
                        <p class="text-4xl font-bold text-gray-900 mt-1">{{ number_format($totalCreditsUsed) }}</p>
                        <span class="text-xs font-semibold text-gray-400 uppercase">Crédits</span>
                    </div>
                </div>
                <div class="p-3 bg-blue-50 rounded-full">
                    <span class="text-2xl">📉</span>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-2 pt-4 border-t border-gray-50">
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wide">Jeunes</p>
                    <p class="text-sm font-bold text-gray-800">{{ number_format($creditsConsumedJeune) }}</p>
                    <p class="text-[10px] font-medium text-blue-600">≈ {{ number_format($fcfaConsumedJeune, 0, ',', ' ')
                        }}</p>
                </div>
                <div class="text-center">
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wide">Mentors</p>
                    <p class="text-sm font-bold text-gray-800">{{ number_format($creditsConsumedMentor) }}</p>
                    <p class="text-[10px] font-medium text-blue-600">≈ {{ number_format($fcfaConsumedMentor, 0, ',', '
                        ') }}</p>
                </div>
                <div class="text-right">
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wide">Orgs</p>
                    <p class="text-sm font-bold text-gray-800">{{ number_format($creditsConsumedOrg) }}</p>
                    <p class="text-[10px] font-medium text-blue-600">≈ {{ number_format($fcfaConsumedOrg, 0, ',', ' ')
                        }}</p>
                </div>
            </div>
        </div>

        <!-- Revenus Organisations -->
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-sm font-medium text-gray-500">Revenus Organisations</p>
                    <div class="flex items-baseline gap-2">
                        <p class="text-4xl font-bold text-indigo-600 mt-1">{{ number_format($orgRevenue, 0, ',', ' ') }}
                        </p>
                        <span class="text-xs font-semibold text-gray-400 uppercase">FCFA</span>
                    </div>
                </div>
                <div class="p-3 bg-indigo-50 rounded-full">
                    <span class="text-2xl">🏢</span>
                </div>
            </div>
            <div class="pt-4 border-t border-gray-50">
                <p class="text-xs text-gray-500">Cumul des achats de packs et abonnements effectués par les
                    organisations.</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Configuration -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl border border-gray-200 p-6 sticky top-24">
                <h2 class="text-lg font-bold text-gray-900 mb-6 pb-2 border-b border-gray-100">Configuration</h2>

                @if(session('success'))
                <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-400 text-green-700 text-sm">
                    {{ session('success') }}
                </div>
                @endif

                @if($errors->any())
                <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-400 text-red-700 text-sm">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form action="{{ route('admin.monetization.settings.update') }}" method="POST" class="space-y-5">
                    @csrf

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Prix Crédit - JEUNE (FCFA)</label>
                        <div class="relative">
                            <input type="number" name="credit_price_jeune" value="{{ $creditPriceJeune }}"
                                class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5 pr-12">
                            <div
                                class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-500 text-xs">
                                FCFA</div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Prix Crédit - MENTOR
                            (FCFA)</label>
                        <div class="relative">
                            <input type="number" name="credit_price_mentor" value="{{ $creditPriceMentor }}"
                                class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5 pr-12">
                            <div
                                class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-500 text-xs">
                                FCFA</div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Prix Crédit - ORGANISATION
                            (FCFA)</label>
                        <div class="relative">
                            <input type="number" name="credit_price_organization" value="{{ $creditPriceOrganization }}"
                                class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5 pr-12">
                            <div
                                class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-500 text-xs">
                                FCFA</div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Commission Mise en Relation
                            (%)</label>
                        <div class="relative">
                            <input type="number" name="mentorship_commission_percent" value="{{ $commissionPercent }}"
                                class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5 pr-12"
                                min="0" max="100">
                            <div
                                class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-500 text-xs">
                                %</div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Commission prélevée sur le coût des sessions.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Coût Ciblage Avancé
                            (Crédits)</label>
                        <div class="relative">
                            <input type="number" name="feature_cost_advanced_targeting" value="{{ $targetingCost }}"
                                class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5 pr-12">
                            <div
                                class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-500 text-xs">
                                Crédits</div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Débité lors de la création/maj d'une ressource avec
                            ciblage.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Commission Retrait Mentor
                            (%)</label>
                        <div class="relative">
                            <input type="number" name="payout_fee_percentage" value="{{ $payoutFeePercentage }}"
                                class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5 pr-12"
                                min="0" max="100">
                            <div
                                class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-500 text-xs">
                                %</div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Pourcentage prélevée sur le solde lors d'un retrait.</p>
                    </div>

                    <div class="pt-4 mt-6 border-t border-gray-100">
                        <h3 class="text-sm font-bold text-gray-900 mb-4 tracking-tight uppercase">Coûts des
                            Fonctionnalités</h3>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Outils d'Analyse (Crédits)</label>
                                <div class="relative">
                                    <input type="number" name="feature_cost_analysis_tool" value="{{ $analysisToolCost }}"
                                        class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5 pr-12">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-500 text-xs">
                                        Crédits</div>
                                </div>
                                <p class="text-[10px] text-gray-500 mt-1 italic">Utilisé par les mentors pour analyser la demande.</p>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Contact Conseiller (Crédits)</label>
                                <div class="relative">
                                    <input type="number" name="feature_cost_contact_advisor" value="{{ $contactAdvisorCost }}"
                                        class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5 pr-12">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-500 text-xs">
                                        Crédits</div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Nouveau Chat AI (Crédits)</label>
                                <div class="relative">
                                    <input type="number" name="feature_cost_new_chat" value="{{ $newChatCost }}"
                                        class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5 pr-12">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-500 text-xs">
                                        Crédits</div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Déblocage Historique (Crédits)</label>
                                <div class="relative">
                                    <input type="number" name="feature_cost_unlock_history" value="{{ $unlockHistoryCost }}"
                                        class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5 pr-12">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-500 text-xs">
                                        Crédits</div>
                                </div>
                                <p class="text-[10px] text-gray-500 mt-1 italic">Commun mentor et jeune pour l'historique complet.</p>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Rapport Compilé (Crédits)</label>
                                <div class="relative">
                                    <input type="number" name="feature_cost_compiled_report" value="{{ $compiledReportCost }}"
                                        class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5 pr-12">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-500 text-xs">
                                        Crédits</div>
                                </div>
                                <p class="text-[10px] text-gray-500 mt-1 italic">Générer un rapport global mentor/jeune.</p>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Téléchargement Transcription (Crédits)</label>
                                <div class="relative">
                                    <input type="number" name="feature_cost_transcription_download" value="{{ $transcriptionDownloadCost }}"
                                        class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5 pr-12">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-500 text-xs">
                                        Crédits</div>
                                </div>
                                <p class="text-[10px] text-gray-500 mt-1 italic">Coût pour télécharger le PDF de la transcription (Mentor & Jeune).</p>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Génération Rapport IA (Crédits)</label>
                                <div class="relative">
                                    <input type="number" name="feature_cost_ai_report_generation" value="{{ $aiReportGenerationCost }}"
                                        class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5 pr-12">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-500 text-xs">
                                        Crédits</div>
                                </div>
                                <p class="text-[10px] text-gray-500 mt-1 italic">Coût pour le pré-remplissage du compte rendu via l'IA (Mentor).</p>
                            </div>
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full bg-gray-900 text-white font-bold py-2.5 rounded-lg hover:bg-black transition">
                        Enregistrer
                    </button>
                </form>
            </div>
        </div>

        <!-- Historique Transactions -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="p-6 border-b border-gray-100">
                    <h2 class="text-lg font-bold text-gray-900">Dernières Transactions</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-600">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                            <tr>
                                <th class="px-6 py-3 font-semibold">Date</th>
                                <th class="px-6 py-3 font-semibold">Utilisateur</th>
                                <th class="px-6 py-3 font-semibold">Type</th>
                                <th class="px-6 py-3 font-semibold">Montant</th>
                                <th class="px-6 py-3 font-semibold">Description</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($transactions as $transaction)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-3 whitespace-nowrap">
                                    {{ $transaction->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-3 font-medium text-gray-900">
                                    @if($transaction->user && $transaction->user->user_type === 'organization' &&
                                    $transaction->user->organization)
                                    <div class="flex flex-col">
                                        <span>{{ $transaction->user->organization->name }}</span>
                                        <span class="text-[10px] text-gray-400 font-normal">Par: {{
                                            $transaction->user->name }}</span>
                                    </div>
                                    @elseif($transaction->organization)
                                    <div class="flex flex-col">
                                        <span>{{ $transaction->organization->name }}</span>
                                        <span class="text-[10px] text-gray-400 font-normal italic">Système</span>
                                    </div>
                                    @else
                                    {{ optional($transaction->user)->name ?? 'Utilisateur supprimé' }}
                                    @endif
                                </td>
                                <td class="px-6 py-3">
                                    @if($transaction->amount > 0)
                                    <span
                                        class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Crédit</span>
                                    @else
                                    <span
                                        class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Débit</span>
                                    @endif
                                </td>
                                <td
                                    class="px-6 py-3 font-bold {{ $transaction->amount > 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $transaction->amount > 0 ? '+' : '' }}{{ $transaction->amount }}
                                </td>
                                <td class="px-6 py-3 text-xs text-gray-500 max-w-xs truncate"
                                    title="{{ $transaction->description }}">
                                    {{ $transaction->description }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-gray-500 italic">Aucune transaction
                                    enregistrée.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection