@extends('layouts.admin')

@section('title', 'Modifier la Publicité')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Modifier la Publicité</h1>
            <p class="text-gray-600">Modifiez le visuel, le titre ou le lien de redirection de la publicité.</p>
        </div>
    </div>

    <!-- Card Form -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <form method="POST" action="{{ route('admin.advertisements.update', $advertisement) }}" enctype="multipart/form-data" class="space-y-6 p-6">
            @csrf
            @method('PUT')

            <!-- Info Box -->
            <div class="bg-indigo-50 border border-indigo-150 rounded-lg p-4 flex items-start space-x-3">
                <svg class="w-5 h-5 text-indigo-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="text-xs text-indigo-850">
                    <h4 class="font-bold">Mise à jour directe</h4>
                    <p class="mt-0.5">Les modifications apportées par l'administration sont enregistrées et publiées immédiatement. Si vous téléversez une nouvelle image, l'ancienne sera supprimée et remplacée par la nouvelle au format WebP.</p>
                </div>
            </div>

            <!-- Title -->
            <div>
                <label for="title" class="block text-sm font-semibold text-gray-700">
                    Titre de la publicité <span class="text-gray-400 font-normal">(optionnel)</span>
                </label>
                <input type="text" name="title" id="title" value="{{ old('title', $advertisement->title) }}"
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
                <input type="url" name="link_url" id="link_url" value="{{ old('link_url', $advertisement->link_url) }}"
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
            <x-advertisement-image-upload theme="admin" :existing-image="$advertisement->image_path" />

            <!-- Form Actions -->
            <div class="flex justify-end space-x-3 pt-6 border-t border-gray-150">
                <a href="{{ route('admin.advertisements.index') }}"
                   class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 text-sm font-medium transition-colors">
                    Annuler
                </a>
                <button type="submit"
                        class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 text-sm font-semibold transition-colors shadow-sm">
                    Enregistrer les modifications
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
