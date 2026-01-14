@extends('layouts.admin')

@section('title', 'Créer un Domaine d\'Expertise')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-3xl">
    <div class="mb-6">
        <a href="{{ route('admin.specializations.index') }}" class="text-orange-600 hover:text-orange-800">
            ← Retour à la liste
        </a>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Créer un Domaine d'Expertise</h1>

        <form action="{{ route('admin.specializations.store') }}" method="POST">
            @csrf

            <!-- Nom -->
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                    Nom du domaine <span class="text-red-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="name" 
                    name="name" 
                    value="{{ old('name') }}"
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
                >{{ old('description') }}</textarea>
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
                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Actif</option>
                    <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                    <option value="archived" {{ old('status') == 'archived' ? 'selected' : '' }}>Archivé</option>
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
                                {{ in_array($code, old('mbti_types', [])) ? 'checked' : '' }}
                                class="rounded border-gray-300 text-orange-600 focus:ring-orange-500"
                            >
                            <span class="text-sm">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                <p class="text-sm text-gray-500 mt-2">
                    Sélectionnez les secteurs MBTI compatibles avec ce domaine
                </p>
            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.specializations.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    Annuler
                </a>
                <button type="submit" class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700">
                    Créer le domaine
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
