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
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4 flex items-center">
                                Personnalisation (Marque Blanche)
                                @if(!$organization->isEnterprise())
                                <span
                                    class="ml-3 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    <svg class="-ml-0.5 mr-1.5 h-3 w-3 text-yellow-400" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    Plan Enterprise Requis
                                </span>
                                @endif
                            </h3>

                            @if($organization->isEnterprise())
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

                            <!-- Success Banner for Domain Update -->
                            @if(session('domain_updated'))
                            <div
                                class="mb-8 bg-green-50 border-l-4 border-green-400 p-6 rounded-r-lg shadow-sm animate-pulse-subtle">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-sm font-bold text-green-800 uppercase tracking-wide">
                                            Félicitations ! Votre espace est prêt</h3>
                                        <div class="mt-2 text-sm text-green-700">
                                            <p>Votre organisation est désormais accessible via votre propre lien
                                                personnalisé. Vous pouvez dès à présent l'utiliser pour inviter vos
                                                membres.</p>
                                        </div>
                                        <div class="mt-4">
                                            <a href="{{ session('new_url') }}" target="_blank"
                                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 shadow-sm transition-all">
                                                Accéder à mon nouvel espace
                                                <svg class="ml-2 -mr-1 h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                </svg>
                                            </a>
                                        </div>
                                        <p class="mt-3 text-xs text-green-600 italic">* Vous pourriez avoir besoin de
                                            vous reconnecter sur la nouvelle URL pour des raisons de sécurité.</p>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <div>
                                <label for="custom_domain" class="block text-sm font-medium text-gray-700">Alias d'URL
                                    personnalisé (Ex: mondomaine.{{ parse_url(config('app.url'), PHP_URL_HOST) ??
                                    'brillio.africa' }})</label>
                                <div class="mt-1 flex rounded-md shadow-sm relative">
                                    <input type="text" name="custom_domain" id="custom_domain"
                                        value="{{ old('custom_domain', str_replace('.' . (parse_url(config('app.url'), PHP_URL_HOST) ?? 'brillio.africa'), '', $organization->custom_domain)) }}"
                                        class="focus:ring-organization-500 focus:border-organization-500 flex-1 block w-full sm:text-sm border-gray-300 rounded-none rounded-l-md"
                                        placeholder="mondomaine" autocomplete="off">
                                    <span
                                        class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm">
                                        .{{ parse_url(config('app.url'), PHP_URL_HOST) ?? 'brillio.africa' }}
                                    </span>
                                </div>

                                <!-- Availability Indicator -->
                                <div id="domain-checker-feedback" class="mt-2 text-xs hidden flex items-center">
                                    <span id="checker-spinner" class="mr-2 hidden">
                                        <svg class="animate-spin h-3 w-3 text-gray-400"
                                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                            </path>
                                        </svg>
                                    </span>
                                    <span id="checker-status-text"></span>
                                </div>

                                <p class="mt-2 text-xs text-gray-500">Laissez vide pour utiliser l'URL par défaut de
                                    Brillio.</p>
                                @error('custom_domain')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            @else
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">Fonctionnalité Premium "Marque
                                    Blanche"</h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    Sublimez votre espace partenaire avec vos propres couleurs (primaire, secondaire) et
                                    votre nom de domaine personnalisé. Intégration transparente pour votre équipe.<br>
                                    Accessible uniquement avec le plan <strong>Enterprise (50.000 FCFA/mois)</strong>.
                                </p>
                                <div class="mt-6">
                                    <a href="{{ route('organization.subscriptions.index') }}"
                                        class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-organization-600 hover:bg-organization-700">
                                        Mettre à niveau le plan
                                    </a>
                                </div>
                            </div>
                            @endif
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

    // Domain availability check
    const domainInput = document.getElementById('custom_domain');
    const feedbackText = document.getElementById('checker-status-text');
    const feedbackContainer = document.getElementById('domain-checker-feedback');
    const spinner = document.getElementById('checker-spinner');
    let debounceTimer;

    if (domainInput) {
        domainInput.addEventListener('input', function () {
            const domain = this.value.trim();

            clearTimeout(debounceTimer);

            if (domain.length < 2) {
                feedbackContainer.classList.add('hidden');
                return;
            }

            debounceTimer = setTimeout(() => {
                checkDomain(domain);
            }, 500);
        });
    }

    async function checkDomain(domain) {
        feedbackContainer.classList.remove('hidden');
        spinner.classList.remove('hidden');
        feedbackText.innerText = 'Vérification...';
        feedbackText.className = 'text-gray-500';

        try {
            const response = await fetch(`{{ route('organization.profile.check-domain') }}?domain=${encodeURIComponent(domain)}`);
            const data = await response.json();

            spinner.classList.add('hidden');

            if (data.available) {
                feedbackText.innerText = data.message;
                feedbackText.className = 'text-green-600 font-medium';
            } else {
                feedbackText.innerText = data.message;
                feedbackText.className = 'text-red-500 font-medium';
            }
        } catch (error) {
            spinner.classList.add('hidden');
            feedbackText.innerText = 'Erreur lors de la vérification.';
            feedbackText.className = 'text-red-500';
            console.error('Domain check error:', error);
        }
    }
</script>
@endpush