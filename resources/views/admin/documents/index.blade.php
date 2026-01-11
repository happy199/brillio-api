@extends('layouts.admin')

@section('title', 'Documents')
@section('header', 'Gestion des documents')

@section('content')
<div class="space-y-6">
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
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Document</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Utilisateur</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Taille</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($documents as $document)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                                    @php
                                        $extension = pathinfo($document->file_name, PATHINFO_EXTENSION);
                                        $iconColor = match($extension) {
                                            'pdf' => 'text-red-500',
                                            'doc', 'docx' => 'text-blue-500',
                                            'jpg', 'jpeg', 'png' => 'text-green-500',
                                            default => 'text-gray-500'
                                        };
                                    @endphp
                                    <svg class="w-6 h-6 {{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900 truncate max-w-xs" title="{{ $document->file_name }}">
                                        {{ Str::limit($document->file_name, 40) }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ strtoupper($extension) }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($document->user)
                                <a href="{{ route('admin.users.show', $document->user) }}" class="text-indigo-600 hover:text-indigo-900">
                                    <div class="text-sm font-medium">{{ $document->user->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $document->user->email }}</div>
                                </a>
                            @else
                                <span class="text-gray-400">Utilisateur supprimé</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $typeColors = [
                                    'bulletin' => 'bg-blue-100 text-blue-700',
                                    'releve_notes' => 'bg-green-100 text-green-700',
                                    'diplome' => 'bg-purple-100 text-purple-700',
                                    'certificat' => 'bg-yellow-100 text-yellow-700',
                                    'attestation' => 'bg-orange-100 text-orange-700',
                                    'autre' => 'bg-gray-100 text-gray-700',
                                ];
                            @endphp
                            <span class="px-2 py-1 text-xs rounded-full {{ $typeColors[$document->document_type] ?? 'bg-gray-100 text-gray-700' }}">
                                {{ $documentTypes[$document->document_type] ?? $document->document_type }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ number_format($document->file_size / 1024, 1) }} Ko
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $document->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                            <a href="{{ route('admin.documents.download', $document) }}"
                               class="text-indigo-600 hover:text-indigo-900 mr-3">
                                Télécharger
                            </a>
                            <form action="{{ route('admin.documents.destroy', $document) }}"
                                  method="POST"
                                  class="inline"
                                  onsubmit="return confirm('Supprimer ce document ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900">
                                    Supprimer
                                </button>
                            </form>
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
</div>
@endsection
