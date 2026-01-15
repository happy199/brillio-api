@extends('layouts.admin')

@section('title', 'Éditer un Domaine d\'Expertise')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-3xl">
    <div class="mb-6">
        <a href="{{ route('admin.specializations.index') }}" class="text-orange-600 hover:text-orange-800">
            ← Retour à la liste
        </a>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-start mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Éditer : {{ $specialization->name }}</h1>
                <p class="text-sm text-gray-500 mt-1">
                    {{ $specialization->mentor_profiles_count }} mentor(s) lié(s) à ce domaine
                </p>
            </div>
        </div>

        @if($specialization->mentor_profiles_count > 0)
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <p class="text-sm text-yellow-800">
                    ⚠️ <strong>Attention :</strong> {{ $specialization->mentor_profiles_count }} mentor(s) sont liés à ce domaine. 
                    La suppression archivera le domaine au lieu de le supprimer.
                </p>
            </div>
        @endif

        <form action="{{ route('admin.specializations.update', $specialization) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Nom -->
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                    Nom du domaine <span class="text-red-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="name" 
                    name="name" 
                    value="{{ old('name', $specialization->name) }}"
                    class="w-full border-gray-300 rounded-lg @error('name') border-red-500 @enderror"
                    required
                >
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description -->
            <div class="mb-4">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                    Description
                </label>
                <textarea 
                    id="description" 
                    name="description" 
                    rows="3"
                    class="w-full border-gray-300 rounded-lg @error('description') border-red-500 @enderror"
                    placeholder="Décrivez ce domaine d'expertise..."
                >{{ old('description', $specialization->description) }}</textarea>
                @error('description')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Statut -->
            <div class="mb-4">
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                    Statut <span class="text-red-500">*</span>
                </label>
                <select 
                    id="status" 
                    name="status" 
                    class="w-full border-gray-300 rounded-lg @error('status') border-red-500 @enderror"
                    required
                >
                    <option value="active" {{ old('status', $specialization->status) == 'active' ? 'selected' : '' }}>Actif</option>
                    <option value="pending" {{ old('status', $specialization->status) == 'pending' ? 'selected' : '' }}>En attente</option>
                    <option value="archived" {{ old('status', $specialization->status) == 'archived' ? 'selected' : '' }}>Archivé</option>
                </select>
                @error('status')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Types MBTI -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Types MBTI associés
                </label>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                    @foreach($mbtiSectors as $code => $label)
                        <label class="flex items-center space-x-2 p-2 border rounded hover:bg-gray-50 cursor-pointer">
                            <input 
                                type="checkbox" 
                                name="mbti_types[]" 
                                value="{{ $code }}"
                                {{ in_array($code, old('mbti_types', $selectedMbtiTypes)) ? 'checked' : '' }}
                                class="rounded border-gray-300 text-orange-600 focus:ring-orange-500"
                            >
                            <span class="text-sm">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-between">
                <div class="flex gap-3">
                    <a href="{{ route('admin.specializations.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Annuler
                    </a>
                    <button type="submit" class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700">
                        Enregistrer les modifications
                    </button>
                </div>
            </div>
        </form>

        <!-- Delete form separate -->
        <form action="{{ route('admin.specializations.destroy', $specialization) }}" method="POST" class="mt-4" onsubmit="return confirm('Êtes-vous sûr de vouloir {{ $specialization->mentor_profiles_count > 0 ? 'archiver' : 'supprimer' }} ce domaine ?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                {{ $specialization->mentor_profiles_count > 0 ? 'Archiver' : 'Supprimer' }}
            </button>
        </form>
    </div>
</div>
@endsection
