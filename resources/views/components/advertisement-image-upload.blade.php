@props([
    'existingImage' => null,
    'theme' => 'admin',
    'required' => false
])

@php
    $themeColor = $theme === 'organization' ? 'organization' : 'indigo';
@endphp

<div x-data="{
    fileName: '',
    imageUrl: '{{ $existingImage ? asset('storage/' . $existingImage) : '' }}',
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
        this.imageUrl = '{{ $existingImage ? asset('storage/' . $existingImage) : '' }}';
        this.hasNewImage = false;
        document.getElementById('image').value = '';
    }
}">
    <label for="image" class="block text-sm font-semibold text-gray-700 mb-2">
        @if($existingImage)
            Visuel publicitaire (Image) <span class="text-gray-400 font-normal">(laisser vide pour conserver l'image actuelle)</span>
        @else
            Fichier visuel publicitaire (Image) <span class="text-red-500">*</span>
        @endif
    </label>

    <div class="mt-1 flex flex-col items-center justify-center p-6 border-2 border-gray-300 border-dashed rounded-md hover:border-{{ $themeColor }}-400 transition-colors">

        <!-- Default State (Create mode, no file selected yet) -->
        @if(!$existingImage)
            <div class="space-y-1 text-center" x-show="!imageUrl">
                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <div class="flex text-sm text-gray-600 justify-center">
                    <label for="image" class="relative cursor-pointer bg-white rounded-md font-medium text-{{ $themeColor }}-600 hover:text-{{ $themeColor }}-500 focus-within:outline-none">
                        <span>Sélectionner un fichier</span>
                    </label>
                </div>
                <p class="text-xs text-gray-500">
                    Format JPG, PNG, WEBP, GIF jusqu'à 5 Mo
                </p>
            </div>
        @endif

        <!-- Preview State (Image is loaded/selected) -->
        <div x-show="imageUrl" class="space-y-4 w-full flex flex-col items-center justify-center" @if(!$existingImage) x-cloak @endif>
            <div class="relative max-w-xs md:max-w-md rounded-lg overflow-hidden border border-gray-200 shadow-sm bg-gray-50">
                <img :src="imageUrl" class="max-h-64 object-contain mx-auto" alt="Aperçu du visuel">

                <!-- Cancel / Delete button -->
                <button type="button" x-show="hasNewImage || (imageUrl && !'{{ $existingImage }}')" @click="resetImage()" class="absolute top-2 right-2 bg-red-600 hover:bg-red-700 text-white rounded-full p-1.5 shadow focus:outline-none transition-colors" title="Annuler le changement">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Status badge / filename indicator -->
            <div class="flex flex-col items-center gap-1.5">
                @if($existingImage)
                    <template x-if="!hasNewImage">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-800 border border-gray-200">
                            Image actuelle
                        </span>
                    </template>
                @endif
                <template x-if="hasNewImage">
                    <div class="text-sm font-semibold text-{{ $themeColor }}-600 flex items-center justify-center gap-1.5 bg-{{ $themeColor }}-50 px-3 py-1 rounded-full border border-{{ $themeColor }}-150">
                        <i class="fas fa-file-image"></i>
                        <span x-text="fileName" class="truncate max-w-xs"></span>
                    </div>
                </template>
            </div>

            <label for="image" class="cursor-pointer bg-white border border-gray-300 text-gray-700 px-3 py-1.5 rounded-md text-xs font-semibold hover:bg-gray-50 transition shadow-sm">
                <span x-text="hasNewImage || (imageUrl && !'{{ $existingImage }}') ? 'Changer d\'image' : ('{{ $existingImage }}' ? 'Remplacer l\'image' : 'Sélectionner un fichier')">Remplacer l'image</span>
                <input id="image" name="image" type="file" class="sr-only" accept="image/*" @change="handleFileChange($event)" {{ $required && !$existingImage ? 'required' : '' }}>
            </label>
        </div>
    </div>
    @error('image')
        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
