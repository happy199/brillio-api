<!DOCTYPE html>
<html lang="fr">

<head>
    @php
    $org = $current_organization ?? (auth()->check() ? auth()->user()->organization : null);
    $isEnterprise = $org && $org->isEnterprise();
    $primary = $org?->primary_color ?? '#f43f5e';

    // Fallback logic for secondary and accent
    $secondary = $org?->secondary_color ?? '#e11d48';
    $accent = $org?->accent_color ?? '#fb7185';
    @endphp
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Espace Organisation') - Brillio</title>

    <!-- Favicons -->
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="{{ $primary }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>

        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        organization: {
                            50: '{{ $isEnterprise ? $primary . "10" : "#fff1f2" }}',
                            100: '{{ $isEnterprise ? $primary . "20" : "#ffe4e6" }}',
                            200: '{{ $isEnterprise ? $primary . "30" : "#fecdd3" }}',
                            300: '{{ $accent }}',
                            400: '{{ $accent }}',
                            500: '{{ $primary }}',
                            600: '{{ $secondary }}',
                            700: '{{ $secondary }}',
                            800: '{{ $isEnterprise ? $primary : "#9f1239" }}',
                            900: '{{ $isEnterprise ? $primary : "#881337" }}',
                        }
                    }
                }
            }
        }
    </script>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    @stack('styles')
</head>

<body class="bg-gray-50 font-sans">

    <!-- Top Navigation Bar -->
    <nav class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('organization.dashboard') }}" class="flex items-center">
                        @if($org && $org->isEnterprise() && $org->logo_url)
                        <img src="{{ $org->logo_url }}" alt="{{ $org->name }}"
                            class="h-10 w-auto max-w-[150px] object-contain">
                        @else
                        <div class="flex items-center space-x-3">
                            <div
                                class="w-8 h-8 bg-gradient-to-br from-organization-400 to-organization-600 rounded-lg flex items-center justify-center">
                                <span class="text-white font-bold text-sm">B</span>
                            </div>
                            <span class="text-xl font-bold text-gray-900">Brillio<span
                                    class="text-organization-500">Partner</span></span>
                        </div>
                        @endif
                    </a>

                    <!-- Navigation Links -->
                    <div class="hidden md:ml-10 md:flex md:space-x-8">
                        <a href="{{ route('organization.dashboard') }}"
                            class="{{ request()->routeIs('organization.dashboard') ? 'border-organization-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Tableau de bord
                        </a>

                        <a href="{{ route('organization.users.index') }}"
                            class="{{ request()->routeIs('organization.users.*') ? 'border-organization-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Jeunes
                        </a>
                        <a href="{{ route('organization.mentors.index') }}"
                            class="{{ request()->routeIs('organization.mentors.*') ? 'border-organization-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Mentors
                        </a>
                        <a href="{{ route('organization.mentorships.index') }}"
                            class="{{ request()->routeIs('organization.mentorships.*') ? 'border-organization-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Mentorat
                        </a>
                        <a href="{{ route('organization.sessions.index') }}"
                            class="{{ request()->routeIs('organization.sessions.*') ? 'border-organization-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Calendrier
                        </a>

                        <a href="{{ route('organization.invitations.index') }}"
                            class="{{ request()->routeIs('organization.invitations.*') ? 'border-organization-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Invitations
                        </a>
                    </div>
                </div>

                <div class="flex items-center space-x-4">
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" @click.away="open = false"
                            class="flex items-center space-x-2 text-sm text-gray-700 hover:text-gray-900 focus:outline-none">
                            @if(auth()->user()->organization && auth()->user()->organization->logo_url)
                            <img src="{{ auth()->user()->organization->logo_url }}" alt="Logo"
                                class="h-8 w-8 rounded-full border border-gray-200 object-cover">
                            @elseif(auth()->user()->organization)
                            <div
                                class="h-8 w-8 rounded-full bg-organization-100 flex items-center justify-center text-organization-600 font-bold border border-organization-200 text-xs">
                                {{ auth()->user()->organization->initials }}
                            </div>
                            @else
                            <div
                                class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 text-xs font-bold">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </div>
                            @endif
                            <span class="font-medium">{{ auth()->user()->name }}</span>
                            <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="open" x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="transform opacity-100 scale-100"
                            x-transition:leave-end="transform opacity-0 scale-95"
                            class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 ring-1 ring-black ring-opacity-5"
                            style="display: none;">

                            @if(auth()->user()->organization_role !== 'viewer')
                            <a href="{{ route('organization.profile.edit') }}"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                Profil
                            </a>

                            @if($org && $org->isEnterprise())
                            <a href="{{ route('organization.team.index') }}"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                Équipe
                            </a>
                            @endif

                            <a href="{{ route('organization.subscriptions.index') }}"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                Abonnement
                            </a>

                            <a href="{{ route('organization.wallet.index') }}"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                Portefeuille
                            </a>
                            @endif

                            <div class="border-t border-gray-100"></div>

                            <form method="POST" action="{{ route('organization.logout') }}">
                                @csrf
                                <button type="submit"
                                    class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                    Déconnexion
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if(session('success'))
        <div class="mb-6 bg-organization-50 border border-organization-200 text-organization-800 px-4 py-3 rounded-lg">
            {{ session('success') }}

            @if(session('invitation_url'))
            <div class="mt-3 p-3 bg-white rounded border border-organization-300">
                <p class="text-sm font-semibold text-gray-700 mb-2">Lien d'invitation :</p>
                <div class="flex items-center space-x-2">
                    <input type="text" readonly value="{{ session('invitation_url') }}" id="invitation-url-input"
                        class="flex-1 text-sm px-3 py-2 border border-gray-300 rounded-md bg-gray-50 font-mono">
                    <button onclick="copyInvitationUrl()"
                        class="px-4 py-2 bg-organization-600 text-white text-sm font-medium rounded-md hover:bg-organization-700">
                        Copier
                    </button>
                </div>
            </div>
            @endif
        </div>
        @endif

        @if(session('error'))
        <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
            {{ session('error') }}
        </div>
        @endif

        @yield('content')
    </main>

    @stack('scripts')

    <!-- Toast Notification -->
    <div x-data="{ 
        showToast: false, 
        toastMessage: '', 
        toastType: 'success',
        show(message, type = 'success') {
            this.toastMessage = message;
            this.toastType = type;
            this.showToast = true;
            setTimeout(() => this.showToast = false, 3000);
        }
    }" @copy-notification.window="show($event.detail.message, $event.detail.type)"
        class="fixed bottom-5 right-5 z-[100]">
        <div x-show="showToast" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-4"
            class="bg-white border-l-4 rounded-lg shadow-xl p-4 flex items-center space-x-3 min-w-[300px]"
            :class="toastType === 'success' ? 'border-organization-500' : 'border-red-500'" style="display: none;">
            <div class="flex-shrink-0">
                <template x-if="toastType === 'success'">
                    <svg class="h-6 w-6 text-organization-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </template>
                <template x-if="toastType === 'error'">
                    <svg class="h-6 w-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </template>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-900" x-text="toastMessage"></p>
            </div>
            <button @click="showToast = false" class="ml-auto text-gray-400 hover:text-gray-500">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    <script>
        function copyInvitationUrl() {
            const input = document.getElementById('invitation-url-input');
            input.select();
            input.setSelectionRange(0, 99999); // For mobile devices
            navigator.clipboard.writeText(input.value).then(function () {
                window.dispatchEvent(new CustomEvent('copy-notification', {
                    detail: { message: 'Lien d\'invitation copié !', type: 'success' }
                }));
            }, function (err) {
                wispatchEvent(new CustomEvent('copy-notification', {
                    detail: { message: 'Erreur lors de la copie.', type: 'error' }
                }));
            });
        }
    </script>
</body>

</html>