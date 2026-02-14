@extends('layouts.admin')

@section('header')
<div class="flex items-center">
    <a href="{{ route('admin.organizations.index') }}" class="text-gray-500 hover:text-gray-700 mr-4">
        <i class="fas fa-arrow-left"></i> Retour
    </a>
    <h2 class="text-xl font-semibold text-gray-800">
        {{ isset($organization) ? 'Modifier l\'organisation' : 'Nouvelle Organisation' }}
    </h2>
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

@section('content')
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6 bg-white border-b border-gray-200">
        <form
            action="{{ isset($organization) ? route('admin.organizations.update', $organization) : route('admin.organizations.store') }}"
            method="POST" enctype="multipart/form-data">
            @csrf
            @if(isset($organization))
            @method('PUT')
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Colonne Gauche -->
                <div>
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700">Nom de l'organisation</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $organization->name ?? '') }}"
                            class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                            required>
                        @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="contact_email" class="block text-sm font-medium text-gray-700">Email de contact
                            (Admin)</label>
                        <input type="email" name="contact_email" id="contact_email"
                            value="{{ old('contact_email', $organization->contact_email ?? '') }}"
                            class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                            required>
                        @error('contact_email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="phone" class="block text-sm font-medium text-gray-700">Téléphone</label>
                        <input type="text" name="phone" id="phone"
                            value="{{ old('phone', $organization->phone ?? '') }}"
                            class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        @error('phone')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="website" class="block text-sm font-medium text-gray-700">Site Web</label>
                        <input type="url" name="website" id="website"
                            value="{{ old('website', $organization->website ?? '') }}"
                            class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                            placeholder="https://exemple.com">
                        @error('website')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Colonne Droite -->
                <div>
                    <div class="mb-4">
                        <label for="sector" class="block text-sm font-medium text-gray-700">Secteur d'activité</label>
                        <input type="text" name="sector" id="sector"
                            value="{{ old('sector', $organization->sector ?? '') }}"
                            class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        @error('sector')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="status" class="block text-sm font-medium text-gray-700">Statut</label>
                        <select name="status" id="status"
                            class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option value="active" {{ old('status', $organization->status ?? 'active') == 'active' ?
                                'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $organization->status ?? '') == 'inactive' ?
                                'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Logo</label>

                        <div class="mt-2 mb-2 relative h-20 w-20">
                            <!-- Image Preview -->
                            <img id="logo-preview"
                                src="{{ isset($organization) && $organization->logo_url ? $organization->logo_url : '' }}"
                                alt="Logo actuel"
                                class="h-20 w-20 object-cover rounded-md border text-xs {{ isset($organization) && $organization->logo_url ? '' : 'hidden' }}">

                            <!-- Initials Fallback -->
                            @if(isset($organization) && !$organization->logo_url)
                            <div id="logo-initials"
                                class="h-20 w-20 rounded-md bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-xl border border-indigo-200 absolute top-0 left-0">
                                {{ $organization->initials }}
                            </div>
                            @endif
                        </div>

                        <input type="file" name="logo" id="logo" class="mt-1 block w-full text-sm text-gray-500
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-full file:border-0
                                file:text-sm file:font-semibold
                                file:bg-indigo-50 file:text-indigo-700
                                hover:file:bg-indigo-100" onchange="previewLogo(this)">
                        <p class="text-xs text-gray-500 mt-1">PNG, JPG jusqu'à 2MB. Carré recommandé.</p>
                        @error('logo')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="mb-6">
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea name="description" id="description" rows="3"
                    class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">{{ old('description', $organization->description ?? '') }}</textarea>
                @error('description')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-end">
                <a href="{{ route('admin.organizations.index') }}"
                    class="text-gray-600 hover:text-gray-900 mr-4">Annuler</a>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                    {{ isset($organization) ? 'Mettre à jour' : 'Créer l\'organisation' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection