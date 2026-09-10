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
            const json = await res.json();
            if (json.success && json.analysis) {
                this.cvData = json.analysis;
            } else {
                this.cvError = json.message || 'Impossible de charger l\'analyse du CV.';
            }
        } catch (e) {
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
    <dialog x-show="showModal"
            x-cloak
            :open="showModal"
            class="fixed inset-0 z-50 overflow-y-auto w-full h-full max-w-full max-h-full p-0 border-0 bg-transparent"
            aria-labelledby="modal-title"
            aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="showModal = false"
                 class="fixed inset-0 transition-opacity bg-gray-900 bg-opacity-75"
                 aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="showModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block w-full max-w-5xl p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-2xl">

                <div class="flex items-center justify-between pb-4 border-b border-gray-200">
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
                                class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="mt-4 bg-gray-100 rounded-xl overflow-hidden min-h-[550px] flex items-center justify-center p-2">
                    <template x-if="isImage">
                        <img :src="previewUrl" :alt="fileName || 'Prévisualisation image'" class="max-h-[70vh] object-contain mx-auto rounded-lg shadow-sm">
                    </template>
                    <template x-if="!isImage">
                        <iframe :src="previewUrl" :title="fileName || 'Prévisualisation du document'" title="Prévisualisation du document" class="w-full h-[70vh] rounded-lg border-0 bg-white"></iframe>
                    </template>
                </div>
            </div>
        </div>
    </dialog>

    <!-- Modal Propositions & Analyse IA du CV -->
    <dialog x-show="showCvModal"
            x-cloak
            :open="showCvModal"
            class="fixed inset-0 z-50 overflow-y-auto w-full h-full max-w-full max-h-full p-0 border-0 bg-transparent"
            aria-labelledby="cv-modal-title"
            aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showCvModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="showCvModal = false"
                 class="fixed inset-0 transition-opacity bg-gray-900 bg-opacity-75"
                 aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="showCvModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block w-full max-w-4xl p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-2xl max-h-[90vh] flex flex-col">

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
                            <p class="text-xs text-gray-500" x-text="cvData ? (cvData.candidate_title + ' • Analysé le ' + cvData.created_at) : 'Chargement...'"></p>
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
                                        <span class="font-medium text-gray-800" x-text="cvData.contact?.location || 'Non spécifié'"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Scores par critères ATS -->
                            <div class="bg-white border rounded-xl p-4 shadow-2xs">
                                <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Scores par critères ATS</h4>
                                <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                                    <template x-for="(crit, i) in (cvData.criteria || [])" :key="i">
                                        <div class="bg-gray-50 p-2.5 rounded-lg text-center">
                                            <p class="text-[11px] text-gray-500 font-medium capitalize truncate" x-text="crit.name"></p>
                                            <p class="text-lg font-bold text-indigo-600 mt-0.5" x-text="crit.score + '/100'"></p>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Forces & Axes d'amélioration -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="p-4 bg-emerald-50/60 border border-emerald-100 rounded-xl">
                                    <h4 class="text-xs font-bold text-emerald-800 uppercase tracking-wider mb-2">Points forts</h4>
                                    <ul class="text-xs text-emerald-900 space-y-1.5 list-disc list-inside">
                                        <template x-for="(st, i) in (cvData.strengths || [])" :key="i">
                                            <li x-text="st"></li>
                                        </template>
                                    </ul>
                                </div>
                                <div class="p-4 bg-amber-50/60 border border-amber-100 rounded-xl">
                                    <h4 class="text-xs font-bold text-amber-800 uppercase tracking-wider mb-2">Axes d'amélioration</h4>
                                    <ul class="text-xs text-amber-900 space-y-1.5 list-disc list-inside">
                                        <template x-for="(wk, i) in (cvData.improvements || cvData.weaknesses || [])" :key="i">
                                            <li x-text="wk"></li>
                                        </template>
                                    </ul>
                                </div>
                            </div>

                            <!-- Synthèse / Profil rédigé par l'IA -->
                            <div class="p-4 bg-indigo-50/50 border border-indigo-100 rounded-xl">
                                <h4 class="text-xs font-bold text-indigo-900 uppercase tracking-wider mb-1.5">Profil optimisé par l'IA</h4>
                                <p class="text-xs sm:text-sm text-gray-700 leading-relaxed italic" x-text="cvData.profil || cvData.summary"></p>
                            </div>

                            <!-- Expériences & Projets restructurés -->
                            <div class="border rounded-xl p-4">
                                <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Expériences & Projets revalorisés</h4>
                                <div class="space-y-3">
                                    <template x-for="(exp, i) in (cvData.experiences || [])" :key="i">
                                        <div class="border-b last:border-0 pb-3 last:pb-0">
                                            <div class="flex items-center justify-between">
                                                <h5 class="text-xs sm:text-sm font-bold text-gray-900" x-text="exp.title"></h5>
                                                <span class="text-[11px] text-gray-400 font-medium" x-text="exp.period"></span>
                                            </div>
                                            <p class="text-xs text-indigo-700 font-semibold" x-text="exp.company"></p>
                                            <ul class="mt-1.5 text-xs text-gray-600 space-y-1 list-disc list-inside">
                                                <template x-for="(bullet, bIdx) in (exp.bullets || [])" :key="bIdx">
                                                    <li x-text="bullet"></li>
                                                </template>
                                            </ul>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Formations & Diplômes -->
                            <template x-if="cvData.formation && cvData.formation.length > 0">
                                <div class="border rounded-xl p-4">
                                    <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Formations & Diplômes</h4>
                                    <div class="space-y-2">
                                        <template x-for="(form, fIdx) in cvData.formation" :key="fIdx">
                                            <div class="flex items-center justify-between text-xs border-b last:border-0 pb-2 last:pb-0">
                                                <div>
                                                    <p class="font-bold text-gray-800" x-text="form.degree || form.diploma || form.title"></p>
                                                    <p class="text-gray-500" x-text="form.school || form.institution || form.etablissement"></p>
                                                </div>
                                                <span class="text-gray-400 font-medium" x-text="form.year || form.period || form.annee"></span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>

                            <!-- Compétences clés -->
                            <div class="p-4 bg-gray-50 rounded-xl border">
                                <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Compétences clés détectées</h4>
                                <div class="flex flex-wrap gap-1.5">
                                    <template x-for="(sk, i) in (cvData.competences || [])" :key="i">
                                        <span class="px-2 py-0.5 bg-white border border-gray-200 rounded-md text-xs font-medium text-gray-700" x-text="sk"></span>
                                    </template>
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
    </dialog>

    <!-- Modal Extraction Commerciale des CV -->
    <dialog x-show="showExportModal"
            x-cloak
            :open="showExportModal"
            class="fixed inset-0 z-50 overflow-y-auto w-full h-full max-w-full max-h-full p-0 border-0 bg-transparent"
            aria-labelledby="export-modal-title"
            aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showExportModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="showExportModal = false"
                 class="fixed inset-0 transition-opacity bg-gray-900 bg-opacity-75"
                 aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="showExportModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block w-full max-w-2xl p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-2xl">

                <form action="{{ route('admin.documents.export-candidates') }}" method="POST">
                    @csrf
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center">
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

                    <div class="mt-5 space-y-5">
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

                    <div class="mt-6 pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
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
    </dialog>
</div>
@endsection
