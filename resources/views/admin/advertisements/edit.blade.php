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
                <label for="image" class="block text-sm font-semibold text-gray-700 mb-2">
                    Visuel publicitaire (Image) <span class="text-gray-400 font-normal">(laisser vide pour conserver l'image actuelle)</span>
                </label>
                
                <div class="mt-1 flex flex-col items-center justify-center p-6 border-2 border-gray-300 border-dashed rounded-md hover:border-indigo-400 transition-colors">
                    
                    <!-- Preview and Action container -->
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
                                <div class="text-sm font-semibold text-indigo-600 flex items-center justify-center gap-1.5 bg-indigo-50 px-3 py-1 rounded-full border border-indigo-150">
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
