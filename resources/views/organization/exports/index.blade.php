@extends('layouts.organization')

@section('title', 'Centre d\'Exportation')

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Centre d'Exportation</h1>
            <p class="mt-1 text-sm text-gray-500">Configurez et téléchargez vos rapports personnalisés.</p>
        </div>
        <a href="{{ route('organization.dashboard') }}"
            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
            Retour au tableau de bord
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Export Configuration Form -->
        <div class="lg:col-span-2 relative">

            @if(!$organization->isEnterprise())
            <div
                class="absolute inset-0 z-10 bg-white/60 backdrop-blur-[2px] rounded-lg flex flex-col items-center justify-center text-center p-8">
                <div class="bg-white p-8 rounded-xl shadow-2xl border border-gray-200 max-w-md">
                    <div
                        class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-gray-900 text-white mb-6">
                        <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Fonctionnalité Entreprise</h3>
                    <p class="text-gray-500 mb-8">
                        Le Centre d'Exportation est réservé aux organisations sous contrat Entreprise.
                        Générez des rapports PDF et CSV illimités pour vos bilans d'impact.
                    </p>
                    <a href="{{ route('organization.subscriptions.index') }}"
                        class="inline-flex w-full justify-center items-center rounded-md bg-gray-900 px-5 py-3 text-base font-semibold text-white shadow-sm hover:bg-gray-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-900 transition-colors">
                        Voir l'offre Entreprise
                    </a>
                </div>
            </div>
            @endif

            <div
                class="bg-white shadow rounded-lg overflow-hidden {{ !$organization->isEnterprise() ? 'filter blur-[1px]' : '' }}">
                <form action="{{ route('organization.exports.generate') }}" method="GET" class="p-6 space-y-8">
                    <!-- Section 1: Type de rapport -->
                    <div>
                        <label class="text-base font-semibold text-gray-900">1. Type de rapport</label>
                        <p class="text-sm text-gray-500 mb-4">Quelles données souhaitez-vous exporter ?</p>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <!-- General Stats -->
                            <label
                                class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none border-gray-200 hover:border-indigo-300 transition-all">
                                <input type="radio" name="type" value="general" class="sr-only" checked>
                                <span class="flex flex-1">
                                    <span class="flex flex-col">
                                        <span class="block text-sm font-bold text-gray-900">Stats Générales</span>
                                        <span class="mt-1 flex items-center text-xs text-gray-500">Résumé global de
                                            l'activité</span>
                                    </span>
                                </span>
                                <div class="radio-indicator absolute -inset-px rounded-lg border-2 border-transparent pointer-events-none"
                                    aria-hidden="true"></div>
                            </label>

                            <!-- Mentees List -->
                            <label
                                class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none border-gray-200 hover:border-indigo-300 transition-all">
                                <input type="radio" name="type" value="jeunes" class="sr-only">
                                <span class="flex flex-1">
                                    <span class="flex flex-col">
                                        <span class="block text-sm font-bold text-gray-900">Liste des Jeunes</span>
                                        <span class="mt-1 flex items-center text-xs text-gray-500">Détails des profils
                                            parrainés</span>
                                    </span>
                                </span>
                                <div class="radio-indicator absolute -inset-px rounded-lg border-2 border-transparent pointer-events-none"
                                    aria-hidden="true"></div>
                            </label>

                            <!-- Sessions History -->
                            <label
                                class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none border-gray-200 hover:border-indigo-300 transition-all">
                                <input type="radio" name="type" value="sessions" class="sr-only">
                                <span class="flex flex-1">
                                    <span class="flex flex-col">
                                        <span class="block text-sm font-bold text-gray-900">Rapports Séances</span>
                                        <span class="mt-1 flex items-center text-xs text-gray-500">Historique des
                                            mentorats</span>
                                    </span>
                                </span>
                                <div class="radio-indicator absolute -inset-px rounded-lg border-2 border-transparent pointer-events-none"
                                    aria-hidden="true"></div>
                            </label>
                        </div>
                    </div>

                    <!-- Section 2: Format d'export -->
                    <div class="pt-6 border-t border-gray-100">
                        <label class="text-base font-semibold text-gray-900">2. Format d'export</label>
                        <p class="text-sm text-gray-500 mb-4">Choisissez le format de fichier souhaité.</p>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <!-- CSV -->
                            <label
                                class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none border-gray-200 hover:border-indigo-300 transition-all">
                                <input type="radio" name="format" value="csv" class="sr-only" checked>
                                <span class="flex flex-1">
                                    <span class="flex flex-col">
                                        <span class="block text-sm font-bold text-gray-900 flex items-center">
                                            <svg class="h-4 w-4 text-green-600 mr-2" fill="currentColor"
                                                viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            CSV (Excel)
                                        </span>
                                        <span class="mt-1 flex items-center text-xs text-gray-500">Pour analyse et
                                            manipulation de données</span>
                                    </span>
                                </span>
                                <div class="radio-indicator absolute -inset-px rounded-lg border-2 border-transparent pointer-events-none"
                                    aria-hidden="true"></div>
                            </label>

                            <!-- PDF -->
                            <label
                                class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none border-gray-200 hover:border-indigo-300 transition-all">
                                <input type="radio" name="format" value="pdf" class="sr-only">
                                <span class="flex flex-1">
                                    <span class="flex flex-col">
                                        <span class="block text-sm font-bold text-gray-900 flex items-center">
                                            <svg class="h-4 w-4 text-red-600 mr-2" fill="currentColor"
                                                viewBox="0 0 20 20">
                                                <path
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9a1 1 0 000 2h6a1 1 0 100-2H7z" />
                                            </svg>
                                            PDF (Document)
                                        </span>
                                        <span class="mt-1 flex items-center text-xs text-gray-500">Rapport formaté pour
                                            lecture et impression</span>
                                    </span>
                                </span>
                                <div class="radio-indicator absolute -inset-px rounded-lg border-2 border-transparent pointer-events-none"
                                    aria-hidden="true"></div>
                            </label>
                        </div>
                    </div>

                    <!-- Section 3: Filtres de période -->
                    <div class="pt-6 border-t border-gray-100">
                        <label class="text-base font-semibold text-gray-900">3. Filtres de période</label>
                        <p class="text-sm text-gray-500 mb-4">Optionnel : restreindre l'export à une période spécifique.
                        </p>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700">Date de
                                    début</label>
                                <input type="date" name="start_date" id="start_date"
                                    class="mt-1 block w-full px-4 py-3 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="end_date" class="block text-sm font-medium text-gray-700">Date de
                                    fin</label>
                                <input type="date" name="end_date" id="end_date"
                                    class="mt-1 block w-full px-4 py-3 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-8">
                        <button type="submit"
                            class="w-full flex justify-center items-center py-4 px-6 border border-transparent rounded-md shadow-lg text-base font-bold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all transform hover:scale-[1.01] active:scale-[0.99] disabled:opacity-50 disabled:cursor-not-allowed"
                            {{ !$organization->isEnterprise() ? 'disabled' : '' }}>
                            <svg class="mr-2 h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Générer l'exportation
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Help / Info Card -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-indigo-50 rounded-lg p-6 border border-indigo-100 shadow-sm">
                <h3 class="text-sm font-bold text-indigo-900 uppercase tracking-wider mb-4">Informations utiles</h3>
                <ul class="space-y-4">
                    <li class="flex gap-3">
                        <svg class="h-6 w-6 text-indigo-600 flex-shrink-0" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-sm text-indigo-800 leading-relaxed">Les exports sont générés en temps réel avec
                            les données les plus récentes de votre organisation.</p>
                    </li>
                    <li class="flex gap-3">
                        <svg class="h-6 w-6 text-indigo-600 flex-shrink-0" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-sm text-indigo-800 leading-relaxed">Le format <strong>CSV</strong> est idéal pour
                            importer vos données dans Excel ou Google Sheets pour analyse.</p>
                    </li>
                    <li class="flex gap-3">
                        <svg class="h-6 w-6 text-indigo-600 flex-shrink-0" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-sm text-indigo-800 leading-relaxed">Les rapports <strong>PDF</strong> sont
                            optimisés pour le partage visuel et l'archivage de documents.</p>
                    </li>
                </ul>
            </div>

            <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
                <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4">Besoin d'aide ?</h3>
                <p class="text-sm text-gray-600 mb-4 italic">Vous ne trouvez pas les données que vous cherchez ?</p>
                <a href="mailto:support@brillio.ai"
                    class="text-sm font-semibold text-indigo-600 hover:text-indigo-500 flex items-center">
                    Contacter le support
                    <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    /* Styling for the radio button indicator */
    input[type="radio"]:checked~.radio-indicator {
        border-color: #4f46e5;
        background-color: rgba(245, 243, 255, 0.4);
    }

    label:has(input[type="radio"]:checked) {
        border-color: #4f46e5;
        background-color: #f5f3ff;
    }
</style>
@endsection