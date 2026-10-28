@extends('layouts.admin')

@section('header_content')
<div class="flex items-center space-x-4">
    <a href="{{ route('admin.partners.index') }}" class="text-gray-500 hover:text-gray-700 mr-2 transition-colors">
        <i class="fas fa-arrow-left"></i>
    </a>
    <div>
        <h2 class="text-xl font-bold text-gray-800">
            {{ isset($partner) ? 'Modifier le Partenaire' : 'Nouveau Partenaire' }}
        </h2>
        <p class="text-xs text-gray-500 mt-0.5">
            {{ isset($partner) ? "Mettre à jour les informations du partenaire « {$partner->name} »" : 'Ajouter un partenaire externe pour enrichir la page À propos.' }}
        </p>
    </div>
</div>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <form action="{{ isset($partner) ? route('admin.partners.update', $partner) : route('admin.partners.store') }}"
              method="POST"
              enctype="multipart/form-data"
              class="p-6 sm:p-8 space-y-6">
            @csrf
            @if(isset($partner))
                @method('PUT')
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- Left Column: Informations Générales -->
                <div class="space-y-5">
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider pb-2 border-b border-gray-100 flex items-center">
                        <i class="fas fa-info-circle text-indigo-600 mr-2"></i> Informations générales
                    </h3>

                    <!-- Nom -->
                    <div>
                        <label for="name" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                            Nom du Partenaire <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" id="name"
                            value="{{ old('name', $partner->name ?? '') }}"
                            required
                            placeholder="ex: Google for Startups, Université XYZ..."
                            class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('name') border-red-500 @enderror">
                        @error('name')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Site Web -->
                    <div>
                        <label for="website" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                            Site Web (URL)
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <i class="fas fa-globe text-xs"></i>
                            </div>
                            <input type="url" name="website" id="website"
                                value="{{ old('website', $partner->website ?? '') }}"
                                placeholder="https://example.com"
                                class="w-full pl-9 pr-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('website') border-red-500 @enderror">
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Si renseigné, le logo sur la page À propos sera cliquable vers ce lien.</p>
                        @error('website')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Type de Partenaire -->
                    <div>
                        <label for="type" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                            Type / Catégorie
                        </label>
                        <select name="type" id="type"
                            class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="external" {{ old('type', $partner->type ?? 'external') === 'external' ? 'selected' : '' }}>Partenaire Externe</option>
                            <option value="institutionnel" {{ old('type', $partner->type ?? '') === 'institutionnel' ? 'selected' : '' }}>Partenaire Institutionnel</option>
                            <option value="academique" {{ old('type', $partner->type ?? '') === 'academique' ? 'selected' : '' }}>Université / École</option>
                            <option value="sponsor" {{ old('type', $partner->type ?? '') === 'sponsor' ? 'selected' : '' }}>Sponsor / Mécène</option>
                            <option value="recruteur" {{ old('type', $partner->type ?? '') === 'recruteur' ? 'selected' : '' }}>Partenaire Recrutement & Insertion</option>
                        </select>
                        @error('type')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Ordre d'affichage -->
                    <div>
                        <label for="display_order" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                            Ordre d'affichage (priorité)
                        </label>
                        <input type="number" name="display_order" id="display_order" min="0"
                            value="{{ old('display_order', $partner->display_order ?? 0) }}"
                            class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <p class="text-xs text-gray-400 mt-1">Un chiffre plus petit apparaît en premier (0, 1, 2...).</p>
                        @error('display_order')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Right Column: Logo & Statut -->
                <div class="space-y-5">
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider pb-2 border-b border-gray-100 flex items-center">
                        <i class="fas fa-image text-indigo-600 mr-2"></i> Logo & Visibilité
                    </h3>

                    <!-- Logo Upload -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">
                            Logo du Partenaire {{ !isset($partner) ? '*' : '' }}
                        </label>

                        <div class="flex items-center space-x-4">
                            <!-- Preview Box -->
                            <div class="h-24 w-36 bg-gray-50 border-2 border-dashed border-gray-200 rounded-xl flex items-center justify-center p-2 overflow-hidden shadow-xs">
                                <img id="logo-preview"
                                     src="{{ isset($partner) && $partner->logo_url ? $partner->logo_url : '' }}"
                                     alt="Aperçu"
                                     class="max-h-full max-w-full object-contain {{ isset($partner) && $partner->logo_url ? '' : 'hidden' }}">
                                <div id="logo-placeholder" class="text-center text-gray-400 {{ isset($partner) && $partner->logo_url ? 'hidden' : '' }}">
                                    <i class="fas fa-cloud-arrow-up text-2xl mb-1"></i>
                                    <span class="block text-[10px]">Aperçu logo</span>
                                </div>
                            </div>

                            <!-- File Input -->
                            <div class="flex-1">
                                <input type="file" name="logo" id="logo"
                                    accept="image/*"
                                    onchange="previewImage(this)"
                                    class="block w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                                <p class="text-[11px] text-gray-400 mt-1.5">PNG, JPG, SVG, WebP jusqu'à 2 Mo. Format paysage recommandé sur fond blanc ou transparent.</p>
                            </div>
                        </div>
                        @error('logo')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description / Notes -->
                    <div>
                        <label for="description" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                            Description / Notes internes
                        </label>
                        <textarea name="description" id="description" rows="3"
                            placeholder="ex: Contrat de partenariat signé le 15 mars pour le closing d'étudiants..."
                            class="w-full px-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('description', $partner->description ?? '') }}</textarea>
                        @error('description')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Visibility Toggle -->
                    <div class="pt-2">
                        <label class="relative flex items-start cursor-pointer">
                            <div class="flex items-center h-5">
                                <input type="checkbox" name="is_active" value="1"
                                    {{ old('is_active', $partner->is_active ?? true) ? 'checked' : '' }}
                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            </div>
                            <div class="ml-3 text-sm">
                                <span class="font-medium text-gray-900">Actif et visible</span>
                                <p class="text-xs text-gray-500">Afficher immédiatement ce partenaire dans la section « Nos partenaires » sur la page À propos.</p>
                            </div>
                        </label>
                    </div>

                </div>
            </div>

            <!-- Action Buttons -->
            <div class="pt-6 border-t border-gray-100 flex items-center justify-end space-x-3">
                <a href="{{ route('admin.partners.index') }}"
                   class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                    Annuler
                </a>
                <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold py-2 px-6 rounded-lg shadow-sm transition-colors flex items-center">
                    <i class="fas fa-save mr-2"></i>
                    {{ isset($partner) ? 'Enregistrer les modifications' : 'Créer le Partenaire' }}
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function previewImage(input) {
        const preview = document.getElementById('logo-preview');
        const placeholder = document.getElementById('logo-placeholder');

        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.classList.remove('hidden');
                if (placeholder) placeholder.classList.add('hidden');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection
