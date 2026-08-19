@extends('layouts.admin')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto" x-data="{ showAddModal: false }">

    <!-- Page header -->
    <div class="sm:flex sm:justify-between sm:items-center mb-8">
        <div class="mb-4 sm:mb-0">
            <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Exclusions & Blacklist Emails</h1>
            <p class="text-sm text-slate-500 mt-1">Gestion des adresses e-mails désactivées pour protéger la réputation du domaine SMTP.</p>
        </div>
        <div>
            <button @click="showAddModal = true" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium flex items-center shadow-sm transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Ajouter une exclusion manuelle
            </button>
        </div>
    </div>

    <!-- Feedback Flash messages -->
    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg flex items-center justify-between shadow-sm">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-3 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-lg shadow-sm">
            <ul class="list-disc list-inside text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Alert Infobox -->
    <div class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-lg text-amber-900 text-sm flex items-start gap-3 shadow-sm">
        <svg class="w-5 h-5 text-amber-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <div>
            <strong class="font-semibold text-amber-950">Protection Anti-Spam & Conservation des Comptes :</strong>
            Les comptes associés aux adresses ci-dessous restent 100% actifs dans l'application (les utilisateurs/mentors peuvent continuer d'utiliser Brillio normally). Le système empêche uniquement l'envoi d'e-mails vers ces boîtes (ex: boîtes saturées, 452 4.2.2 ou refus de livraison) afin de préserver la réputation SMTP auprès des fournisseurs.
        </div>
    </div>

    <!-- Filtres -->
    <div class="bg-white rounded-lg shadow-sm mb-6 p-4 border border-slate-200">
        <form method="GET" action="{{ route('admin.audits.suppressions') }}" class="flex flex-col sm:flex-row gap-4">
            <div class="flex-grow">
                <input type="text" name="search" placeholder="Rechercher par adresse email ou raison" value="{{ request('search') }}" class="w-full form-input bg-slate-100 border-transparent focus:bg-white focus:border-slate-300">
            </div>
            <div class="w-full sm:w-48">
                <select name="source" class="w-full form-select bg-slate-100 border-transparent focus:bg-white focus:border-slate-300">
                    <option value="">Toutes les origines</option>
                    <option value="system_auto" {{ request('source') == 'system_auto' ? 'selected' : '' }}>Automatique (Système)</option>
                    <option value="admin_manual" {{ request('source') == 'admin_manual' ? 'selected' : '' }}>Manuel (Admin)</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium flex-shrink-0 transition-colors">
                Filtrer
            </button>
            @if(request('search') || request('source'))
                <a href="{{ route('admin.audits.suppressions') }}" class="px-4 py-2 bg-white border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 text-sm font-medium flex-shrink-0 transition-colors">Effacer</a>
            @endif
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white shadow-lg rounded-sm border border-slate-200">
        <div class="overflow-x-auto">
            <table class="table-auto w-full">
                <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3 whitespace-nowrap"><div class="font-semibold text-left">Adresse E-mail</div></th>
                        <th class="px-4 py-3 whitespace-nowrap"><div class="font-semibold text-left">Origine</div></th>
                        <th class="px-4 py-3 whitespace-nowrap"><div class="font-semibold text-left">Raison / Motif</div></th>
                        <th class="px-4 py-3 whitespace-nowrap"><div class="font-semibold text-left">Date d'Ajout</div></th>
                        <th class="px-4 py-3 whitespace-nowrap"><div class="font-semibold text-center">Action</div></th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-slate-200">
                    @forelse($suppressions as $suppression)
                    <tr>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="font-semibold text-slate-800">{{ $suppression->email }}</div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($suppression->source === 'system_auto')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                    Automatique (Système)
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    Manuel (Admin)
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 max-w-md">
                            <div class="text-slate-700 font-normal truncate" title="{{ $suppression->reason }}">{{ $suppression->reason }}</div>
                            @if($suppression->creator)
                                <div class="text-xs text-slate-400 mt-0.5">Par: {{ $suppression->creator->name }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-slate-600">
                            {{ $suppression->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-center">
                            <form action="{{ route('admin.audits.suppressions.destroy', $suppression) }}" method="POST" onsubmit="return confirm('Voulez-vous vraiment retirer {{ $suppression->email }} de la liste d\'exclusion ? Les envois vers cette adresse seront réactivés.');" class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-rose-200 text-xs font-medium rounded-lg text-rose-700 bg-rose-50 hover:bg-rose-100 hover:border-rose-300 transition-colors shadow-sm">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Retirer l'exclusion
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-slate-500">
                            Aucune adresse e-mail dans la liste d'exclusion.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($suppressions->hasPages())
        <div class="px-4 py-3 border-t border-slate-200">
            {{ $suppressions->links() }}
        </div>
        @endif
    </div>

    <!-- Modal d'ajout d'exclusion -->
    <div x-show="showAddModal" style="display: none;" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="showAddModal = false" class="bg-white rounded-xl shadow-2xl overflow-hidden w-full max-w-lg border border-slate-200">
            <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                <h3 class="font-bold text-slate-800 text-lg">Ajouter une adresse en exclusion</h3>
                <button type="button" @click="showAddModal = false" class="text-slate-400 hover:text-slate-600 text-2xl leading-none">&times;</button>
            </div>
            <form action="{{ route('admin.audits.suppressions.store') }}" method="POST" class="p-6">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Adresse E-mail <span class="text-rose-500">*</span></label>
                    <input type="email" name="email" required placeholder="ex: utilisateur@example.com" class="w-full form-input bg-slate-50 border-slate-300 focus:bg-white focus:border-indigo-500 rounded-lg">
                </div>
                <div class="mb-6">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Motif / Raison d'exclusion <span class="text-rose-500">*</span></label>
                    <input type="text" name="reason" required placeholder="ex: Boîte mail saturée / Demande d'unsubcribe" class="w-full form-input bg-slate-50 border-slate-300 focus:bg-white focus:border-indigo-500 rounded-lg">
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" @click="showAddModal = false" class="px-4 py-2 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 text-sm font-medium">Annuler</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium">Enregistrer l'exclusion</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
