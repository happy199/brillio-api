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
            <div x-data="{
                fileName: '',
                imageUrl: '{{ asset('storage/' . $advertisement->image_path) }}',
                hasNewImage: false,
                handleFileChange(event) {
                    const file = event.target.files[0];
                    if (file) {
                        this.fileName = file.name;
                        this.imageUrl = URL.createObjectURL(file);
                        this.hasNewImage = true;
                    } else {
                        this.resetImage();
                    }
                },
                resetImage() {
                    this.fileName = '';
                    this.imageUrl = '{{ asset('storage/' . $advertisement->image_path) }}';
                    this.hasNewImage = false;
                    document.getElementById('image').value = '';
                }
            }">
                <label for="image" class="block text-sm font-medium text-gray-700 mb-2">
                    Fichier visuel publicitaire (Image) <span class="text-gray-400 font-normal">(laisser vide pour conserver l'image actuelle)</span>
                </label>
                
                <div class="mt-1 flex flex-col items-center justify-center p-6 border-2 border-gray-300 border-dashed rounded-md hover:border-organization-400 transition-colors">
                    
                    <!-- Preview and actions -->
                    <div class="space-y-4 w-full flex flex-col items-center justify-center">
                        <div class="relative max-w-xs md:max-w-md rounded-lg overflow-hidden border border-gray-200 shadow-sm bg-gray-50">
                            <img :src="imageUrl" class="max-h-64 object-contain mx-auto" alt="Aperçu du visuel">
                            
                            <!-- Cancel new selection button -->
                            <button type="button" x-show="hasNewImage" @click="resetImage()" class="absolute top-2 right-2 bg-red-600 hover:bg-red-700 text-white rounded-full p-1.5 shadow focus:outline-none transition-colors" title="Annuler le changement">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Status badge / filename indicator -->
                        <div class="flex flex-col items-center gap-1.5">
                            <template x-if="!hasNewImage">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-800 border border-gray-200">
                                    Image actuelle
                                </span>
                            </template>
                            <template x-if="hasNewImage">
                                <div class="text-sm font-semibold text-organization-600 flex items-center justify-center gap-1.5 bg-organization-50 px-3 py-1 rounded-full border border-organization-150">
                                    <i class="fas fa-file-image"></i>
                                    <span x-text="fileName" class="truncate max-w-xs"></span>
                                </div>
                            </template>
                        </div>

                        <label for="image" class="cursor-pointer bg-white border border-gray-300 text-gray-700 px-3 py-1.5 rounded-md text-xs font-semibold hover:bg-gray-50 transition shadow-sm">
                            <span x-text="hasNewImage ? 'Changer d\'image' : 'Remplacer l\'image'">Remplacer l'image</span>
                            <input id="image" name="image" type="file" class="sr-only" accept="image/*" @change="handleFileChange($event)">
                        </label>
                    </div>
                </div>
                @error('image')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

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
