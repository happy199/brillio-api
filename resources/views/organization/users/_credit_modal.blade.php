<!-- Credit Distribution Modal -->
<div x-show="showModal" class="fixed inset-0 z-50 overflow-y-auto" x-cloak
    x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="showModal = false"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

        <div class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-xl shadow-2xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

            <div>
                <div class="flex items-center justify-center w-12 h-12 mx-auto bg-organization-100 rounded-full">
                    <svg class="w-6 h-6 text-organization-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="mt-3 text-center sm:mt-5">
                    <h3 class="text-lg font-bold leading-6 text-gray-900"
                        x-text="targetType === 'all' ? 'Distribuer à tous le monde' : 'Offrir des crédits à ' + targetUserName">
                    </h3>
                    <div class="mt-4 p-4 bg-gray-50 rounded-lg border border-gray-100">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">Votre solde actuel :</span>
                            <span class="font-bold text-gray-900" x-text="balance + ' crédits'"></span>
                        </div>
                    </div>

                    <div class="mt-6 text-left">
                        <label for="amount" class="block text-sm font-semibold text-gray-700 mb-2">Montant par
                            personne</label>
                        <div class="relative rounded-md shadow-sm">
                            <input type="number" x-model.number="amount" id="amount" min="1"
                                class="block w-full pl-4 pr-12 py-3 border-gray-300 focus:ring-organization-500 focus:border-organization-500 sm:text-sm rounded-lg transition-all"
                                placeholder="Ex: 5">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">crédits</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 p-4 rounded-lg flex items-start gap-3 transition-colors duration-300"
                        :class="isInsufficient ? 'bg-red-50 border border-red-100' : 'bg-organization-50 border border-organization-100'">
                        <svg class="h-5 w-5 flex-shrink-0"
                            :class="isInsufficient ? 'text-red-400' : 'text-organization-400'" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="text-left">
                            <p class="text-sm font-medium"
                                :class="isInsufficient ? 'text-red-800' : 'text-organization-800'">
                                Coût total : <span x-text="totalCost"></span> crédits
                            </p>
                            <p class="mt-1 text-xs" :class="isInsufficient ? 'text-red-600' : 'text-organization-600'">
                                <template x-if="targetType === 'all'">
                                    <span x-text="'Pour ' + totalUsersAll + ' personnes.'"></span>
                                </template>
                                <template x-if="targetType === 'single'">
                                    <span>Pour 1 personne sélectionnée.</span>
                                </template>
                            </p>
                        </div>
                    </div>

                    <!-- Insufficient Funds Alert -->
                    <div x-show="isInsufficient" x-cloak class="mt-4 p-4 bg-red-100 rounded-lg text-left">
                        <h4 class="text-sm font-bold text-red-800">Solde insuffisant</h4>
                        <p class="text-xs text-red-700 mt-1">
                            Il vous manque <span x-text="totalCost - balance"></span>
                            crédits.
                        </p>
                        <div class="mt-3 flex gap-2">
                            <a href="{{ route('organization.wallet.index') }}"
                                class="px-3 py-1.5 bg-red-800 text-white text-xs font-bold rounded-md hover:bg-red-900 transition-colors">
                                Recharger
                            </a>
                            <button @click="amount = Math.floor(balance / actualUserCount)"
                                class="px-3 py-1.5 bg-white border border-red-200 text-red-800 text-xs font-bold rounded-md hover:bg-red-50 transition-colors">
                                Ajuster au max
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 space-y-3 sm:space-y-0 sm:flex sm:flex-row-reverse sm:gap-3">
                <button type="button" @click="submitDistribution()" :disabled="loading || isInsufficient || amount < 1"
                    :class="loading || isInsufficient || amount < 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-organization-700'"
                    class="inline-flex justify-center w-full px-4 py-3 text-sm font-bold text-white transition-all bg-organization-600 border border-transparent rounded-lg shadow-sm focus:outline-none focus:ring-organization-500 sm:w-auto">
                    <template x-if="!loading">
                        <span>Confirmer le don</span>
                    </template>
                    <template x-if="loading">
                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                    </template>
                </button>
                <button type="button" @click="showModal = false"
                    class="inline-flex justify-center w-full px-4 py-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-organization-500 sm:w-auto">
                    Annuler
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Feedback Modal (Success/Error) -->
<div x-show="showFeedbackModal" class="fixed inset-0 z-[60] overflow-y-auto" x-cloak
    x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-900 bg-opacity-50 backdrop-blur-sm"
            @click="closeFeedback()"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

        <div class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-2xl shadow-3xl sm:my-8 sm:align-middle sm:max-w-sm sm:w-full sm:p-6"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

            <div>
                <!-- Success Icon -->
                <template x-if="feedbackType === 'success'">
                    <div
                        class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-organization-100 mb-6 scale-up-center">
                        <svg class="h-10 w-10 text-organization-600 animate-bounce-short" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </template>

                <!-- Error Icon -->
                <template x-if="feedbackType === 'error'">
                    <div
                        class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-red-100 mb-6 shake-horizontal">
                        <svg class="h-10 w-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                </template>

                <div class="text-center">
                    <h3 class="text-xl font-extrabold text-gray-900 mb-2"
                        x-text="feedbackType === 'success' ? 'Félicitations !' : 'Oups !'"></h3>
                    <p class="text-sm text-gray-600 leading-relaxed" x-text="feedbackMessage"></p>
                </div>
            </div>

            <div class="mt-8">
                <button type="button" @click="closeFeedback()"
                    :class="feedbackType === 'success' ? 'bg-organization-600 hover:bg-organization-700 focus:ring-organization-500' : 'bg-red-600 hover:bg-red-700 focus:ring-red-500'"
                    class="inline-flex justify-center w-full px-6 py-3 text-base font-bold text-white transition-all border border-transparent rounded-xl shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2">
                    Continuer
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .scale-up-center {
        animation: scale-up-center 0.4s cubic-bezier(0.390, 0.575, 0.565, 1.000) both;
    }

    @keyframes scale-up-center {
        0% {
            transform: scale(0.5);
        }

        100% {
            transform: scale(1);
        }
    }

    .shake-horizontal {
        animation: shake-horizontal 0.8s cubic-bezier(0.455, 0.030, 0.515, 0.955) both;
    }

    @keyframes shake-horizontal {

        0%,
        100% {
            transform: translateX(0);
        }

        10%,
        30%,
        50%,
        70% {
            transform: translateX(-10px);
        }

        20%,
        40%,
        60% {
            transform: translateX(10px);
        }

        80% {
            transform: translateX(8px);
        }

        90% {
            transform: translateX(-8px);
        }
    }

    .animate-bounce-short {
        animation: bounce 1s ease-in-out 3;
    }

    @keyframes bounce {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-10px);
        }
    }

    .shadow-3xl {
        box-shadow: 0 35px 60px -15px rgba(0, 0, 0, 0.3);
    }
</style>