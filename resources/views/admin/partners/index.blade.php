@extends('layouts.admin')

@section('header_content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 w-full">
    <div>
        <h2 class="text-xl font-bold text-gray-800 flex items-center">
            <i class="fas fa-handshake text-indigo-600 mr-3"></i>
            Partenaires
        </h2>
        <p class="text-xs text-gray-500 mt-1">
            Gérez les partenaires et sponsors affichés sur la page « À propos » et sur la plateforme.
        </p>
    </div>
    <div class="flex items-center space-x-3">
        <a href="{{ route('about') }}" target="_blank"
            class="bg-white hover:bg-gray-50 border border-gray-300 text-gray-700 text-sm font-medium py-2 px-3 rounded-lg shadow-sm flex items-center transition duration-150">
            <i class="fas fa-external-link-alt mr-2 text-gray-500 text-xs"></i> Voir page À propos
        </a>
        <a href="{{ route('admin.partners.create') }}"
            class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium py-2 px-4 rounded-lg shadow-sm flex items-center transition duration-150">
            <i class="fas fa-plus mr-2"></i> Nouveau Partenaire
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="space-y-6">

    <!-- KPI / Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Total Visibles -->
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex items-center">
            <div class="h-12 w-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl mr-4">
                <i class="fas fa-eye"></i>
            </div>
            <div>
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Visibles sur le site</div>
                <div class="text-2xl font-extrabold text-gray-900 mt-0.5">{{ $totalActivePartners }}</div>
                <div class="text-xs text-green-600 font-medium">Affichés sur la page À propos</div>
            </div>
        </div>

        <!-- External Partners -->
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex items-center">
            <div class="h-12 w-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl mr-4">
                <i class="fas fa-file-contract"></i>
            </div>
            <div>
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Partenaires Externes</div>
                <div class="text-2xl font-extrabold text-gray-900 mt-0.5">{{ $totalExternalPartners }}</div>
                <div class="text-xs text-gray-500 font-medium">{{ $totalActiveExternal }} actif(s)</div>
            </div>
        </div>

        <!-- Brillio Organizations with Logo -->
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex items-center">
            <div class="h-12 w-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl mr-4">
                <i class="fas fa-building"></i>
            </div>
            <div>
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Comptes Organisations</div>
                <div class="text-2xl font-extrabold text-gray-900 mt-0.5">{{ $totalOrgs }}</div>
                <div class="text-xs text-gray-500 font-medium">Avec logo sur Brillio</div>
            </div>
        </div>

        <!-- Quick Info Card -->
        <div class="bg-gradient-to-br from-indigo-50 to-blue-50 p-5 rounded-xl shadow-sm border border-indigo-100 flex items-center">
            <div class="h-12 w-12 rounded-xl bg-white text-indigo-600 flex items-center justify-center text-xl mr-4 shadow-xs">
                <i class="fas fa-lightbulb"></i>
            </div>
            <div class="text-xs text-gray-700">
                <span class="font-semibold block text-indigo-900">Enrichissement libre :</span>
                Ajoutez vos partenaires (contrats, écoles, partenaires stratégiques) même s'ils n'ont pas de compte Brillio.
            </div>
        </div>
    </div>

    <!-- Main Container -->
    <div class="bg-white shadow-sm sm:rounded-xl border border-gray-100 overflow-hidden">
        
        <!-- Filter Tabs & Search -->
        <div class="p-4 border-b border-gray-200 flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-gray-50/50">
            <!-- Tabs -->
            <div class="flex items-center space-x-2">
                <a href="{{ route('admin.partners.index', ['tab' => 'all', 'search' => $search]) }}"
                    class="px-4 py-2 text-xs font-semibold rounded-lg transition-colors {{ $tab === 'all' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200' }}">
                    Tous ({{ $totalActivePartners }})
                </a>
                <a href="{{ route('admin.partners.index', ['tab' => 'external', 'search' => $search]) }}"
                    class="px-4 py-2 text-xs font-semibold rounded-lg transition-colors {{ $tab === 'external' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200' }}">
                    Partenaires Externes ({{ $totalExternalPartners }})
                </a>
                <a href="{{ route('admin.partners.index', ['tab' => 'organizations', 'search' => $search]) }}"
                    class="px-4 py-2 text-xs font-semibold rounded-lg transition-colors {{ $tab === 'organizations' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200' }}">
                    Organisations Brillio ({{ $totalOrgs }})
                </a>
            </div>

            <!-- Search Form -->
            <form action="{{ route('admin.partners.index') }}" method="GET" class="flex items-center">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <div class="relative w-full sm:w-64">
                    <input type="text" name="search" value="{{ $search }}"
                        placeholder="Rechercher par nom..."
                        class="w-full pl-9 pr-4 py-2 text-xs border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <i class="fas fa-search text-xs"></i>
                    </div>
                </div>
                @if($search)
                <a href="{{ route('admin.partners.index', ['tab' => $tab]) }}" class="ml-2 text-xs text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times-circle"></i>
                </a>
                @endif
            </form>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Partenaire / Organisation
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Source / Type
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Site Web
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Ordre / Notes
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Statut À propos
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">

                    @php
                        $hasResults = false;
                    @endphp

                    {{-- 1. EXTERNAL PARTNERS --}}
                    @if($tab === 'all' || $tab === 'external')
                        @foreach($externalPartners as $partner)
                            @php $hasResults = true; @endphp
                            <tr class="hover:bg-gray-50/70 transition-colors">
                                <!-- Logo & Name -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-12 w-20 bg-gray-50 border border-gray-100 rounded-lg p-1.5 flex items-center justify-center">
                                            @if($partner->logo_url)
                                                <img class="max-h-full max-w-full object-contain" src="{{ $partner->logo_url }}" alt="{{ $partner->name }}">
                                            @else
                                                <div class="text-xs font-bold text-gray-400">{{ $partner->initials }}</div>
                                            @endif
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-bold text-gray-900 flex items-center">
                                                {{ $partner->name }}
                                            </div>
                                            @if($partner->description)
                                                <div class="text-xs text-gray-500 max-w-xs truncate" title="{{ $partner->description }}">
                                                    {{ $partner->description }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <!-- Source / Type -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2.5 py-1 inline-flex text-xs leading-4 font-semibold rounded-full bg-purple-100 text-purple-800">
                                        <i class="fas fa-handshake mr-1.5 self-center"></i> Partenaire externe
                                    </span>
                                </td>

                                <!-- Website -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($partner->website)
                                        <a href="{{ $partner->website }}" target="_blank" rel="noopener noreferrer"
                                            class="text-indigo-600 hover:text-indigo-900 hover:underline inline-flex items-center">
                                            {{ Str::limit(preg_replace('#^https?://#', '', $partner->website), 30) }}
                                            <i class="fas fa-external-link-alt ml-1.5 text-xs text-gray-400"></i>
                                        </a>
                                    @else
                                        <span class="text-gray-400 italic text-xs">Aucun</span>
                                    @endif
                                </td>

                                <!-- Order -->
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500">
                                    <span class="bg-gray-100 px-2 py-0.5 rounded text-gray-600 font-mono">
                                        Ordre : {{ $partner->display_order }}
                                    </span>
                                </td>

                                <!-- Status -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <form action="{{ route('admin.partners.toggle-status', $partner) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            class="px-2.5 py-1 inline-flex text-xs leading-4 font-semibold rounded-full cursor-pointer transition-colors {{ $partner->is_active ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}"
                                            title="Cliquez pour changer la visibilité">
                                            <i class="fas {{ $partner->is_active ? 'fa-check-circle' : 'fa-eye-slash' }} mr-1.5 self-center"></i>
                                            {{ $partner->is_active ? 'Visible' : 'Masqué' }}
                                        </button>
                                    </form>
                                </td>

                                <!-- Actions -->
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('admin.partners.edit', $partner) }}"
                                        class="text-indigo-600 hover:text-indigo-900 mr-3 inline-block" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.partners.destroy', $partner) }}" method="POST"
                                        class="inline-block"
                                        onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer le partenaire « {{ addslashes($partner->name) }} » ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900" title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    @endif

                    {{-- 2. BRILLIO ORGANIZATIONS WITH LOGO --}}
                    @if($tab === 'all' || $tab === 'organizations')
                        @foreach($organizationPartners as $org)
                            @php $hasResults = true; @endphp
                            <tr class="hover:bg-gray-50/70 transition-colors bg-blue-50/20">
                                <!-- Logo & Name -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-12 w-20 bg-gray-50 border border-gray-100 rounded-lg p-1.5 flex items-center justify-center">
                                            <img class="max-h-full max-w-full object-contain" src="{{ $org->logo_url }}" alt="{{ $org->name }}">
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-bold text-gray-900 flex items-center">
                                                {{ $org->name }}
                                                <a href="{{ route('admin.organizations.show', $org) }}" class="text-indigo-600 hover:text-indigo-800 ml-2" title="Voir l'organisation">
                                                    <i class="fas fa-arrow-up-right-from-square text-xs"></i>
                                                </a>
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                {{ $org->contact_email }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Source / Type -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2.5 py-1 inline-flex text-xs leading-4 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        <i class="fas fa-building mr-1.5 self-center"></i> Organisation Brillio
                                    </span>
                                </td>

                                <!-- Website -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($org->website)
                                        <a href="{{ $org->website }}" target="_blank" rel="noopener noreferrer"
                                            class="text-indigo-600 hover:text-indigo-900 hover:underline inline-flex items-center">
                                            {{ Str::limit(preg_replace('#^https?://#', '', $org->website), 30) }}
                                            <i class="fas fa-external-link-alt ml-1.5 text-xs text-gray-400"></i>
                                        </a>
                                    @else
                                        <span class="text-gray-400 italic text-xs">Non renseigné</span>
                                    @endif
                                </td>

                                <!-- Plan / Info -->
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $org->subscription_plan === 'free' ? 'bg-gray-100 text-gray-800' : ($org->subscription_plan === 'pro' ? 'bg-purple-100 text-purple-800' : ($org->subscription_plan === 'establishment' ? 'bg-emerald-100 text-emerald-800' : 'bg-indigo-100 text-indigo-800')) }}">
                                        Plan {{ ucfirst($org->subscription_plan) }}
                                    </span>
                                </td>

                                <!-- Status -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2.5 py-1 inline-flex text-xs leading-4 font-semibold rounded-full bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1.5 self-center"></i> Visible
                                    </span>
                                </td>

                                <!-- Actions -->
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('admin.organizations.show', $org) }}"
                                        class="text-indigo-600 hover:text-indigo-900 mr-3 inline-block" title="Voir l'organisation">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.organizations.edit', $org) }}"
                                        class="text-yellow-600 hover:text-yellow-900 mr-2 inline-block" title="Modifier l'organisation">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    @endif

                    @if(!$hasResults)
                        <tr>
                            <td colspan="6" class="px-6 py-12 whitespace-nowrap text-center text-sm text-gray-500">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="fas fa-handshake text-gray-300 text-4xl mb-3"></i>
                                    <span class="font-medium">Aucun partenaire trouvé</span>
                                    <p class="text-xs text-gray-400 mt-1">
                                        @if($search)
                                            Aucun résultat ne correspond à votre recherche « {{ $search }} ».
                                        @else
                                            Commencez par ajouter votre premier partenaire externe.
                                        @endif
                                    </p>
                                    @if(!$search)
                                        <a href="{{ route('admin.partners.create') }}"
                                            class="mt-4 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold py-2 px-4 rounded-lg shadow-sm">
                                            <i class="fas fa-plus mr-1"></i> Ajouter un Partenaire
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endif

                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
