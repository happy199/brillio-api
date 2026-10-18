@extends('layouts.admin')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto" x-data="{ showAddModal: false, showImportModal: false }">

    <!-- En-tête de la page -->
    <div class="sm:flex sm:justify-between sm:items-center mb-6">
        <div>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-rose-500 to-rose-600 text-white flex items-center justify-center shadow-md shadow-rose-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Exclusions & Protection SMTP (Anti-Bounce)</h1>
                    <p class="text-sm text-slate-500 mt-0.5">Désactivation préventive des adresses e-mails en échec pour protéger Namecheap et éviter tout blocage du domaine.</p>
                </div>
            </div>
        </div>

        <!-- Boutons d'action -->
        <div class="mt-4 sm:mt-0 flex flex-wrap gap-2">
            <!-- Importer des rejets (bounces) -->
            <button type="button" @click="showImportModal = true" class="px-3.5 py-2 bg-rose-600 text-white rounded-lg hover:bg-rose-700 text-xs sm:text-sm font-semibold flex items-center shadow-sm transition-colors">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                </svg>
                Scanner / Importer des Rejets (NDR)
            </button>

            <!-- Scanner les domaines sans MX -->
            <form action="{{ route('admin.audits.suppressions.scan-domains') }}" method="POST" class="inline-block" onsubmit="return confirm('Vérifier les serveurs de messagerie (MX) des utilisateurs pour exclure les faux domaines ?');">
                @csrf
                <button type="submit" class="px-3.5 py-2 bg-white border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 text-xs sm:text-sm font-semibold flex items-center shadow-sm transition-colors">
                    <svg class="w-4 h-4 mr-1.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    Vérifier les DNS MX
                </button>
            </form>

            <!-- Ajouter manuellement -->
            <button type="button" @click="showAddModal = true" class="px-3.5 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-xs sm:text-sm font-semibold flex items-center shadow-sm transition-colors">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Ajouter manuellement
            </button>
        </div>
    </div>

    <!-- Feedback Flash messages -->
    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl flex items-start gap-3 shadow-sm">
            <svg class="w-5 h-5 text-emerald-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div class="text-sm font-medium">{{ session('success') }}</div>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl flex items-start gap-3 shadow-sm">
            <svg class="w-5 h-5 text-rose-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div class="text-sm font-medium">{{ session('error') }}</div>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl shadow-sm">
            <ul class="list-disc list-inside text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Cartes d'indicateurs de rebonds -->
    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-5 gap-3 mb-6">
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
            <div class="text-xs font-semibold uppercase text-slate-400">Total Exclusions</div>
            <div class="text-2xl font-bold text-slate-800 mt-1">{{ $stats['total'] }}</div>
            <div class="text-xs text-slate-500 mt-0.5">Adresses protégées</div>
        </div>

        <div class="bg-white rounded-xl border border-rose-100 p-4 shadow-sm bg-rose-50/20">
            <div class="text-xs font-semibold uppercase text-rose-600">Rejets Définitifs (550)</div>
            <div class="text-2xl font-bold text-rose-700 mt-1">{{ $stats['hard'] }}</div>
            <div class="text-xs text-rose-500 mt-0.5">Comptes inexistants</div>
        </div>

        <div class="bg-white rounded-xl border border-amber-100 p-4 shadow-sm bg-amber-50/20">
            <div class="text-xs font-semibold uppercase text-amber-600">Boîtes Saturées (452)</div>
            <div class="text-2xl font-bold text-amber-700 mt-1">{{ $stats['soft'] }}</div>
            <div class="text-xs text-amber-500 mt-0.5">Espace insuffisant</div>
        </div>

        <div class="bg-white rounded-xl border border-indigo-100 p-4 shadow-sm bg-indigo-50/20">
            <div class="text-xs font-semibold uppercase text-indigo-600">Domaines Invalides</div>
            <div class="text-2xl font-bold text-indigo-700 mt-1">{{ $stats['dns'] }}</div>
            <div class="text-xs text-indigo-500 mt-0.5">Sans serveur MX</div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
            <div class="text-xs font-semibold uppercase text-slate-500">Ajouts Manuels</div>
            <div class="text-2xl font-bold text-slate-700 mt-1">{{ $stats['manual'] }}</div>
            <div class="text-xs text-slate-500 mt-0.5">Par l'administrateur</div>
        </div>
    </div>

    <!-- Alert Infobox explicative -->
    <div class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-xl text-amber-900 text-sm flex items-start gap-3 shadow-sm">
        <svg class="w-5 h-5 text-amber-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <div>
            <strong class="font-semibold text-amber-950">Pourquoi ces rejets surviennent et comment nous protégeons Namecheap :</strong>
            Les envois SMTP chez Namecheap sont acceptés en file d'attente (250 OK) puis rejetés plus tard par Google ou Yahoo si l'adresse n'existe pas (<code class="bg-amber-100 px-1 py-0.5 rounded text-xs font-mono">550-5.1.1 NoSuchUser</code>) ou si la boîte est saturée (<code class="bg-amber-100 px-1 py-0.5 rounded text-xs font-mono">452-4.2.2 OverQuotaTemp</code>). Dès qu'une adresse figure sur cette liste, **aucun e-mail ne quitte plus le serveur vers ce destinataire**, garantissant que votre quota et réputation Namecheap restent impeccables.
        </div>
    </div>

    <!-- Filtres -->
    <div class="bg-white rounded-xl shadow-sm mb-6 p-4 border border-slate-200">
        <form method="GET" action="{{ route('admin.audits.suppressions') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
            <div class="sm:col-span-5">
                <label for="suppression_search" class="sr-only">Rechercher une adresse e-mail ou motif</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input id="suppression_search" type="text" name="search" placeholder="Rechercher par adresse email ou raison..." value="{{ request('search') }}"
                        class="w-full pl-9 pr-3 py-2 form-input bg-slate-50 border-slate-200 focus:bg-white focus:border-indigo-500 rounded-lg text-sm">
                </div>
            </div>

            <div class="sm:col-span-3">
                <label for="suppression_bounce_filter" class="sr-only">Type de rejet</label>
                <select id="suppression_bounce_filter" name="bounce_type" class="w-full form-select bg-slate-50 border-slate-200 focus:bg-white focus:border-indigo-500 rounded-lg text-sm">
                    <option value="all">Tous les types de rebonds</option>
                    <option value="hard" {{ request('bounce_type') == 'hard' ? 'selected' : '' }}>Rejet définitif (550)</option>
                    <option value="soft" {{ request('bounce_type') == 'soft' ? 'selected' : '' }}>Boîte saturée (452)</option>
                    <option value="preflight_dns" {{ request('bounce_type') == 'preflight_dns' ? 'selected' : '' }}>Domaine sans MX</option>
                </select>
            </div>

            <div class="sm:col-span-2">
                <label for="suppression_source_filter" class="sr-only">Origine</label>
                <select id="suppression_source_filter" name="source" class="w-full form-select bg-slate-50 border-slate-200 focus:bg-white focus:border-indigo-500 rounded-lg text-sm">
                    <option value="all">Toutes origines</option>
                    <option value="system_auto" {{ request('source') == 'system_auto' ? 'selected' : '' }}>Automatique</option>
                    <option value="admin_manual" {{ request('source') == 'admin_manual' ? 'selected' : '' }}>Manuel</option>
                </select>
            </div>

            <div class="sm:col-span-2 flex gap-2">
                <button type="submit" class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-semibold transition-colors shadow-sm">
                    Filtrer
                </button>
                @if(request('search') || (request('source') && request('source') !== 'all') || (request('bounce_type') && request('bounce_type') !== 'all'))
                    <a href="{{ route('admin.audits.suppressions') }}" class="px-3 py-2 bg-white border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 text-sm font-medium transition-colors" title="Effacer les filtres">
                        ✕
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white shadow-sm rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table-auto w-full">
                <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3 whitespace-nowrap"><div class="font-semibold text-left">Adresse E-mail</div></th>
                        <th class="px-4 py-3 whitespace-nowrap"><div class="font-semibold text-left">Type de Rejet</div></th>
                        <th class="px-4 py-3 whitespace-nowrap"><div class="font-semibold text-left">Motif / Erreur Détectée</div></th>
                        <th class="px-4 py-3 whitespace-nowrap"><div class="font-semibold text-center">Tentatives</div></th>
                        <th class="px-4 py-3 whitespace-nowrap"><div class="font-semibold text-left">Date d'Exclusion</div></th>
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
                            @if($suppression->bounce_type === 'hard')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-rose-100 text-rose-800 border border-rose-200">
                                    Compte Inexistant (550)
                                </span>
                            @elseif($suppression->bounce_type === 'soft')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 border border-amber-200">
                                    Boîte Saturée (452)
                                </span>
                            @elseif($suppression->bounce_type === 'preflight_dns')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 border border-purple-200">
                                    Domaine sans MX
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 border border-blue-200">
                                    Manuel
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 max-w-md">
                            <div class="text-slate-700 text-xs font-normal truncate" title="{{ $suppression->reason }}">{{ $suppression->reason }}</div>
                            @if($suppression->creator)
                                <div class="text-xs text-slate-400 mt-0.5">Par: {{ $suppression->creator->name }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-slate-100 text-slate-700">
                                {{ $suppression->bounce_count ?? 1 }}x
                            </span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-slate-600 text-xs">
                            {{ $suppression->created_at->format('d/m/Y H:i') }}
                            <div class="text-slate-400 text-[11px]">{{ $suppression->created_at->diffForHumans() }}</div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-center">
                            <form action="{{ route('admin.audits.suppressions.destroy', $suppression) }}" method="POST" onsubmit="return confirm('Voulez-vous vraiment retirer {{ $suppression->email }} de la liste d\'exclusion ? Les envois vers cette adresse seront réactivés.');" class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center px-2.5 py-1 border border-rose-200 text-xs font-medium rounded-lg text-rose-700 bg-rose-50 hover:bg-rose-100 hover:border-rose-300 transition-colors shadow-sm">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Débloquer
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-slate-500">
                            Aucune adresse e-mail dans la liste d'exclusion.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($suppressions->hasPages())
        <div class="px-4 py-3 border-t border-slate-200">
            {{ $suppressions->withQueryString()->links() }}
        </div>
        @endif
    </div>

    <!-- Modal d'Importation / Scan de Rejets NDR (Bounces) -->
    <dialog :open="showImportModal" x-show="showImportModal" x-cloak style="display: none;" @keydown.escape.window="showImportModal = false" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 m-0 max-w-none max-h-none w-full h-full border-0">
        <div @click.away="showImportModal = false" @keydown.escape="showImportModal = false" class="bg-white rounded-xl shadow-2xl overflow-hidden w-full max-w-2xl border border-slate-200">
            <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800 text-base">Scanner & Importer des Rejets d'e-mails (Bounces NDR)</h3>
                        <p class="text-xs text-slate-500">Extraction automatique des adresses en échec à partir de vos messages de rebond.</p>
                    </div>
                </div>
                <button type="button" @click="showImportModal = false" class="text-slate-400 hover:text-slate-600 text-2xl leading-none">&times;</button>
            </div>
            <form action="{{ route('admin.audits.suppressions.import-bounces') }}" method="POST" class="p-6">
                @csrf
                <div class="mb-4">
                    <label for="bounce_text" class="block text-sm font-semibold text-slate-700 mb-1">
                        Collez le texte brut du mail de rejet (Webmail / Jellyfish / Google Daemon) :
                    </label>
                    <p class="text-xs text-slate-500 mb-2">
                        Vous pouvez copier-coller l'intégralité d'un ou plusieurs e-mails d'erreur reçus (ex: <em>« Delivery incomplete There was a temporary problem delivering your message to... 452-4.2.2 The recipient's inbox is out of storage space »</em> ou <em>« 550-5.1.1 The email account that you tried to reach does not exist »</em>).
                    </p>
                    <textarea id="bounce_text" name="bounce_text" rows="8" required
                        placeholder="Exemple :
