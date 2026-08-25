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
    openPreview(url, name, ext) {
        this.previewUrl = url;
        this.fileName = name;
        this.isImage = ['jpg','jpeg','png','gif','webp','svg'].includes(ext);
        this.isPdf = ext === 'pdf';
        this.showModal = true;
    }
}">
    <!-- Stats rapides -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
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
    </div>

    <!-- Filtres -->
    <div class="bg-white rounded-xl shadow-sm p-4">
        <form action="{{ route('admin.documents.index') }}" method="GET" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">Recherche</label>
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Nom du fichier..."
                       class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="w-48">
                <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                <select name="type" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <option value="">Tous les types</option>
                    @foreach($documentTypes as $key => $label)
                        <option value="{{ $key }}" {{ request('type') === $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                Filtrer
            </button>

            @if(request()->hasAny(['search', 'type']))
                <a href="{{ route('admin.documents.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Réinitialiser
                </a>
            @endif
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
                                <button type="button"
                                        @click="openPreview('{{ route('admin.documents.preview', $document) }}', '{{ addslashes($document->file_name) }}', '{{ strtolower($extension) }}')"
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
</div>
@endsection
