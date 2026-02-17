<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="api-token" content="{{ auth()->user()->createToken('mentor-wallet')->plainTextToken }}">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Espace Mentor') - Brillio</title>

    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-PPX01GY0R9"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }
        gtag('js', new Date());
        gtag('config', 'G-PPX01GY0R9');
    </script>

    <!-- Favicons -->
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#f97316">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        mentor: {
                            50: '#fff7ed',
                            100: '#ffedd5',
                            200: '#fed7aa',
                            300: '#fdba74',
                            400: '#fb923c',
                            500: '#f97316',
                            600: '#ea580c',
                            700: '#c2410c',
                            800: '#9a3412',
                            900: '#7c2d12',
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
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            color: white;
        }

        .card-hover {
            transition: all 0.3s ease;
        }

        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px -12px rgba(249, 115, 22, 0.25);
        }

        .gradient-text {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 50%, #dc2626 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
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
                    <a href="{{ route('mentor.dashboard') }}" class="flex items-center gap-2">
                        <div
                            class="w-10 h-10 bg-gradient-to-br from-orange-500 to-red-600 rounded-xl flex items-center justify-center">
                            <span class="text-xl font-bold text-white">B</span>
                        </div>
                        <span class="text-xl font-bold gradient-text hidden sm:block">Brillio Mentor</span>
                    </a>
                </div>

                <!-- Navigation Items (Desktop) -->
                <div class="hidden md:flex items-center gap-2">
                    <a href="{{ route('mentor.dashboard') }}"
                        class="nav-item px-4 py-2 rounded-xl text-sm font-medium {{ request()->routeIs('mentor.dashboard') ? 'active' : 'text-gray-600 hover:bg-gray-100' }}">
                        Tableau de bord
                    </a>

                    <a href="{{ route('mentor.explore') }}"
                        class="nav-item px-4 py-2 rounded-xl text-sm font-medium {{ request()->routeIs('mentor.explore') ? 'active' : 'text-gray-600 hover:bg-gray-100' }}">
                        Explorer
                    </a>
                    <a href="{{ route('mentor.personality') }}"
                        class="nav-item px-4 py-2 rounded-xl text-sm font-medium {{ request()->routeIs('mentor.personality') ? 'active' : 'text-gray-600 hover:bg-gray-100' }}">
                        Personnalité
                    </a>
                    <a href="{{ route('mentor.roadmap') }}"
                        class="nav-item px-4 py-2 rounded-xl text-sm font-medium {{ request()->routeIs('mentor.roadmap') ? 'active' : 'text-gray-600 hover:bg-gray-100' }}">
                        Mon parcours
                    </a>
                    <!-- Mentorat Dropdown -->
                    <div class="relative" x-data="{ openMentorship: false }" @mouseenter="openMentorship = true"
                        @mouseleave="openMentorship = false">
                        <button
                            class="nav-item px-4 py-2 rounded-xl text-sm font-medium flex items-center gap-1 {{ request()->routeIs('mentor.resources.*') || request()->routeIs('mentor.mentorship.*') ? 'active' : 'text-gray-600 hover:bg-gray-100' }}">
                            Mentorat
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <div x-show="openMentorship" x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="transform opacity-100 scale-100"
                            x-transition:leave-end="transform opacity-0 scale-95"
                            class="absolute left-0 mt-0 w-56 bg-white rounded-xl shadow-lg border border-gray-100 py-2 z-50">

                            <a href="{{ route('mentor.resources.index') }}"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-orange-50 hover:text-orange-600">
                                Mes Ressources
                            </a>
                            <a href="{{ route('mentor.mentorship.index') }}"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-orange-50 hover:text-orange-600">
                                Mes Mentés
                            </a>
                            <a href="{{ route('mentor.mentorship.calendar') }}"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-orange-50 hover:text-orange-600">
                                Calendrier & Séances
                            </a>
                        </div>
                    </div>
                    <a href="{{ route('mentor.stats') }}"
                        class="nav-item px-4 py-2 rounded-xl text-sm font-medium {{ request()->routeIs('mentor.stats') ? 'active' : 'text-gray-600 hover:bg-gray-100' }}">
                        Statistiques
                    </a>
                </div>

                <!-- Profile Dropdown -->
                <div class="flex items-center gap-4">
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open"
                            class="flex items-center gap-2 p-2 rounded-xl hover:bg-gray-100 transition">
                            <div
                                class="w-8 h-8 rounded-full bg-gradient-to-br from-orange-400 to-red-500 flex items-center justify-center">
                                @if(auth()->user()->avatar_url)
                                <img src="{{ auth()->user()->avatar_url }}" alt=""
                                    class="w-8 h-8 rounded-full object-cover">
                                @else
                                <span class="text-sm font-semibold text-white">{{
                                    strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                                @endif
                            </div>
                            <span class="hidden sm:block text-sm font-medium text-gray-700">{{ auth()->user()->name
                                }}</span>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="open" @click.away="open = false" x-transition
                            class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-100 py-2 z-50">
                            <a href="{{ route('mentor.profile') }}"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                Mon profil
                            </a>
                            <a href="{{ route('mentor.wallet.index') }}"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center justify-between">
                                <span>Mon Portefeuille ({{ auth()->user()->credits_balance }})</span>
                                <span
                                    class="animate-pulse bg-orange-100 text-orange-800 text-[10px] font-bold px-1.5 py-0.5 rounded-full">NEW</span>
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
            <a href="{{ route('mentor.dashboard') }}"
                class="nav-item flex-shrink-0 px-3 py-2 rounded-lg text-xs font-medium {{ request()->routeIs('mentor.dashboard') ? 'active' : 'text-gray-600 bg-gray-100' }}">
                Dashboard
            </a>

            <a href="{{ route('mentor.explore') }}"
                class="nav-item flex-shrink-0 px-3 py-2 rounded-lg text-xs font-medium {{ request()->routeIs('mentor.explore') ? 'active' : 'text-gray-600 bg-gray-100' }}">
                Explorer
            </a>
            <a href="{{ route('mentor.personality') }}"
                class="nav-item flex-shrink-0 px-3 py-2 rounded-lg text-xs font-medium {{ request()->routeIs('mentor.personality') ? 'active' : 'text-gray-600 bg-gray-100' }}">
                Personnalité
            </a>
            <a href="{{ route('mentor.roadmap') }}"
                class="nav-item flex-shrink-0 px-3 py-2 rounded-lg text-xs font-medium {{ request()->routeIs('mentor.roadmap') ? 'active' : 'text-gray-600 bg-gray-100' }}">
                Parcours
            </a>
            <a href="{{ route('mentor.resources.index') }}"
                class="nav-item flex-shrink-0 px-3 py-2 rounded-lg text-xs font-medium {{ request()->routeIs('mentor.resources.*') ? 'active' : 'text-gray-600 bg-gray-100' }}">
                Ressources
            </a>
            <a href="{{ route('mentor.stats') }}"
                class="nav-item flex-shrink-0 px-3 py-2 rounded-lg text-xs font-medium {{ request()->routeIs('mentor.stats') ? 'active' : 'text-gray-600 bg-gray-100' }}">
                Stats
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
                <p class="text-sm text-gray-500">2026 Brillio. Merci de partager votre experience.</p>
                <div class="flex gap-4">
                    <a href="{{ route('about') }}" class="text-sm text-gray-500 hover:text-orange-600">A propos</a>
                    <a href="{{ route('contact') }}" class="text-sm text-gray-500 hover:text-orange-600">Contact</a>
                </div>
            </div>
        </div>
    </footer>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @stack('scripts')
    @include('partials.toast')
</body>

</html>