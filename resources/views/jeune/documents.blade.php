@extends('layouts.jeune')

@section('title', 'Mes documents')

@section('content')
    <div class="space-y-8" x-data="documentsApp()">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Mes documents</h1>
                <p class="text-gray-500">Centralise et organise tes documents academiques</p>
            </div>
            <button @click="showUploadModal = true"
                class="px-5 py-3 bg-primary-600 text-white font-semibold rounded-xl hover:bg-primary-700 transition flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Ajouter un document
            </button>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl p-5 shadow-sm">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mb-3">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <p class="text-2xl font-bold text-gray-900">{{ $documents->count() }}</p>
                <p class="text-sm text-gray-500">Total documents</p>
            </div>
            <div class="bg-white rounded-2xl p-5 shadow-sm">
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mb-3">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <p class="text-2xl font-bold text-gray-900">{{ $documents->where('is_verified', true)->count() }}</p>
                <p class="text-sm text-gray-500">Verifies</p>
            </div>
            <div class="bg-white rounded-2xl p-5 shadow-sm">
                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mb-3">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                <p class="text-2xl font-bold text-gray-900">{{ $documents->where('document_type', 'bulletin')->count() }}
                </p>
                <p class="text-sm text-gray-500">Bulletins</p>
            </div>
            <div class="bg-white rounded-2xl p-5 shadow-sm">
                <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center mb-3">
                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <p class="text-2xl font-bold text-gray-900">
                    {{ number_format($documents->sum('file_size') / 1024 / 1024, 1) }} Mo
                </p>
                <p class="text-sm text-gray-500">Stockage utilise</p>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="flex flex-wrap gap-2">
            <button @click="filter = 'all'"
                :class="filter === 'all' ? 'bg-primary-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'"
                class="px-4 py-2 rounded-full text-sm font-medium transition">
                Tous
            </button>
            <button @click="filter = 'bulletin'"
                :class="filter === 'bulletin' ? 'bg-primary-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'"
                class="px-4 py-2 rounded-full text-sm font-medium transition">
                Bulletins
            </button>
            <button @click="filter = 'diplome'"
                :class="filter === 'diplome' ? 'bg-primary-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'"
                class="px-4 py-2 rounded-full text-sm font-medium transition">
                Diplomes
            </button>
            <button @click="filter = 'attestation'"
                :class="filter === 'attestation' ? 'bg-primary-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'"
                class="px-4 py-2 rounded-full text-sm font-medium transition">
                Attestations
            </button>
            <button @click="filter = 'autre'"
                :class="filter === 'autre' ? 'bg-primary-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'"
                class="px-4 py-2 rounded-full text-sm font-medium transition">
                Autres
            </button>
        </div>

        <!-- Documents Grid -->
        @if($documents->count() > 0)
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($documents as $document)
                    <div class="bg-white rounded-2xl p-5 shadow-sm hover:shadow-md transition group"
                        x-show="filter === 'all' || filter === '{{ $document->document_type }}'">
                        <div class="flex items-start gap-4">
                            <div
                                class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0
                                                                    {{ $document->document_type === 'bulletin' ? 'bg-purple-100' : '' }}
                                                                    {{ $document->document_type === 'diplome' ? 'bg-yellow-100' : '' }}
                                                                    {{ $document->document_type === 'attestation' ? 'bg-blue-100' : '' }}
                                                                    {{ $document->document_type === 'autre' ? 'bg-gray-100' : '' }}">
                                @if($document->mime_type === 'application/pdf')
                                    <svg class="w-6 h-6 text-red-500" fill="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M13,9V3.5L18.5,9H13M10.3,14.4L9.6,16.9H8.1L9.9,11H11.8L13.6,16.9H12L11.4,14.4H10.3M10.5,13.5H11.2L10.9,12.2L10.5,13.5Z" />
                                    </svg>
                                @else
                                    <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-gray-900 truncate">{{ $document->file_name }}</p>
                                <p class="text-sm text-gray-500">{{ ucfirst($document->document_type) }}</p>
                                <div class="flex items-center gap-2 mt-2">
                                    <span class="text-xs text-gray-400">{{ number_format($document->file_size / 1024, 1) }}
                                        Ko</span>
                                    @if($document->is_verified)
                                        <span
                                            class="px-2 py-0.5 bg-green-100 text-green-700 text-xs rounded-full flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 13l4 4L19 7" />
                                            </svg>
                                            Verifie
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 mt-4 pt-4 border-t opacity-0 group-hover:opacity-100 transition">
                            <button
                                @click="previewDocument({{ $document->id }}, '{{ $document->mime_type }}', '{{ $document->file_name }}')"
                                class="flex-1 py-2 text-center text-sm font-medium text-blue-600 hover:bg-blue-50 rounded-lg transition">
                                Previsualiser
                            </button>
                            <a href="{{ route('jeune.documents.download', $document) }}"
                                class="flex-1 py-2 text-center text-sm font-medium text-primary-600 hover:bg-primary-50 rounded-lg transition">
                                Telecharger
                            </a>
                            <button @click="deleteDocument({{ $document->id }})"
                                class="flex-1 py-2 text-center text-sm font-medium text-red-600 hover:bg-red-50 rounded-lg transition">
                                Supprimer
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <!-- Empty State -->
            <div class="bg-white rounded-2xl p-12 text-center">
                <div class="w-20 h-20 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Aucun document</h3>
                <p class="text-gray-500 mb-6">Commence par ajouter tes bulletins, diplomes et autres documents academiques.</p>
                <button @click="showUploadModal = true"
                    class="px-6 py-3 bg-primary-600 text-white font-semibold rounded-xl hover:bg-primary-700 transition">
                    Ajouter mon premier document
                </button>
            </div>
        @endif

        <!-- Upload Modal -->
        <div x-show="showUploadModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
            x-transition>
            <div class="bg-white rounded-3xl max-w-lg w-full p-6" @click.outside="showUploadModal = false">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold text-gray-900">Ajouter un document</h3>
                    <button @click="showUploadModal = false" class="p-2 hover:bg-gray-100 rounded-full">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form action="{{ route('jeune.documents.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="space-y-4">
                        <!-- File Upload -->
                        <div class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-primary-500 transition cursor-pointer"
                            @click="$refs.fileInput.click()" @dragover.prevent="dragover = true"
                            @dragleave.prevent="dragover = false" @drop.prevent="handleDrop($event)"
                            :class="dragover ? 'border-primary-500 bg-primary-50' : ''">
                            <input type="file" name="document" x-ref="fileInput" class="hidden"
                                accept=".pdf,.jpg,.jpeg,.png" @change="handleFileSelect($event)">
                            <div class="w-14 h-14 bg-gray-100 rounded-xl flex items-center justify-center mx-auto mb-4">
                                <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                            </div>
                            <p class="font-medium text-gray-900" x-text="fileName || 'Clique ou glisse un fichier ici'"></p>
                            <p class="text-sm text-gray-500 mt-1">PDF, JPG ou PNG (max 10 Mo)</p>
                        </div>

                        <!-- Document Type -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Type de document</label>
                            <select name="document_type"
                                class="w-full px-4 py-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="bulletin">Bulletin scolaire</option>
                                <option value="diplome">Diplome</option>
                                <option value="attestation">Attestation</option>
                                <option value="autre">Autre</option>
                            </select>
                        </div>

                        <!-- School Year (optional) -->
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Année de début</label>
                                <input type="number" name="start_year" x-model="startYear" @input="calculateEndYear()"
                                    placeholder="Ex: 2023" min="1900" :max="new Date().getFullYear()"
                                    class="w-full px-4 py-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Année de fin</label>
                                <input type="number" name="end_year" x-model="endYear" readonly placeholder="Auto"
                                    class="w-full px-4 py-3 border rounded-xl bg-gray-50 cursor-not-allowed focus:outline-none">
                            </div>
                        </div>
                        <!-- Hidden field for combined school_year -->
                        <input type="hidden" name="school_year"
                            :value="startYear && endYear ? startYear + '-' + endYear : ''">

                        <!-- Description (optional) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Description (optionnel)</label>
                            <textarea name="description" rows="2" placeholder="Ajoute une note..."
                                class="w-full px-4 py-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 resize-none"></textarea>
                        </div>
                    </div>

                    <div class="flex gap-3 mt-6">
                        <button type="button" @click="showUploadModal = false"
                            class="flex-1 py-3 border rounded-xl font-medium text-gray-700 hover:bg-gray-50 transition">
                            Annuler
                        </button>
                        <button type="submit"
                            class="flex-1 py-3 bg-primary-600 text-white font-semibold rounded-xl hover:bg-primary-700 transition">
                            Ajouter
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <!-- Preview Modal -->
        <div x-show="showPreviewModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80"
            x-transition>
            <div class="bg-white rounded-3xl max-w-4xl w-full max-h-[90vh] overflow-hidden flex flex-col">
                <div class="flex items-center justify-between p-6 border-b">
                    <h3 class="text-xl font-bold text-gray-900" x-text="previewFileName"></h3>
                    <button @click="showPreviewModal = false" class="p-2 hover:bg-gray-100 rounded-full">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="flex-1 overflow-auto p-6 bg-gray-50">
                    <!-- Image Preview -->
                    <template x-if="previewType && previewType.startsWith('image/')">
                        <img :src="previewUrl" :alt="previewFileName"
                            class="max-w-full h-auto mx-auto rounded-lg shadow-lg">
                    </template>

                    <!-- PDF Preview -->
                    <template x-if="previewType === 'application/pdf'">
                        <iframe :src="previewUrl" class="w-full h-[70vh] rounded-lg shadow-lg"></iframe>
                    </template>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div x-show="showDeleteModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
            x-transition>
            <div class="bg-white rounded-3xl max-w-md w-full p-6" @click.outside="showDeleteModal = false">
                <div class="flex items-center justify-center w-14 h-14 bg-red-100 rounded-full mx-auto mb-4">
                    <svg class="w-7 h-7 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 text-center mb-2">Supprimer ce document ?</h3>
                <p class="text-gray-500 text-center mb-6">Cette action est irréversible. Le document sera définitivement
                    supprimé.</p>

                <div class="flex gap-3">
                    <button @click="showDeleteModal = false"
                        class="flex-1 py-3 border border-gray-300 rounded-xl font-medium text-gray-700 hover:bg-gray-50 transition">
                        Annuler
                    </button>
                    <button @click="confirmDelete()"
                        class="flex-1 py-3 bg-red-600 text-white font-semibold rounded-xl hover:bg-red-700 transition">
                        Supprimer
                    </button>
                </div>
            </div>
        </div>
    </div>
    @push('scripts')
        <script>
            function documentsApp() {
                return {
                    filter: 'all',
                    showUploadModal: false,
                    showPreviewModal: false,
                    showDeleteModal: false,
                    documentToDelete: null,
                    fileName: '', ',
                            dragover: false,
                    previewUrl: '',
                    previewType: '',
                    previewFileName: '',
                    startYear: '',
                    endYear: '',

                    calculateEndYear() {
                        if (this.startYear && this.startYear.length === 4) {
                            this.endYear = parseInt(this.startYear) + 1;
                        } else {
                            this.endYear = '';
                        }
                    },

                    handleFileSelect(event) {
                        const file = event.target.files[0];
                        if (file) {
                            this.fileName = file.name;
                        }
                    },

                    handleDrop(event) {
                        this.dragover = false;
                        const file = event.dataTransfer.files[0];
                        if (file) {
                            this.fileName = file.name;
                            this.$refs.fileInput.files = event.dataTransfer.files;
                        }
                    },

                    previewDocument(id, mimeType, fileName) {
                        this.previewUrl = `/espace-jeune/documents/${id}/view`;
                        this.previewType = mimeType;
                        this.previewFileName = fileName;
                        this.showPreviewModal = true;
                    },

                    deleteDocument(id) {
                        this.documentToDelete = id;
                        this.showDeleteModal = true;
                    },

                    async confirmDelete() {
                        if (!this.documentToDelete) return;

                        try {
                            const response = await fetch(`/espace-jeune/documents/${this.documentToDelete}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                }
                            });

                            if (response.ok) {
                                window.location.reload();
                            }
                        } catch (error) {
                            console.error('Error:', error);
                        }
                    }
                }
            }
        </script>
    @endpush
@endsection