@extends('layouts.organization')

@section('title', 'Mon Profil')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Mon Profil Organisation</h1>
    </div>

    <div class="bg-white overflow-hidden shadow-sm rounded-lg max-w-4xl mx-auto">
        <div class="p-8 bg-white border-b border-gray-200">
            <form action="{{ route('organization.profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-12 gap-8">
                    <!-- Colonne Logo (3 cols) -->
                    <div class="md:col-span-4 flex flex-col items-center border-r border-gray-100 pr-4">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Logo</label>
                            <div class="relative h-32 w-32 mx-auto">
                                <img id="logo-preview" src="{{ $organization->logo_url }}" alt="Logo actuel"
                                    class="h-full w-full object-cover rounded-full border-4 border-gray-100 shadow-sm {{ $organization->logo_url ? '' : 'hidden' }}">

                                @if(!$organization->logo_url)
                                <div id="logo-initials"
                                    class="h-full w-full flex items-center justify-center rounded-full border-4 border-gray-100 shadow-sm bg-gray-100 text-gray-500 text-4xl font-bold absolute top-0 left-0">
                                    {{ $organization->initials }}
                                </div>
                                @endif

                                <label for="logo"
                                    class="absolute bottom-0 right-0 bg-organization-600 rounded-full p-2 cursor-pointer hover:bg-organization-700 text-white shadow-md z-10">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </label>
                                <input type="file" name="logo" id="logo" class="hidden" accept="image/*"
                                    onchange="previewLogo(this)">
                            </div>
                            <p class="text-xs text-gray-500 mt-2 text-center">PNG, JPG jusqu'à 2MB.</p>
                            @error('logo')
                            <p class="text-red-500 text-xs mt-1 text-center">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Colonne formulaire (9 cols) -->
                    <div class="md:col-span-8 space-y-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Nom de
                                l'organisation</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $organization->name) }}"
                                class="mt-1 focus:ring-organization-500 focus:border-organization-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                required>
                            @error('name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="contact_email" class="block text-sm font-medium text-gray-700">Email de contact
                                (Admin)</label>
                            <input type="email" name="contact_email" id="contact_email"
                                value="{{ old('contact_email', $organization->contact_email) }}"
                                class="mt-1 focus:ring-organization-500 focus:border-organization-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                required>
                            @error('contact_email')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700">Téléphone</label>
                                <input type="text" name="phone" id="phone"
                                    value="{{ old('phone', $organization->phone) }}"
                                    class="mt-1 focus:ring-organization-500 focus:border-organization-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                @error('phone')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="website" class="block text-sm font-medium text-gray-700">Site Web</label>
                                <input type="url" name="website" id="website"
                                    value="{{ old('website', $organization->website) }}"
                                    class="mt-1 focus:ring-organization-500 focus:border-organization-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                    placeholder="https://exemple.com">
                                @error('website')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label for="sector" class="block text-sm font-medium text-gray-700">Secteur
                                d'activité</label>
                            <input type="text" name="sector" id="sector"
                                value="{{ old('sector', $organization->sector) }}"
                                class="mt-1 focus:ring-organization-500 focus:border-organization-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            @error('sector')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea name="description" id="description" rows="3"
                                class="mt-1 focus:ring-organization-500 focus:border-organization-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">{{ old('description', $organization->description) }}</textarea>
                            @error('description')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Branding & Customization Section -->
                        <div class="pt-6 border-t border-gray-200">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Personnalisation (Marque
                                Blanche)</h3>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                                <!-- Primary Color -->
                                <div>
                                    <label for="primary_color" class="block text-sm font-medium text-gray-700">Couleur
                                        Principale</label>
                                    <div
                                        class="mt-1 flex items-center shadow-sm border border-gray-300 rounded-md overflow-hidden">
                                        <input type="color" name="primary_color" id="primary_color"
                                            value="{{ old('primary_color', $organization->primary_color ?? '#f43f5e') }}"
                                            class="h-10 w-12 border-0 p-0 rounded-l-md cursor-pointer">
                                        <input type="text"
                                            value="{{ old('primary_color', $organization->primary_color ?? '#f43f5e') }}"
                                            class="flex-1 focus:ring-organization-500 focus:border-organization-500 block w-full sm:text-sm border-0 px-3 uppercase font-mono"
                                            onchange="document.getElementById('primary_color').value = this.value">
                                    </div>
                                    @error('primary_color')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Secondary Color -->
                                <div>
                                    <label for="secondary_color" class="block text-sm font-medium text-gray-700">Couleur
                                        Secondaire</label>
                                    <div
                                        class="mt-1 flex items-center shadow-sm border border-gray-300 rounded-md overflow-hidden">
                                        <input type="color" name="secondary_color" id="secondary_color"
                                            value="{{ old('secondary_color', $organization->secondary_color ?? '#e11d48') }}"
                                            class="h-10 w-12 border-0 p-0 rounded-l-md cursor-pointer">
                                        <input type="text"
                                            value="{{ old('secondary_color', $organization->secondary_color ?? '#e11d48') }}"
                                            class="flex-1 focus:ring-organization-500 focus:border-organization-500 block w-full sm:text-sm border-0 px-3 uppercase font-mono"
                                            onchange="document.getElementById('secondary_color').value = this.value">
                                    </div>
                                    @error('secondary_color')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Accent Color -->
                                <div>
                                    <label for="accent_color" class="block text-sm font-medium text-gray-700">Couleur
                                        d'Accent</label>
                                    <div
                                        class="mt-1 flex items-center shadow-sm border border-gray-300 rounded-md overflow-hidden">
                                        <input type="color" name="accent_color" id="accent_color"
                                            value="{{ old('accent_color', $organization->accent_color ?? '#fb7185') }}"
                                            class="h-10 w-12 border-0 p-0 rounded-l-md cursor-pointer">
                                        <input type="text"
                                            value="{{ old('accent_color', $organization->accent_color ?? '#fb7185') }}"
                                            class="flex-1 focus:ring-organization-500 focus:border-organization-500 block w-full sm:text-sm border-0 px-3 uppercase font-mono"
                                            onchange="document.getElementById('accent_color').value = this.value">
                                    </div>
                                    @error('accent_color')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div>
                                <label for="custom_domain" class="block text-sm font-medium text-gray-700">Alias d'URL
                                    personnalisé (Ex: mondomaine.brillio.africa)</label>
                                <div class="mt-1 flex rounded-md shadow-sm">
                                    <input type="text" name="custom_domain" id="custom_domain"
                                        value="{{ old('custom_domain', str_replace('.brillio.africa', '', $organization->custom_domain)) }}"
                                        class="focus:ring-organization-500 focus:border-organization-500 flex-1 block w-full sm:text-sm border-gray-300 rounded-none rounded-l-md"
                                        placeholder="mondomaine">
                                    <span
                                        class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm">
                                        .brillio.africa
                                    </span>
                                </div>
                                <p class="mt-2 text-xs text-gray-500">Laissez vide pour utiliser l'URL par défaut de
                                    Brillio.</p>
                                @error('custom_domain')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end mt-6">
                    <button type="button" onclick="window.history.back()"
                        class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-organization-500 mr-3">
                        Annuler
                    </button>
                    <button type="submit"
                        class="bg-organization-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-organization-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-organization-500">
                        Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function previewLogo(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function (e) {
                var preview = document.getElementById('logo-preview');
                var initials = document.getElementById('logo-initials');

                preview.src = e.target.result;
                preview.classList.remove('hidden');

                if (initials) {
                    initials.classList.add('hidden');
                }
            }

            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endpush