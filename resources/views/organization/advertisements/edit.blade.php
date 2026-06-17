@extends('layouts.organization')

@section('title', 'Modifier la Publicité')

@section('content')
<div class="max-w-3xl mx-auto">
    <!-- Header -->
    <div class="md:flex md:items-center md:justify-between mb-8">
        <div class="flex-1 min-w-0">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                📢 Modifier la publicité
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Modifiez les détails ou le visuel de votre publicité. Les changements seront visibles instantanément.
            </p>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white shadow sm:rounded-lg border border-gray-200">
        <form method="POST" action="{{ route('organization.advertisements.update', $advertisement) }}" enctype="multipart/form-data" class="space-y-6 p-6">
            @csrf
            @method('PUT')

            <!-- Banner Info Box -->
            <div class="bg-organization-50 border border-organization-200 rounded-md p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-organization-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h4 class="text-sm font-semibold text-organization-800">Mise à jour instantanée</h4>
                        <p class="text-xs text-organization-700 mt-1">
                            Les modifications apportées à votre publicité (titre, lien, visuel) sont appliquées immédiatement sur la page publique sans repasser par le processus de validation admin.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Title -->
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700">
                    Titre / Nom de l'annonce <span class="text-gray-400 font-normal">(optionnel)</span>
                </label>
                <div class="mt-1">
                    <input type="text" name="title" id="title" value="{{ old('title', $advertisement->title) }}"
                           class="shadow-sm focus:ring-organization-500 focus:border-organization-500 block w-full sm:text-sm border-gray-300 rounded-md px-4 py-3"
                           placeholder="Ex: Forum Étudiant Brillio 2026">
                </div>
                @error('title')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Target Link URL -->
            <div>
                <label for="link_url" class="block text-sm font-medium text-gray-700">
                    Lien cible / Redirection <span class="text-gray-400 font-normal">(optionnel)</span>
                </label>
                <div class="mt-1">
                    <input type="url" name="link_url" id="link_url" value="{{ old('link_url', $advertisement->link_url) }}"
                           class="shadow-sm focus:ring-organization-500 focus:border-organization-500 block w-full sm:text-sm border-gray-300 rounded-md px-4 py-3"
                           placeholder="https://votre-site.com/evenement">
                </div>
                <p class="mt-2 text-xs text-gray-500">
                    Saisissez l'adresse URL complète (commençant par http:// ou https://) vers laquelle rediriger l'utilisateur lorsqu'il clique sur votre visuel.
                </p>
                @error('link_url')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Visual File Input -->
            <x-advertisement-image-upload theme="organization" :existing-image="$advertisement->image_path" />

            <!-- Actions -->
            <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                <a href="{{ route('organization.advertisements.index') }}"
                   class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none transition-colors">
                    Annuler
                </a>
                <button type="submit"
                        class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-organization-600 hover:bg-organization-700 focus:outline-none transition-colors">
                    <i class="fas fa-save mr-2"></i> Enregistrer les modifications
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