Delivery to the following recipient failed permanently:
rachid.bello.bj@gmail.com
Technical details of permanent failure:
550-5.1.1 The email account that you tried to reach does not exist."
                        class="w-full form-textarea bg-slate-50 border-slate-300 focus:bg-white focus:border-rose-500 rounded-lg text-xs font-mono"></textarea>
                </div>

                <div class="mb-4 flex items-start gap-2.5 bg-indigo-50/70 border border-indigo-100 p-3 rounded-xl">
                    <input type="checkbox" id="use_ai" name="use_ai" value="1" checked class="mt-0.5 rounded border-indigo-300 text-indigo-600 focus:ring-indigo-500">
                    <label for="use_ai" class="text-xs text-indigo-950 leading-relaxed">
                        <span class="font-bold text-indigo-900">Analyse intelligente par l'IA Brillio :</span> En cas de message d'erreur complexe ou tronqué, notre IA diagnostique la cause SMTP, extrait le destinataire et recommande l'action à mener pour préserver votre serveur Namecheap.
                    </label>
                </div>

                <div class="flex justify-end gap-3 pt-2 border-t border-slate-100">
                    <button type="button" @click="showImportModal = false" class="px-4 py-2 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 text-sm font-medium">Annuler</button>
                    <button type="submit" class="px-4 py-2 bg-rose-600 text-white rounded-lg hover:bg-rose-700 text-sm font-semibold shadow-sm transition-colors">
                        Analyser & Exclure les adresses
                    </button>
                </div>
            </form>
        </div>
    </dialog>

    <!-- Modal d'ajout d'exclusion manuelle -->
    <dialog :open="showAddModal" x-show="showAddModal" x-cloak style="display: none;" @keydown.escape.window="showAddModal = false" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 m-0 max-w-none max-h-none w-full h-full border-0">
        <div @click.away="showAddModal = false" @keydown.escape="showAddModal = false" class="bg-white rounded-xl shadow-2xl overflow-hidden w-full max-w-lg border border-slate-200">
            <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                <h3 class="font-bold text-slate-800 text-base">Ajouter une exclusion manuelle</h3>
                <button type="button" @click="showAddModal = false" class="text-slate-400 hover:text-slate-600 text-2xl leading-none">&times;</button>
            </div>
            <form action="{{ route('admin.audits.suppressions.store') }}" method="POST" class="p-6">
                @csrf
                <div class="mb-4">
                    <label for="suppression_email" class="block text-sm font-medium text-slate-700 mb-1">Adresse E-mail <span class="text-rose-500">*</span></label>
                    <input id="suppression_email" type="email" name="email" required placeholder="ex: utilisateur@example.com" class="w-full form-input bg-slate-50 border-slate-300 focus:bg-white focus:border-indigo-500 rounded-lg text-sm">
                </div>
                <div class="mb-6">
                    <label for="suppression_reason" class="block text-sm font-medium text-slate-700 mb-1">Motif / Raison d'exclusion <span class="text-rose-500">*</span></label>
                    <input id="suppression_reason" type="text" name="reason" required placeholder="ex: Boîte mail saturée / Demande de désinscription" class="w-full form-input bg-slate-50 border-slate-300 focus:bg-white focus:border-indigo-500 rounded-lg text-sm">
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" @click="showAddModal = false" class="px-4 py-2 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 text-sm font-medium">Annuler</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-semibold shadow-sm">Enregistrer l'exclusion</button>
                </div>
            </form>
        </div>
    </dialog>

</div>
@endsection
