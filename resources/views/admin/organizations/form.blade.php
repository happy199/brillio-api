@extends('layouts.admin')

@section('header')
<div class="flex items-center">
    <a href="{{ route('admin.organizations.index') }}" class="text-gray-500 hover:text-gray-700 mr-4 transition-colors">
        <i class="fas fa-arrow-left"></i> Retour
    </a>
    <h2 class="text-2xl font-bold text-gray-900">
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
                if (initials) initials.classList.add('hidden');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function toggleExpiryField() {
        var plan = document.getElementById('subscription_plan').value;
        var container = document.getElementById('subscription_expiry_container');
        if (plan === 'pro' || plan === 'enterprise') {
            container.classList.remove('hidden');
        } else {
            container.classList.add('hidden');
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        var select = document.getElementById('subscription_plan');
        if (select) {
            select.addEventListener('change', toggleExpiryField);
            toggleExpiryField(); // Run on load to handle pre-filled values
        }
    });
</script>
@endpush

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="bg-white shadow-xl rounded-2xl overflow-hidden border border-gray-100">
        <div class="p-8">
            <form
                action="{{ isset($organization) ? route('admin.organizations.update', $organization) : route('admin.organizations.store') }}"
                method="POST" enctype="multipart/form-data">
                @csrf
                @if(isset($organization))
                @method('PUT')
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                    <!-- Colonne Gauche : Informations de base -->
                    <div class="space-y-6">
                        <h3 class="text-lg font-bold text-gray-800 border-b border-gray-50 pb-2">Informations Générales
                        </h3>

                        <div>
                            <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Nom de
                                l'organisation</label>
                            <input type="text" name="name" id="name"
                                value="{{ old('name', $organization->name ?? '') }}"
                                class="p-3 bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block w-full transition-all"
                                required placeholder="Ex: Brillio Corp">
                            @error('name')
                            <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="contact_email" class="block text-sm font-semibold text-gray-700 mb-2">Email de
                                contact (Admin)</label>
                            <input type="email" name="contact_email" id="contact_email"
                                value="{{ old('contact_email', $organization->contact_email ?? '') }}"
                                class="p-3 bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block w-full transition-all"
                                required placeholder="admin@organisation.com">
                            @error('contact_email')
                            <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="phone"
                                    class="block text-sm font-semibold text-gray-700 mb-2">Téléphone</label>
                                <input type="text" name="phone" id="phone"
                                    value="{{ old('phone', $organization->phone ?? '') }}"
                                    class="p-3 bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block w-full transition-all">
                                @error('phone')
                                <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="sector"
                                    class="block text-sm font-semibold text-gray-700 mb-2">Secteur</label>
                                <input type="text" name="sector" id="sector"
                                    value="{{ old('sector', $organization->sector ?? '') }}"
                                    class="p-3 bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block w-full transition-all"
                                    placeholder="Ex: Éducation">
                                @error('sector')
                                <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label for="website" class="block text-sm font-semibold text-gray-700 mb-2">Site Web</label>
                            <input type="url" name="website" id="website"
                                value="{{ old('website', $organization->website ?? '') }}"
                                class="p-3 bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block w-full transition-all"
                                placeholder="https://exemple.com">
                            @error('website')
                            <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Colonne Droite : Configuration & Logo -->
                    <div class="space-y-6">
                        <h3 class="text-lg font-bold text-gray-800 border-b border-gray-50 pb-2">Paramètres & Visuel
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="status"
                                    class="block text-sm font-semibold text-gray-700 mb-2">Statut</label>
                                <select name="status" id="status"
                                    class="p-3 bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block w-full appearance-none transition-all">
                                    <option value="active" {{ old('status', $organization->status ?? 'active') ==
                                        'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status', $organization->status ?? '') == 'inactive'
                                        ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                            <div>
                                <label for="subscription_plan"
                                    class="block text-sm font-semibold text-gray-700 mb-2">Plan d'abonnement</label>
                                <select name="subscription_plan" id="subscription_plan"
                                    class="p-3 bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block w-full appearance-none transition-all">
                                    <option value="free" {{ old('subscription_plan', $organization->subscription_plan ??
                                        'free') == 'free' ? 'selected' : '' }}>Standard (Gratuit)</option>
                                    <option value="pro" {{ old('subscription_plan', $organization->subscription_plan ??
                                        '') == 'pro' ? 'selected' : '' }}>Pro</option>
                                    <option value="enterprise" {{ old('subscription_plan', $organization->
                                        subscription_plan ?? '') == 'enterprise' ? 'selected' : '' }}>Enterprise
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div id="subscription_expiry_container"
                            class="{{ old('subscription_plan', $organization->subscription_plan ?? 'free') == 'free' ? 'hidden' : '' }}">
                            <label for="subscription_expires_at"
                                class="block text-sm font-semibold text-gray-700 mb-2">Date d'expiration</label>
                            <input type="date" name="subscription_expires_at" id="subscription_expires_at"
                                value="{{ old('subscription_expires_at', (isset($organization) && $organization->subscription_expires_at) ? $organization->subscription_expires_at->format('Y-m-d') : '') }}"
                                class="p-3 bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block w-full transition-all">
                            @error('subscription_expires_at')
                            <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                            @enderror
                            <p class="text-[11px] text-gray-500 mt-2 font-medium">Obligatoire pour les plans Pro et
                                Enterprise.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">Logo de l'organisation</label>
                            <div class="flex items-center gap-6">
                                <div class="relative h-24 w-24 flex-shrink-0 group">
                                    <img id="logo-preview"
                                        src="{{ isset($organization) && $organization->logo_url ? $organization->logo_url : '' }}"
                                        alt="Logo actuel"
                                        class="h-24 w-24 object-cover rounded-2xl border-2 border-gray-100 shadow-sm {{ isset($organization) && $organization->logo_url ? '' : 'hidden' }}">
                                    @if(isset($organization) && !$organization->logo_url)
                                    <div id="logo-initials"
                                        class="h-24 w-24 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 font-extrabold text-3xl border-2 border-indigo-100 shadow-sm">
                                        {{ $organization->initials }}
                                    </div>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <input type="file" name="logo" id="logo" class="block w-full text-sm text-gray-500
                                        file:mr-4 file:py-2.5 file:px-4
                                        file:rounded-xl file:border-0
                                        file:text-sm file:font-bold
                                        file:bg-indigo-600 file:text-white
                                        hover:file:bg-indigo-700 transition-all cursor-pointer"
                                        onchange="previewLogo(this)">
                                    <p class="text-[11px] text-gray-500 mt-2 font-medium">PNG, JPG jusqu'à 2MB. Carré
                                        recommandé pour un meilleur affichage.</p>
                                </div>
                            </div>
                            @error('logo')
                            <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mb-8">
                    <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">Description /
                        Présentation</label>
                    <textarea name="description" id="description" rows="4"
                        class="p-3 bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block w-full transition-all"
                        placeholder="Parlez-nous de l'organisation...">{{ old('description', $organization->description ?? '') }}</textarea>
                    @error('description')
                    <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-end gap-x-6 border-t border-gray-50 pt-8">
                    <a href="{{ route('admin.organizations.index') }}"
                        class="text-sm font-bold text-gray-600 hover:text-gray-900 transition-colors uppercase tracking-wider">Annuler</a>
                    <button type="submit"
                        class="bg-gray-900 hover:bg-black text-white font-bold py-4 px-8 rounded-xl shadow-lg hover:shadow-xl transition-all uppercase tracking-widest text-xs">
                        {{ isset($organization) ? 'Mettre à jour l\'organisation' : 'Créer l\'organisation' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection