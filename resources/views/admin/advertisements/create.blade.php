@extends('layouts.admin')

@section('title', 'Créer une Publicité')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Créer une Publicité</h1>
            <p class="text-gray-600">Publiez directement un nouveau visuel publicitaire sur la page publique.</p>
        </div>
    </div>

    <!-- Card Form -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <form method="POST" action="{{ route('admin.advertisements.store') }}" enctype="multipart/form-data" class="space-y-6 p-6">
            @csrf

            <!-- Info Box -->
            <div class="bg-indigo-50 border border-indigo-150 rounded-lg p-4 flex items-start space-x-3">
                <svg class="w-5 h-5 text-indigo-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="text-xs text-indigo-850">
                    <h4 class="font-bold">Publication immédiate</h4>
                    <p class="mt-0.5">Les publicités créées par l'administration sont publiées immédiatement sur la galerie publique sans nécessiter de validation supplémentaire. L'image sera convertie en format WebP ultra optimisé.</p>
                </div>
            </div>

            <!-- Title -->
            <div>
                <label for="title" class="block text-sm font-semibold text-gray-700">
                    Titre de la publicité <span class="text-gray-400 font-normal">(optionnel)</span>
                </label>
                <input type="text" name="title" id="title" value="{{ old('title') }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-3 border"
                       placeholder="Ex: Formation Orientation 2026">
                @error('title')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Link URL -->
            <div>
                <label for="link_url" class="block text-sm font-semibold text-gray-700">
                    Lien cible / URL de redirection <span class="text-gray-400 font-normal">(optionnel)</span>
                </label>
                <input type="url" name="link_url" id="link_url" value="{{ old('link_url') }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-3 border"
                       placeholder="https://brillio.africa/evenement">
                <p class="mt-2 text-xs text-gray-500">
                    L'adresse web cible complète vers laquelle l'utilisateur sera redirigé s'il clique sur la publicité.
                </p>
                @error('link_url')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Image File Input -->
            <div x-data="{ fileName: '' }">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Visuel publicitaire (Image) <span class="text-red-500">*</span>
                </label>
                
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-indigo-400 transition-colors">
                    <div class="space-y-1 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <div class="flex text-sm text-gray-600 justify-center">
                            <label for="image" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none">
                                <span>Sélectionner un fichier</span>
                                <input id="image" name="image" type="file" class="sr-only" accept="image/*" @change="fileName = $event.target.files[0] ? $event.target.files[0].name : ''" required>
                            </label>
                        </div>
                        <p class="text-xs text-gray-500">
                            Format JPG, PNG, WEBP, GIF jusqu'à 5 Mo
                        </p>
                        
                        <!-- Chosen filename -->
                        <template x-if="fileName">
                            <p class="text-sm font-semibold text-indigo-600 mt-2">
                                <i class="fas fa-file-image mr-1"></i> Sélectionné : <span x-text="fileName"></span>
                            </p>
                        </template>
                    </div>
                </div>
                @error('image')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end space-x-3 pt-6 border-t border-gray-150">
                <a href="{{ route('admin.advertisements.index') }}"
                   class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 text-sm font-medium transition-colors">
                    Annuler
                </a>
                <button type="submit"
                        class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 text-sm font-semibold transition-colors shadow-sm">
                    Publier la publicité
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
