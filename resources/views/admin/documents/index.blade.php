@extends('layouts.admin')

@section('title', 'Documents')
@section('header', 'Gestion des documents')

@section('content')
<div class="space-y-6" x-data="{
    showModal: false,
    previewUrl: '',
    fileName: '',
    isImage: false,
    isPdf: false,
    currentDocId: null,
    isCvDoc: false,
    openPreview(url, name, ext, docId = null, isCv = false) {
        this.previewUrl = url;
        this.fileName = name;
        this.isImage = ['jpg','jpeg','png','gif','webp','svg'].includes(ext);
        this.isPdf = ext === 'pdf';
        this.currentDocId = docId;
        this.isCvDoc = isCv;
        this.showModal = true;
    },
    showCvModal: false,
    cvLoading: false,
    cvData: null,
    cvError: '',
    async openCvAnalysis(docId) {
        this.currentDocId = docId;
        this.showCvModal = true;
        this.cvLoading = true;
        this.cvData = null;
        this.cvError = '';
        try {
            const url = '{{ route('admin.documents.index') }}/' + docId + '/cv-analysis';
            const res = await fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            if (!res.ok) {
                const errJson = await res.json().catch(() => ({}));
                this.cvError = errJson.message || ('Erreur ' + res.status + ' lors de la récupération.');
                return;
            }
            const json = await res.json();
            if (json.success && json.analysis) {
                this.cvData = json.analysis;
            } else {
                this.cvError = json.message || 'Impossible de charger l\'analyse du CV.';
            }
        } catch (e) {
            console.error('Erreur openCvAnalysis:', e);
            this.cvError = 'Une erreur réseau est survenue lors de la récupération de l\'analyse.';
        } finally {
            this.cvLoading = false;
        }
    },
    showExportModal: false,
    selectAllFields(val) {
        const checkboxes = document.querySelectorAll('.export-field-checkbox');
        checkboxes.forEach(cb => cb.checked = val);
    }
}">
    <!-- Stats rapides -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
        <div class="bg-white rounded-xl p-4 shadow-sm">
            <p class="text-sm text-gray-500">Total documents</p>
            <p class="text-2xl font-bold text-gray-900">{{ $documents->total() }}</p>
        </div>
        <div class="bg-blue-50 rounded-xl p-4">
            <p class="text-sm text-blue-600">Bulletins</p>
            <p class="text-2xl font-bold text-blue-700">
                {{ App\Models\AcademicDocument::where('document_type', 'bulletin')->count() }}
            </p>
        </div>
        <div class="bg-green-50 rounded-xl p-4">
            <p class="text-sm text-green-600">Relevés de notes</p>
            <p class="text-2xl font-bold text-green-700">
                {{ App\Models\AcademicDocument::where('document_type', 'releve_notes')->count() }}
            </p>
        </div>
        <div class="bg-purple-50 rounded-xl p-4">
            <p class="text-sm text-purple-600">Diplômes</p>
            <p class="text-2xl font-bold text-purple-700">
                {{ App\Models\AcademicDocument::where('document_type', 'diplome')->count() }}
            </p>
        </div>
        <div class="bg-amber-50 rounded-xl p-4 border border-amber-200/60">
            <div class="flex items-center justify-between">
                <p class="text-sm text-amber-700 font-medium">CVs Originaux</p>
                <span class="text-[10px] font-bold bg-amber-200 text-amber-800 px-1.5 py-0.5 rounded">IA</span>
            </div>
            <p class="text-2xl font-bold text-amber-800">
                {{ App\Models\AcademicDocument::where('document_type', 'cv')->count() }}
            </p>
        </div>
    </div>

    <!-- Filtres & Extraction -->
    <div class="bg-white rounded-xl shadow-sm p-4">
        <form action="{{ route('admin.documents.index') }}" method="GET" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <label for="filter-search" class="block text-sm font-medium text-gray-700 mb-1">Recherche</label>
                <input type="text"
                       id="filter-search"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Nom du fichier..."
                       class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="w-48">
                <label for="filter-type" class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                <select id="filter-type" name="type" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <option value="">Tous les types</option>
                    @foreach($documentTypes as $key => $label)
                        <option value="{{ $key }}" {{ request('type') === $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium transition-colors">
                Filtrer
            </button>

            @if(request()->hasAny(['search', 'type']))
                <a href="{{ route('admin.documents.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 text-gray-700 transition-colors">
                    Réinitialiser
                </a>
            @endif

            <button type="button"
                    @click="showExportModal = true"
                    class="ml-auto px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-semibold text-sm flex items-center gap-2 shadow-xs transition-colors cursor-pointer"
                    title="Extraire un fichier Excel/CSV des données candidats pour l'équipe commerciale">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Extraction Commerciale (CV)
            </button>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-x-auto border border-gray-100">
        <table class="w-full text-left divide-y divide-gray-200">
            <thead class="bg-gray-50/80">
                <tr>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Document</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Utilisateur</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Taille</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @forelse($documents as $document)
                    <tr class="hover:bg-gray-50/80 transition-colors">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="flex-shrink-0 w-9 h-9 bg-gray-100 rounded-lg flex items-center justify-center">
                                    @php
                                        $extension = pathinfo($document->file_name, PATHINFO_EXTENSION);
                                        $iconColor = match($extension) {
                                            'pdf' => 'text-red-500',
                                            'doc', 'docx' => 'text-blue-500',
                                            'jpg', 'jpeg', 'png' => 'text-green-500',
                                            default => 'text-gray-500'
                                        };
                                    @endphp
                                    <svg class="w-5 h-5 {{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <div class="text-xs sm:text-sm font-semibold text-gray-900 truncate max-w-[180px] sm:max-w-[240px]" title="{{ $document->file_name }}">
                                        {{ Str::limit($document->file_name, 35) }}
                                    </div>
                                    <div class="text-[10px] text-gray-400 font-bold">
                                        {{ strtoupper($extension) }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($document->user)
                                <a href="{{ route('admin.users.show', $document->user) }}" class="text-indigo-600 hover:text-indigo-900 block">
                                    <div class="text-xs sm:text-sm font-medium leading-snug">{{ $document->user->name }}</div>
                                    <div class="text-[11px] text-gray-500">{{ $document->user->email }}</div>
                                </a>
                            @else
                                <span class="text-xs text-gray-400">Utilisateur supprimé</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @php
                                $typeColors = [
                                    'bulletin' => 'bg-blue-50 text-blue-700 border-blue-200',
                                    'releve_notes' => 'bg-green-50 text-green-700 border-green-200',
                                    'diplome' => 'bg-purple-50 text-purple-700 border-purple-200',
                                    'certificat' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                                    'attestation' => 'bg-orange-50 text-orange-700 border-orange-200',
                                    'autre' => 'bg-gray-50 text-gray-700 border-gray-200',
                                ];
                            @endphp
                            <span class="px-2.5 py-0.5 text-[11px] font-semibold rounded-full border {{ $typeColors[$document->document_type] ?? 'bg-gray-50 text-gray-700 border-gray-200' }}">
                                {{ $documentTypes[$document->document_type] ?? $document->document_type }}
                            </span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-500">
                            {{ number_format($document->file_size / 1024, 1) }} Ko
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-500">
                            {{ $document->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-right text-xs">
                            <div class="flex items-center justify-end gap-2.5">
                                @if($document->document_type === 'cv')
                                    <button type="button"
                                            @click="openCvAnalysis({{ $document->id }})"
                                            class="text-amber-600 hover:text-amber-800 font-bold transition-colors inline-flex items-center gap-1 cursor-pointer"
                                            title="Consulter l'évaluation ATS et les propositions formulées par l'IA">
                                        <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                        </svg>
                                        Propositions IA
                                    </button>
                                    <span class="text-gray-300">|</span>
                                @endif
                                <button type="button"
                                        id="preview-btn-{{ $document->id }}"
                                        @click="openPreview('{{ route('admin.documents.preview', $document) }}', '{{ addslashes($document->file_name) }}', '{{ strtolower($extension) }}', {{ $document->id }}, {{ $document->document_type === 'cv' ? 'true' : 'false' }})"
                                        class="text-indigo-600 hover:text-indigo-900 font-bold transition-colors">
                                    Visualiser
                                </button>
                                <span class="text-gray-300">|</span>
                                <a href="{{ route('admin.documents.download', $document) }}"
                                   class="text-gray-600 hover:text-gray-900 font-medium transition-colors">
                                    Télécharger
                                </a>
                                <span class="text-gray-300">|</span>
                                <form action="{{ route('admin.documents.destroy', $document) }}"
                                      method="POST"
                                      class="inline"
                                      onsubmit="return confirm('Supprimer ce document ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 font-medium transition-colors">
                                        Supprimer
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <p class="mt-2">Aucun document trouvé</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($documents->hasPages())
            <div class="px-6 py-4 border-t">
                {{ $documents->appends(request()->query())->links() }}
            </div>
        @endif
    </div>

    <!-- Modal de prévisualisation -->
    <div x-show="showModal"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 sm:p-6"
         role="dialog"
         aria-labelledby="modal-title"
         aria-modal="true"
         style="display: none;">
        <div class="fixed inset-0 bg-gray-900/75 transition-opacity"
             @click="showModal = false"
             aria-hidden="true"></div>

        <div class="relative z-10 w-full max-w-5xl p-6 bg-white shadow-2xl rounded-2xl flex flex-col max-h-[92vh] overflow-hidden text-left">
            <div class="flex items-center justify-between pb-4 border-b border-gray-200 shrink-0">
                <h3 id="modal-title" class="text-lg font-bold text-gray-900 truncate max-w-xl" x-text="fileName">Prévisualisation du document</h3>
                <div class="flex items-center gap-3">
                    <template x-if="isCvDoc && currentDocId">
                        <button @click="showModal = false; openCvAnalysis(currentDocId)"
                                type="button"
                                class="px-3 py-1.5 text-xs font-semibold text-amber-700 bg-amber-50 hover:bg-amber-100 rounded-lg transition-colors inline-flex items-center gap-1 cursor-pointer">
                            <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            <span>Propositions IA</span>
                        </button>
                    </template>
                    <a :href="previewUrl"
                       target="_blank"
                       rel="noopener"
                       class="px-3 py-1.5 text-xs font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition-colors inline-flex items-center">
                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                        Ouvrir dans un nouvel onglet
                    </a>
                    <button @click="showModal = false"
                            type="button"
                            class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100 cursor-pointer">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="mt-4 bg-gray-100 rounded-xl overflow-hidden flex-1 flex items-center justify-center p-2 min-h-[500px]">
                <template x-if="isImage">
                    <img :src="previewUrl" :alt="fileName || 'Prévisualisation image'" class="max-h-[70vh] object-contain mx-auto rounded-lg shadow-sm">
                </template>
                <template x-if="!isImage">
                    <iframe :src="previewUrl" :title="fileName || 'Prévisualisation du document'" class="w-full h-[70vh] rounded-lg border-0 bg-white"></iframe>
                </template>
            </div>
        </div>
    </div>

    <!-- Modal Propositions & Analyse IA du CV -->
    <div x-show="showCvModal"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 sm:p-6"
         role="dialog"
         aria-labelledby="cv-modal-title"
         aria-modal="true"
         style="display: none;">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-900/75 transition-opacity"
             @click="showCvModal = false"
             aria-hidden="true"></div>

        <!-- Conteneur Carte Blanche -->
        <div class="relative z-10 w-full max-w-4xl p-6 bg-white shadow-2xl rounded-2xl flex flex-col max-h-[90vh] overflow-hidden text-left">
            <!-- En-tête -->
            <div class="flex items-center justify-between pb-4 border-b border-gray-100 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <div>
                        <h3 id="cv-modal-title" class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <span x-text="cvData ? cvData.candidate_name : 'Analyse IA du CV'"></span>
                            <span class="text-xs px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 font-semibold" x-show="cvData">
                                Score ATS : <span x-text="cvData ? cvData.global_score : ''"></span>/100
                            </span>
                        </h3>
                        <p class="text-xs text-gray-500" x-text="cvData ? (cvData.candidate_title + ' • Analysé le ' + cvData.created_at) : (cvError ? 'Erreur de chargement' : 'Récupération des données...')"></p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <template x-if="currentDocId">
                        <button @click="showCvModal = false; const btn = document.getElementById('preview-btn-' + currentDocId); if (btn) btn.click();"
                                type="button"
                                class="px-3 py-1.5 text-xs font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition-colors inline-flex items-center gap-1 cursor-pointer">
                            <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <span>Voir document</span>
                        </button>
                    </template>
                    <button @click="showCvModal = false" type="button" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-lg hover:bg-gray-100 cursor-pointer">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Contenu défilant -->
            <div class="mt-4 overflow-y-auto flex-1 pr-2 space-y-6">
                <!-- Spinner de chargement -->
                <div x-show="cvLoading" class="py-16 text-center">
                    <svg class="w-8 h-8 mx-auto text-amber-500 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="text-sm text-gray-500 mt-2">Récupération des propositions de l'IA...</p>
                </div>

                <!-- Erreur -->
                <div x-show="cvError" class="p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm" x-text="cvError"></div>

                <!-- Données de l'analyse -->
                <template x-if="cvData">
                    <div class="space-y-6">
                        <!-- Coordonnées détectées -->
                        <div class="bg-gray-50 rounded-xl p-4 border border-gray-200/80">
                            <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Coordonnées candidat</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
                                <div class="flex items-center gap-2">
                                    <span class="text-gray-400">📧</span>
                                    <span class="font-medium text-gray-800 truncate" x-text="cvData.contact?.email || 'Non spécifié'"></span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-gray-400">📞</span>
                                    <span class="font-medium text-gray-800" x-text="cvData.contact?.phone || 'Non spécifié'"></span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-gray-400">📍</span>
                                    <span class="font-medium text-gray-800 truncate" x-text="cvData.contact?.location || 'Non spécifié'"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Critères ATS -->
                        <div x-show="cvData.criteria && cvData.criteria.length > 0">
                            <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Critères ATS analysés</h4>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                <template x-for="(crit, idx) in cvData.criteria" :key="idx">
                                    <div class="bg-amber-50/60 rounded-xl p-3 border border-amber-100">
                                        <div class="flex justify-between items-center mb-1">
                                            <span class="text-xs font-semibold text-gray-700 truncate" x-text="crit.name"></span>
                                            <span class="text-xs font-bold text-amber-700" x-text="crit.score + '/100'"></span>
                                        </div>
                                        <div class="w-full bg-amber-200/50 rounded-full h-1.5 overflow-hidden">
                                            <div class="bg-amber-500 h-1.5 rounded-full transition-all" :style="'width: ' + Math.min(100, Math.max(0, crit.score)) + '%'"></div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Points forts & Améliorations recommandées -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Points forts -->
                            <div class="bg-emerald-50/70 border border-emerald-100 rounded-xl p-4">
                                <h4 class="text-xs font-bold text-emerald-800 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Points forts identifiés
                                </h4>
                                <ul class="space-y-1.5 text-xs text-emerald-900">
                                    <template x-for="(str, idx) in (cvData.strengths || [])" :key="idx">
                                        <li class="flex items-start gap-1.5">
                                            <span class="text-emerald-500 font-bold">•</span>
                                            <span x-text="str"></span>
                                        </li>
                                    </template>
                                </ul>
                            </div>

                            <!-- Pistes d'amélioration -->
                            <div class="bg-rose-50/70 border border-rose-100 rounded-xl p-4">
                                <h4 class="text-xs font-bold text-rose-800 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    Axes d'optimisation
                                </h4>
                                <ul class="space-y-1.5 text-xs text-rose-900">
                                    <template x-for="(imp, idx) in (cvData.improvements || [])" :key="idx">
                                        <li class="flex items-start gap-1.5">
                                            <span class="text-rose-500 font-bold">•</span>
                                            <span x-text="imp"></span>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </div>

                        <!-- Profil / Résumé professionnel proposé -->
                        <div x-show="cvData.profil">
                            <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Profil professionnel revalorisé</h4>
                            <div class="p-3 bg-gray-50 rounded-xl border border-gray-200 text-xs text-gray-700 leading-relaxed italic" x-text="cvData.profil"></div>
                        </div>

                        <!-- Expériences reformulées -->
                        <div x-show="cvData.experiences && cvData.experiences.length > 0">
                            <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Expériences & Réalisations revalorisées</h4>
                            <div class="space-y-3">
                                <template x-for="(exp, i) in (cvData.experiences || [])" :key="i">
                                    <div class="p-3 bg-white rounded-xl border border-gray-200 shadow-xs">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <h5 class="text-xs font-bold text-gray-900" x-text="exp.title"></h5>
                                                <p class="text-[11px] text-gray-500" x-text="(exp.company || '') + (exp.period ? ' • ' + exp.period : '')"></p>
                                            </div>
                                        </div>
                                        <!-- Puces chiffrées -->
                                        <template x-if="exp.bullets && exp.bullets.length > 0">
                                            <ul class="mt-2 space-y-1 text-xs text-gray-700">
                                                <template x-for="(b, bi) in exp.bullets" :key="bi">
                                                    <li class="flex items-start gap-1.5">
                                                        <span class="text-indigo-500 font-bold">•</span>
                                                        <span x-text="b"></span>
                                                    </li>
                                                </template>
                                            </ul>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Formations & Compétences -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Formations -->
                            <div x-show="cvData.formation && cvData.formation.length > 0">
                                <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Formations</h4>
                                <div class="space-y-2">
                                    <template x-for="(f, i) in (cvData.formation || [])" :key="i">
                                        <div class="p-2.5 bg-gray-50 rounded-lg border border-gray-100 text-xs">
                                            <div class="font-bold text-gray-800" x-text="f.degree"></div>
                                            <div class="text-[11px] text-gray-500" x-text="(f.school || '') + (f.year ? ' • ' + f.year : '')"></div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Compétences -->
                            <div x-show="cvData.competences && cvData.competences.length > 0">
                                <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Compétences clés</h4>
                                <div class="flex flex-wrap gap-1.5">
                                    <template x-for="(sk, i) in (cvData.competences || [])" :key="i">
                                        <span class="px-2 py-0.5 bg-white border border-gray-200 rounded-md text-xs font-medium text-gray-700" x-text="sk"></span>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Footer modal -->
            <div class="pt-4 border-t border-gray-100 flex justify-end shrink-0">
                <button type="button" @click="showCvModal = false" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold text-xs rounded-xl transition cursor-pointer">
                    Fermer
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Extraction Commerciale des CV -->
    <div x-show="showExportModal"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 sm:p-6"
         role="dialog"
         aria-labelledby="export-modal-title"
         aria-modal="true"
         style="display: none;">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-900/75 transition-opacity"
             @click="showExportModal = false"
             aria-hidden="true"></div>

        <!-- Conteneur Carte Blanche -->
        <div class="relative z-10 w-full max-w-2xl p-6 bg-white shadow-2xl rounded-2xl flex flex-col max-h-[90vh] overflow-hidden text-left">
            <form action="{{ route('admin.documents.export-candidates') }}" method="POST" class="flex flex-col flex-1 overflow-hidden">
                @csrf
                <div class="flex items-center justify-between pb-4 border-b border-gray-100 shrink-0">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <h3 id="export-modal-title" class="text-base font-bold text-gray-900">Extraction Commerciale des Données CV</h3>
                            <p class="text-xs text-gray-500">Sélectionnez les champs et filtres pour générer l'agrégat commercial (Excel/CSV)</p>
                        </div>
                    </div>
                    <button @click="showExportModal = false" type="button" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-lg hover:bg-gray-100 cursor-pointer">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="mt-5 space-y-5 flex-1 overflow-y-auto pr-1">
                    <!-- Sélection des colonnes -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-xs font-bold text-gray-700 uppercase tracking-wider">Champs à exporter</label>
                            <div class="flex gap-2">
                                <button type="button" @click="selectAllFields(true)" class="text-[11px] text-emerald-600 hover:underline font-medium cursor-pointer">Tout cocher</button>
                                <span class="text-gray-300">•</span>
                                <button type="button" @click="selectAllFields(false)" class="text-[11px] text-gray-500 hover:underline font-medium cursor-pointer">Tout décocher</button>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 p-3 bg-gray-50 rounded-xl border border-gray-100">
                            <label for="field_name" class="flex items-center gap-2 text-xs font-medium text-gray-700 cursor-pointer">
                                <input type="checkbox" id="field_name" name="fields[]" value="candidate_name" checked class="export-field-checkbox rounded text-emerald-600 focus:ring-emerald-500">
                                <span>Nom & Prénom</span>
                            </label>
                            <label for="field_phone" class="flex items-center gap-2 text-xs font-medium text-gray-700 cursor-pointer">
                                <input type="checkbox" id="field_phone" name="fields[]" value="phone" checked class="export-field-checkbox rounded text-emerald-600 focus:ring-emerald-500">
                                <span>Téléphone</span>
                            </label>
                            <label for="field_email" class="flex items-center gap-2 text-xs font-medium text-gray-700 cursor-pointer">
                                <input type="checkbox" id="field_email" name="fields[]" value="email" checked class="export-field-checkbox rounded text-emerald-600 focus:ring-emerald-500">
                                <span>Adresse Email</span>
                            </label>
                            <label for="field_title" class="flex items-center gap-2 text-xs font-medium text-gray-700 cursor-pointer">
                                <input type="checkbox" id="field_title" name="fields[]" value="candidate_title" checked class="export-field-checkbox rounded text-emerald-600 focus:ring-emerald-500">
                                <span>Poste / Métier</span>
                            </label>
                            <label for="field_location" class="flex items-center gap-2 text-xs font-medium text-gray-700 cursor-pointer">
                                <input type="checkbox" id="field_location" name="fields[]" value="location" checked class="export-field-checkbox rounded text-emerald-600 focus:ring-emerald-500">
                                <span>Ville / Pays</span>
                            </label>
                            <label for="field_skills" class="flex items-center gap-2 text-xs font-medium text-gray-700 cursor-pointer">
                                <input type="checkbox" id="field_skills" name="fields[]" value="skills" checked class="export-field-checkbox rounded text-emerald-600 focus:ring-emerald-500">
                                <span>Compétences clés</span>
                            </label>
                            <label for="field_score" class="flex items-center gap-2 text-xs font-medium text-gray-700 cursor-pointer">
                                <input type="checkbox" id="field_score" name="fields[]" value="global_score" checked class="export-field-checkbox rounded text-emerald-600 focus:ring-emerald-500">
                                <span>Score ATS (/100)</span>
                            </label>
                            <label for="field_user" class="flex items-center gap-2 text-xs font-medium text-gray-700 cursor-pointer">
                                <input type="checkbox" id="field_user" name="fields[]" value="registered_user" checked class="export-field-checkbox rounded text-emerald-600 focus:ring-emerald-500">
                                <span>Compte inscrit</span>
                            </label>
                            <label for="field_date" class="flex items-center gap-2 text-xs font-medium text-gray-700 cursor-pointer">
                                <input type="checkbox" id="field_date" name="fields[]" value="analyzed_at" checked class="export-field-checkbox rounded text-emerald-600 focus:ring-emerald-500">
                                <span>Date d'analyse</span>
                            </label>
                        </div>
                    </div>

                    <!-- Filtres optionnels -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="exp_start_date" class="block text-xs font-medium text-gray-700 mb-1">Période du</label>
                            <input type="date" id="exp_start_date" name="start_date" class="w-full text-xs px-3 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label for="exp_end_date" class="block text-xs font-medium text-gray-700 mb-1">Au</label>
                            <input type="date" id="exp_end_date" name="end_date" class="w-full text-xs px-3 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label for="exp_min_score" class="block text-xs font-medium text-gray-700 mb-1">Score ATS minimum</label>
                            <select id="exp_min_score" name="min_score" class="w-full text-xs px-3 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500">
                                <option value="">Tous les scores</option>
                                <option value="50">≥ 50 / 100</option>
                                <option value="70">≥ 70 / 100</option>
                                <option value="80">≥ 80 / 100</option>
                            </select>
                        </div>
                        <div>
                            <label for="exp_keyword" class="block text-xs font-medium text-gray-700 mb-1">Mot-clé / Compétence</label>
                            <input type="text" id="exp_keyword" name="keyword" placeholder="Ex: React, Comptabilité..." class="w-full text-xs px-3 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500">
                        </div>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-gray-100 flex items-center justify-end gap-3 shrink-0">
                    <button type="button" @click="showExportModal = false" class="px-4 py-2 border border-gray-300 rounded-lg text-xs font-semibold text-gray-700 hover:bg-gray-50 transition cursor-pointer">
                        Annuler
                    </button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold shadow-sm transition flex items-center gap-2 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Générer & Télécharger le fichier
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
