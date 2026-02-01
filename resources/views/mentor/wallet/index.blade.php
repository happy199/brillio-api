@extends('layouts.mentor')

@section('title', 'Mon Portefeuille')

@section('content')
    <div x-data="{ activeTab: 'earnings' }" class="space-y-8">
        <!-- Header Global -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Mon Portefeuille</h1>
                <p class="text-gray-600">Gérez vos revenus et vos crédits Brillio.</p>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <button @click="activeTab = 'earnings'"
                    :class="activeTab === 'earnings' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Mes Revenus & Ventes
                </button>
                <button @click="activeTab = 'recharge'"
                    :class="activeTab === 'recharge' ? 'border-orange-500 text-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                    Rechargement & Dépenses
                </button>
            </nav>
        </div>

        <!-- Section 1: Revenus (Earnings) -->
        <div x-show="activeTab === 'earnings'" class="space-y-6">
            <!-- Stats Revenus avec Payout -->
            <div x-data="{ 
                                                    showPayoutModal: false,
                                                    payoutMethods: [],
                                                    payoutForm: {
                                                        amount: '',
                                                        payment_method: '',
                                                        phone_number: '',
                                                        country_code: '',
                                                        dial_code: ''
                                                    },
                                                    selectedMethodCountries: [],

                                                    selectPaymentMethod(methodCode) {
                                                        this.payoutForm.payment_method = methodCode;
                                                        const method = this.payoutMethods.find(m => m.short_code === methodCode);
                                                        if (method && method.countries && method.countries.length > 0) {
                                                            this.selectedMethodCountries = method.countries;
                                                            this.selectCountry(method.countries[0]);
                                                        } else {
                                                            this.selectedMethodCountries = [];
                                                            this.payoutForm.country_code = '';
                                                            this.payoutForm.dial_code = '';
                                                        }
                                                    },

                                                    selectCountry(country) {
                                                        this.payoutForm.country_code = country.code;
                                                        this.payoutForm.dial_code = country.dial_code;
                                                    },
                                                    availableBalance: 0,
                                                    totalWithdrawn: 0,
                                                    loading: false,
                                                    error: '',

                                                    async loadBalance() {
                                                        try {
                                                            const response = await fetch('/api/mentor/balance', {
                                                                headers: {
                                                                    'Authorization': `Bearer ${document.querySelector('meta[name=api-token]')?.content || ''}`,
                                                                    'Accept': 'application/json'
                                                                }
                                                            });
                                                            const data = await response.json();
                                                            this.availableBalance = data.available_balance || 0;
                                                            this.totalWithdrawn = data.total_withdrawn || 0;
                                                        } catch (e) {
                                                            console.error('Error loading balance:', e);
                                                        }
                                                    },

                                                    async loadPayoutMethods() {
                                                        try {
                                                            const response = await fetch('/api/mentor/payout-methods', {
                                                                headers: {
                                                                    'Authorization': `Bearer ${document.querySelector('meta[name=api-token]')?.content || ''}`,
                                                                    'Accept': 'application/json'
                                                                }
                                                            });
                                                            const data = await response.json();
                                                            this.payoutMethods = data.methods?.data || [];
                                                        } catch (e) {
                                                            console.error('Error loading payout methods:', e);
                                                        }
                                                    },

                                                    async requestPayout() {
                                                        this.loading = true;
                                                        this.error = '';

                                                        try {
                                                            const csrfToken = document.querySelector('meta[name=csrf-token]')?.content || '';
                                                            const response = await fetch('/api/mentor/payout/request', {
                                                                method: 'POST',
                                                                headers: {
                                                                    'Authorization': `Bearer ${document.querySelector('meta[name=api-token]')?.content || ''}`,
                                                                    'Content-Type': 'application/json',
                                                                    'Accept': 'application/json',
                                                                    'X-CSRF-TOKEN': csrfToken
                                                                },
                                                                body: JSON.stringify(this.payoutForm)
                                                            });

                                                            const data = await response.json();

                                                            if (!response.ok) {
                                                                throw new Error(data.message || 'Erreur lors de la demande de retrait');
                                                            }

                                                            this.showPayoutModal = false;
                                                            this.payoutForm = { amount: '', payment_method: '', phone_number: '', country_code: '', dial_code: '' };
                                                            this.selectedMethodCountries = [];
                                                            await this.loadBalance();
                                                            location.reload(); // Recharger pour afficher l'historique
                                                        } catch (e) {
                                                            this.error = e.message;
                                                        } finally {
                                                            this.loading = false;
                                                        }
                                                    }
                                                }" x-init="loadBalance(); loadPayoutMethods()"
                class="bg-gradient-to-r from-emerald-600 to-teal-500 rounded-2xl p-6 text-white shadow-lg">
                <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                    <div>
                        <h2 class="text-emerald-50 text-sm font-medium uppercase tracking-wide mb-1">Solde Disponible</h2>
                        <div class="flex items-baseline gap-2">
                            <span class="text-4xl font-extrabold"
                                x-text="new Intl.NumberFormat('fr-FR').format(availableBalance)">0</span>
                            <span class="text-sm font-bold opacity-80">FCFA</span>
                        </div>
                        <p class="text-emerald-100 text-sm mt-2">Total retiré : <span
                                x-text="new Intl.NumberFormat('fr-FR').format(totalWithdrawn)">0</span> FCFA</p>
                    </div>
                    <div>
                        <button @click="showPayoutModal = true"
                            class="bg-white text-emerald-600 font-bold px-6 py-3 rounded-xl hover:bg-emerald-50 transition shadow-md flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z">
                                </path>
                            </svg>
                            Retirer mes gains
                        </button>
                    </div>
                </div>

                <!-- Modal Payout -->
                <div x-show="showPayoutModal" x-cloak
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4"
                    @click.self="showPayoutModal = false">
                    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6" @click.stop>
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-xl font-bold text-gray-900">Demande de Retrait</h3>
                            <button @click="showPayoutModal = false" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <form @submit.prevent="requestPayout" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Montant (FCFA)</label>
                                <input type="number" x-model="payoutForm.amount" min="5000" :max="availableBalance" required
                                    placeholder="Minimum 5 000 FCFA"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent text-gray-900 bg-white">
                                <p class="text-xs text-gray-500 mt-1">Solde disponible: <span
                                        x-text="new Intl.NumberFormat('fr-FR').format(availableBalance)"></span> FCFA</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Méthode de paiement</label>
                                <select @change="selectPaymentMethod($event.target.value)"
                                    x-model="payoutForm.payment_method" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent text-gray-900 bg-white">
                                    <option value="" class="text-gray-500">Sélectionnez une méthode</option>
                                    <template x-for="method in payoutMethods" :key="method.short_code">
                                        <option :value="method.short_code" x-text="method.name" class="text-gray-900">
                                        </option>
                                    </template>
                                </select>
                            </div>

                            <div x-show="selectedMethodCountries.length > 0">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Pays</label>
                                <select x-model="payoutForm.country_code"
                                    @change="selectCountry(selectedMethodCountries.find(c => c.code === $event.target.value))"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent text-gray-900 bg-white">
                                    <template x-for="country in selectedMethodCountries" :key="country.code">
                                        <option :value="country.code" x-text="country.name" class="text-gray-900"></option>
                                    </template>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Numéro de téléphone</label>
                                <div class="flex rounded-lg shadow-sm">
                                    <span
                                        class="inline-flex items-center px-3 rounded-l-lg border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm font-medium"
                                        x-show="payoutForm.dial_code" x-text="payoutForm.dial_code">
                                    </span>
                                    <input type="tel" x-model="payoutForm.phone_number" required pattern="[0-9]{8,15}"
                                        placeholder="Ex: 66000001"
                                        :class="{'rounded-l-none': payoutForm.dial_code, 'rounded-lg': !payoutForm.dial_code}"
                                        class="flex-1 w-full px-4 py-2 border border-gray-300 rounded-r-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent text-gray-900 bg-white">
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Ex: 66000001 (Sans l'indicatif)</p>
                            </div>

                            <div x-show="error"
                                class="bg-red-50 border border-red-200 text-red-600 px-4 py-2 rounded-lg text-sm"
                                x-text="error"></div>

                            <div class="flex gap-3 pt-4">
                                <button type="button" @click="showPayoutModal = false"
                                    class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition font-medium">
                                    Annuler
                                </button>
                                <button type="submit" :disabled="loading"
                                    class="flex-1 px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition font-bold disabled:opacity-50">
                                    <span x-show="!loading">Confirmer</span>
                                    <span x-show="loading">Traitement...</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Historique des Ventes -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <h3 class="text-lg font-bold text-gray-900">Historique des Ventes</h3>
                    <span class="bg-indigo-100 text-indigo-800 text-xs font-bold px-2.5 py-0.5 rounded-full">Ressources &
                        Services</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-600">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                            <tr>
                                <th class="px-6 py-3 font-semibold">Date</th>
                                <th class="px-6 py-3 font-semibold">Description</th>
                                <th class="px-6 py-3 font-semibold">Acheteur / Détail</th>
                                <th class="px-6 py-3 font-semibold text-right">Montant</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($incomeTransactions as $transaction)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-900">
                                        {{ $transaction->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 text-gray-900 font-medium">{{ $transaction->description }}</td>
                                    <td class="px-6 py-4">
                                        @if($transaction->related && $transaction->related instanceof \App\Models\Purchase)
                                            <div class="flex items-center gap-2">
                                                <div
                                                    class="w-6 h-6 rounded-full bg-indigo-100 flex items-center justify-center text-xs font-bold text-indigo-600">
                                                    {{ substr($transaction->related->user->name ?? '?', 0, 1) }}
                                                </div>
                                                <div class="flex flex-col">
                                                    <span
                                                        class="font-medium text-gray-900">{{ $transaction->related->user->name ?? 'Utilisateur Inconnu' }}</span>
                                                    <span class="text-xs text-gray-500">Acheté le
                                                        {{ $transaction->related->created_at->format('d/m/Y') }}</span>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-gray-400 italic">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right font-bold text-green-600">+{{ $transaction->amount }} Cr.
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-gray-500 italic">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="w-12 h-12 text-gray-300 mb-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <p>Aucune vente enregistrée pour le moment.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <div class="p-4 bg-gray-50 border-t border-gray-100">
                    {{ $incomeTransactions->appends(['wallet_page' => request()->wallet_page, 'active_tab' => 'earnings'])->links() }}
                </div>
            </div>

            <!-- Historique des Retraits -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden" x-data="{
                                                     payoutRequests: [],
                                                     payoutMethods: [],

                                                     async loadData() {
                                                         await Promise.all([this.loadPayouts(), this.loadMethods()]);
                                                     },

                                                     async loadMethods() {
                                                         try {
                                                             const response = await fetch('/api/mentor/payout-methods', {
                                                                 headers: {
                                                                     'Authorization': `Bearer ${document.querySelector('meta[name=api-token]')?.content || ''}`,
                                                                     'Accept': 'application/json'
                                                                 }
                                                             });
                                                             const data = await response.json();
                                                             this.payoutMethods = data.methods?.data || [];
                                                         } catch (e) {
                                                             console.error('Error loading methods:', e);
                                                         }
                                                     },

                                                     getMethodName(code) {
                                                         if (!code) return '-';
                                                         const method = this.payoutMethods.find(m => m.short_code === code);
                                                         return method ? method.name : code;
                                                     },

                                                     async loadPayouts() {
                                                         try {
                                                             const response = await fetch('/api/mentor/payout-requests', {
                                                                 headers: {
                                                                     'Authorization': `Bearer ${document.querySelector('meta[name=api-token]')?.content || ''}`,
                                                                     'Accept': 'application/json'
                                                                 }
                                                             });
                                                             const data = await response.json();
                                                             this.payoutRequests = data.payouts || [];
                                                         } catch (e) {
                                                             console.error('Error loading payouts:', e);
                                                         }
                                                     }
                                                 }" x-init="loadData()">
                <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <h3 class="text-lg font-bold text-gray-900">Historique des Retraits</h3>
                    <span
                        class="bg-emerald-100 text-emerald-800 text-xs font-bold px-2.5 py-0.5 rounded-full">Payouts</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-600">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                            <tr>
                                <th class="px-6 py-3 font-semibold">Date</th>
                                <th class="px-6 py-3 font-semibold">Montant</th>
                                <th class="px-6 py-3 font-semibold">Frais</th>
                                <th class="px-6 py-3 font-semibold">Net</th>
                                <th class="px-6 py-3 font-semibold">Méthode</th>
                                <th class="px-6 py-3 font-semibold">Statut</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <template x-if="payoutRequests.length === 0">
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-gray-500 italic">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="w-12 h-12 text-gray-300 mb-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z">
                                                </path>
                                            </svg>
                                            <p>Aucun retrait effectué pour le moment.</p>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                            <template x-for="payout in payoutRequests" :key="payout.id">
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-900"
                                        x-text="new Date(payout.created_at).toLocaleDateString('fr-FR', {day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit'})">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-900"
                                        x-text="new Intl.NumberFormat('fr-FR').format(payout.amount) + ' FCFA'"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-600"
                                        x-text="new Intl.NumberFormat('fr-FR').format(payout.fee) + ' FCFA'"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-900 font-semibold"
                                        x-text="new Intl.NumberFormat('fr-FR').format(payout.net_amount) + ' FCFA'"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-900 font-medium"
                                        x-text="getMethodName(payout.payment_method)"></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span x-show="payout.status === 'pending'"
                                            class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">En
                                            attente</span>
                                        <span x-show="payout.status === 'processing'"
                                            class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">En
                                            cours</span>
                                        <span x-show="payout.status === 'completed'"
                                            class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Complété</span>
                                        <span x-show="payout.status === 'failed'"
                                            class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Échoué</span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Section 2: Rechargement & Dépenses (Recharge) -->
        <div x-show="activeTab === 'recharge'" class="space-y-6" style="display: none;">
            <!-- Solde Dépenses -->
            <div
                class="bg-gradient-to-r from-orange-500 to-red-600 rounded-2xl p-6 text-white shadow-lg flex flex-col md:flex-row items-center justify-between gap-6">
                <div>
                    <h2 class="text-orange-50 text-sm font-medium uppercase tracking-wide mb-1">Solde Disponible (Dépenses)
                    </h2>
                    <div class="flex items-baseline gap-2">
                        <span class="text-4xl font-extrabold">{{ number_format($user->credits_balance) }}</span>
                        <span class="text-sm font-bold opacity-80">Crédits</span>
                    </div>
                    <p class="text-orange-100 text-sm mt-2">Utilisez ce solde pour promouvoir vos ressources et accéder aux
                        outils premium.</p>
                </div>
                <div class="bg-white/20 p-3 rounded-xl backdrop-blur-sm">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Packs Recharge -->
                <div class="lg:col-span-2 space-y-6">
                    <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Acheter des crédits
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($packs as $pack)
                            <div
                                class="bg-white border rounded-xl p-5 hover:border-orange-500 hover:shadow-md transition relative group overflow-hidden {{ $pack->is_popular ? 'border-orange-500 ring-1 ring-orange-500' : '' }}">
                                @if($pack->promo_percent > 0)
                                    <div
                                        class="absolute top-0 right-0 bg-red-500 text-white text-[10px] font-bold px-2 py-1 rounded-bl-lg z-10">
                                        -{{ $pack->promo_percent }}%
                                    </div>
                                @elseif($pack->is_popular)
                                    <div
                                        class="absolute top-0 right-0 bg-orange-500 text-white text-[10px] font-bold px-2 py-1 rounded-bl-lg">
                                        POPULAIRE
                                    </div>
                                @endif

                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <span class="block text-2xl font-black text-gray-900">{{ $pack->credits }}</span>
                                        <span class="text-xs font-semibold text-gray-500 uppercase">Crédits</span>
                                    </div>
                                    <div class="text-right">
                                        <span
                                            class="block text-lg font-bold text-orange-600">{{ number_format($pack->price, 0, ',', ' ') }}
                                            F</span>
                                    </div>
                                </div>

                                @if($pack->name)
                                    <p class="text-sm text-gray-600 font-medium mb-4">{{ $pack->name }}</p>
                                @else
                                    <div class="mb-4"></div>
                                @endif

                                <form action="{{ route('mentor.wallet.purchase') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="pack_id" value="{{ $pack->id }}">
                                    <button type="submit"
                                        class="w-full py-2 px-3 bg-gray-50 hover:bg-orange-600 text-gray-700 hover:text-white font-semibold rounded-lg transition text-sm">
                                        Acheter maintenant
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>

                    <!-- Coupon -->
                    <div class="bg-orange-50 rounded-xl p-6 border border-orange-100 mt-6">
                        <h3 class="font-bold text-orange-900 mb-2">Code Promo</h3>
                        <form action="{{ route('mentor.wallet.redeem') }}" method="POST" class="flex gap-2">
                            @csrf
                            <input type="text" name="code" placeholder="Entrez votre code"
                                class="flex-1 bg-white border-0 text-sm rounded-lg focus:ring-2 focus:ring-orange-500 p-3">
                            <button type="submit"
                                class="bg-orange-600 text-white font-bold px-6 py-2 rounded-lg hover:bg-orange-700 transition">
                                Valider
                            </button>
                        </form>
                        @error('code') <p class="text-red-600 text-sm mt-2 font-medium">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Historique Recharge/Dépenses -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden sticky top-24">
                        <div class="p-4 border-b border-gray-100 bg-gray-50">
                            <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide">Historique Transactions</h2>
                        </div>
                        <div class="divide-y divide-gray-100 max-h-[500px] overflow-y-auto">
                            @forelse($walletTransactions as $transaction)
                                <div class="p-4 hover:bg-gray-50 transition">
                                    <div class="flex justify-between items-center mb-1">
                                        <span
                                            class="text-xs font-medium text-gray-500">{{ $transaction->created_at->format('d M H:i') }}</span>
                                        <span
                                            class="font-bold text-sm {{ $transaction->amount > 0 ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $transaction->amount > 0 ? '+' : '' }}{{ $transaction->amount }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-700 truncate" title="{{ $transaction->description }}">
                                        {{ $transaction->description }}
                                    </p>
                                </div>
                            @empty
                                <div class="p-6 text-center text-sm text-gray-500 italic">
                                    Aucune transaction récente.
                                </div>
                            @endforelse
                        </div>
                        <!-- Pagination -->
                        <div class="p-4 bg-gray-50 border-t border-gray-100">
                            {{ $walletTransactions->appends(['income_page' => request()->income_page, 'active_tab' => 'recharge'])->links('pagination.simple-orange') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection