<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Espace Jeune') - Brillio</title>

    <!-- Favicons -->
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#6366f1">


    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#f0f4ff',
                            100: '#e0e9ff',
                            200: '#c7d6fe',
                            300: '#a4b8fc',
                            400: '#8093f8',
                            500: '#6366f1',
                            600: '#5145e5',
                            700: '#4536ca',
                            800: '#3a2fa3',
                            900: '#332c81',
                        }
                    }
                }
            }
        }
    </script>

    <style>
        .nav-item {
            transition: all 0.2s ease;
        }

        .nav-item:hover {
            transform: translateY(-2px);
        }

        .nav-item.active {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
        }

        .card-hover {
            transition: all 0.3s ease;
        }

        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px -12px rgba(99, 102, 241, 0.25);
        }

        .gradient-text {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #d946ef 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        [x-cloak] {
            display: none !important;
        }
    </style>

    @stack('styles')
</head>

<body class="font-sans antialiased bg-gray-50 min-h-screen">
    <!-- Navigation Top -->
    <nav class="bg-white border-b border-gray-100 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="{{ route('jeune.dashboard') }}" class="flex items-center gap-2">
                        <div
                            class="w-10 h-10 bg-gradient-to-br from-primary-500 to-purple-600 rounded-xl flex items-center justify-center">
                            <span class="text-xl font-bold text-white">B</span>
                        </div>
                        <span class="text-xl font-bold gradient-text hidden sm:block">Brillio</span>
                    </a>
                </div>

                <!-- Navigation Items (Desktop) -->
                <div class="hidden md:flex items-center gap-2">
                    <a href="{{ route('jeune.dashboard') }}"
                        class="nav-item px-4 py-2 rounded-xl text-sm font-medium {{ request()->routeIs('jeune.dashboard') ? 'active' : 'text-gray-600 hover:bg-gray-100' }}">
                        Accueil
                    </a>
                    <a href="{{ route('jeune.personality') }}"
                        class="nav-item px-4 py-2 rounded-xl text-sm font-medium {{ request()->routeIs('jeune.personality') ? 'active' : 'text-gray-600 hover:bg-gray-100' }}">
                        Personnalité
                    </a>
                    <a href="{{ route('jeune.chat') }}"
                        class="nav-item px-4 py-2 rounded-xl text-sm font-medium {{ request()->routeIs('jeune.chat') ? 'active' : 'text-gray-600 hover:bg-gray-100' }}">
                        Assistant IA
                    </a>
                    <a href="{{ route('jeune.resources.index') }}"
                        class="nav-item px-4 py-2 rounded-xl text-sm font-medium {{ request()->routeIs('jeune.resources.*') ? 'active' : 'text-gray-600 hover:bg-gray-100' }}">
                        Ressources
                    </a>
                    <a href="{{ route('jeune.documents') }}"
                        class="nav-item px-4 py-2 rounded-xl text-sm font-medium {{ request()->routeIs('jeune.documents') ? 'active' : 'text-gray-600 hover:bg-gray-100' }}">
                        Documents
                    </a>
                    <a href="{{ route('jeune.mentors') }}"
                        class="nav-item px-4 py-2 rounded-xl text-sm font-medium {{ request()->routeIs('jeune.mentors') ? 'active' : 'text-gray-600 hover:bg-gray-100' }}">
                        Mentors
                    </a>
                </div>

                <!-- Profile Dropdown -->
                <div class="flex items-center gap-4">
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open"
                            class="flex items-center gap-2 p-2 rounded-xl hover:bg-gray-100 transition">
                            <div
                                class="w-8 h-8 rounded-full bg-gradient-to-br from-primary-400 to-purple-500 flex items-center justify-center">
                                @if(auth()->user()->avatar_url)
                                    <img src="{{ auth()->user()->avatar_url }}" alt=""
                                        class="w-8 h-8 rounded-full object-cover">
                                @else
                                    <span
                                        class="text-sm font-semibold text-white">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                                @endif
                            </div>
                            <span
                                class="hidden sm:block text-sm font-medium text-gray-700">{{ auth()->user()->name }}</span>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="open" @click.away="open = false" x-transition
                            class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-100 py-2 z-50">
                            <a href="{{ route('jeune.profile') }}"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                Mon profil
                            </a>
                            <a href="{{ route('jeune.wallet.index') }}"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                Mon Portefeuille ({{ auth()->user()->credits_balance }})
                            </a>
                            <hr class="my-2 border-gray-100">
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                    Deconnexion
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Navigation -->
        <div class="md:hidden border-t border-gray-100 px-4 py-2 flex gap-2 overflow-x-auto">
            <a href="{{ route('jeune.dashboard') }}"
                class="nav-item flex-shrink-0 px-3 py-2 rounded-lg text-xs font-medium {{ request()->routeIs('jeune.dashboard') ? 'active' : 'text-gray-600 bg-gray-100' }}">
                Accueil
            </a>
            <a href="{{ route('jeune.personality') }}"
                class="nav-item flex-shrink-0 px-3 py-2 rounded-lg text-xs font-medium {{ request()->routeIs('jeune.personality') ? 'active' : 'text-gray-600 bg-gray-100' }}">
                Personnalité
            </a>
            <a href="{{ route('jeune.chat') }}"
                class="nav-item flex-shrink-0 px-3 py-2 rounded-lg text-xs font-medium {{ request()->routeIs('jeune.chat') ? 'active' : 'text-gray-600 bg-gray-100' }}">
                Assistant
            </a>
            <a href="{{ route('jeune.resources.index') }}"
                class="nav-item flex-shrink-0 px-3 py-2 rounded-lg text-xs font-medium {{ request()->routeIs('jeune.resources.*') ? 'active' : 'text-gray-600 bg-gray-100' }}">
                Ressources
            </a>
            <a href="{{ route('jeune.documents') }}"
                class="nav-item flex-shrink-0 px-3 py-2 rounded-lg text-xs font-medium {{ request()->routeIs('jeune.documents') ? 'active' : 'text-gray-600 bg-gray-100' }}">
                Docs
            </a>
            <a href="{{ route('jeune.mentors') }}"
                class="nav-item flex-shrink-0 px-3 py-2 rounded-lg text-xs font-medium {{ request()->routeIs('jeune.mentors') ? 'active' : 'text-gray-600 bg-gray-100' }}">
                Mentors
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-100 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                <p class="text-sm text-gray-500">2026 Brillio. Tous droits reserves.</p>
                <div class="flex gap-4">
                    <a href="{{ route('about') }}" class="text-sm text-gray-500 hover:text-primary-600">A propos</a>
                    <a href="{{ route('contact') }}" class="text-sm text-gray-500 hover:text-primary-600">Contact</a>
                    <a href="{{ route('privacy-policy') }}"
                        class="text-sm text-gray-500 hover:text-primary-600">Confidentialite</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Cookie Consent Banner -->
    <div id="cookieBanner"
        class="hidden fixed bottom-0 left-0 right-0 bg-white border-t-2 border-primary-500 shadow-2xl z-50 p-4 sm:p-6">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-start gap-3 flex-1">
                <svg class="w-6 h-6 text-primary-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <p class="text-sm text-gray-700">
                        <strong>Nous utilisons des cookies</strong> pour améliorer votre expérience et analyser
                        l'utilisation de notre site.
                        En continuant, vous acceptez notre <a href="{{ route('privacy-policy') }}"
                            class="text-primary-600 hover:underline">politique de confidentialité</a>.
                    </p>
                </div>
            </div>
            <button onclick="acceptCookies()"
                class="px-6 py-2 bg-primary-600 text-white font-semibold rounded-xl hover:bg-primary-700 transition whitespace-nowrap">
                Accepter
            </button>
        </div>
    </div>

    <script>
        // Gestion du consentement aux cookies
        function acceptCookies() {
            // Stocker dans localStorage
            localStorage.setItem('cookiesAccepted', 'true');

            // Envoyer au serveur
            fetch('{{ route("accept-cookies") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            // Cacher la bannière
            document.getElementById('cookieBanner').classList.add('hidden');
        }

        // Afficher la bannière si pas encore accepté
        if (!localStorage.getItem('cookiesAccepted')) {
            document.getElementById('cookieBanner').classList.remove('hidden');
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @stack('scripts')
    @include('partials.toast')
</body>

</html>