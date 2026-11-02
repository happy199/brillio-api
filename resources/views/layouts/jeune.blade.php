<!DOCTYPE html>
<html lang="fr">
@php
    $displayOrg = $current_organization ?? (auth()->check() ? (auth()->user()->sponsoringOrganization ?? auth()->user()->organizations->first()) : null);
    $primaryColor = $displayOrg?->primary_color ?? '#6366f1';
    $secondaryColor = $displayOrg?->secondary_color ?? '#8b5cf6';
    $accentColor = $displayOrg?->accent_color ?? '#d946ef';
@endphp
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Espace Jeune') - {{ $displayOrg ? $displayOrg->name : 'Brillio' }}</title>

    @include('partials.analytics')
    @include('partials.pwa-head')


    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Suppress Tailwind CDN warning -->
    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        (function() {
            const originalWarn = console.warn;
            console.warn = function(...args) {
                if (args[0] && typeof args[0] === 'string' && args[0].includes('cdn.tailwindcss.com')) return;
                originalWarn.apply(console, args);
            };
        })();
    </script>
    <script nonce="{{ request()->attributes->get('csp_nonce') }}" src="https://cdn.tailwindcss.com"></script>
    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
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
                            500: '{{ $primaryColor }}',
                            600: '{{ $primaryColor }}',
                            700: '#4536ca',
                            800: '#3a2fa3',
                            900: '#332c81',
                        }
                    }
                }
            }
        }
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
        html, body {
            touch-action: manipulation;
            -webkit-tap-highlight-color: transparent;
        }
        a, button, [role="button"] {
            touch-action: manipulation;
        }
        .nav-item {
            transition: all 0.2s ease;
        }

        .nav-item:hover {
            transform: translateY(-2px);
        }

        .nav-item.active {
            background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $secondaryColor }} 100%) !important;
            color: white !important;
        }

        .card-hover {
            transition: all 0.3s ease;
        }

        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px -12px rgba(99, 102, 241, 0.25);
        }

        .gradient-text {
            background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $secondaryColor }} 50%, {{ $accentColor }} 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .org-gradient-banner {
            background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $secondaryColor }} 50%, {{ $accentColor }} 100%) !important;
        }

        [x-cloak] {
            display: none !important;
        }
    </style>

    @stack('styles')
</head>

<body x-data="{ mobileMenuOpen: false }" class="font-sans antialiased bg-gray-50 min-h-screen">
    <!-- Navigation Top -->
    <nav class="bg-white/95 backdrop-blur-md border-b border-gray-100 sticky top-0 z-50 pt-[env(safe-area-inset-top)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Mobile App Bar (Figma-Harmonized) -->
            <div class="flex md:hidden items-center justify-between h-16">
                <!-- User Greeting & Avatar (Cliquer sur l'accueil ou le profil) -->
                <div class="flex items-center gap-3 min-w-0">
                    <a href="{{ route('jeune.profile') }}" class="relative group flex-shrink-0" title="Voir mon profil">
                        <div class="w-10 h-10 rounded-full overflow-hidden ring-2 ring-indigo-500/30 flex items-center justify-center bg-gradient-to-br from-indigo-500 to-purple-600 shadow-sm">
                            @if(auth()->user()->avatar_url)
                                <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-full h-full object-cover">
                            @else
                                <span class="text-sm font-bold text-white">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                            @endif
                        </div>
                    </a>
                    <a href="{{ route('jeune.dashboard') }}" class="leading-tight min-w-0" title="Retour au tableau de bord">
                        <p class="text-[11px] font-medium text-slate-400">Bonjour 👋</p>
                        <h2 class="text-sm font-bold text-slate-800 tracking-tight truncate max-w-[130px]">{{ explode(' ', auth()->user()->name)[0] }}</h2>
                    </a>
                </div>

                <!-- Right Actions: Balance, Notification Bell & Menu Drawer -->
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <!-- Credits Pill -->
                    <a href="{{ route('jeune.wallet.index') }}"
                       class="flex items-center gap-1 px-2.5 py-1.5 bg-gradient-to-r from-indigo-50 to-purple-50 border border-indigo-100/80 rounded-full text-indigo-700 font-bold text-xs shadow-xs hover:border-indigo-200 active:scale-95 transition"
                       title="Portefeuille">
                        <span class="text-xs">💎</span>
                        <span>{{ auth()->user()->credits_balance ?? 0 }}</span>
                    </a>

                    <!-- Notification Bell -->
                    @php
                        $mobileUnread = \App\Models\Message::whereHas('mentorship', fn($q) =>
                            $q->where('mentee_id', auth()->id())->where('status','accepted'))
                            ->where('sender_id', '!=', auth()->id())->whereNull('read_at')->count();
                    @endphp
                    <a href="{{ route('jeune.messages.index') }}"
                       class="relative p-2 rounded-xl text-slate-600 hover:bg-slate-100 active:scale-95 transition"
                       title="Messages et notifications">
                        <span class="sr-only">Messages et notifications</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        @if($mobileUnread > 0)
                            <span class="absolute top-1.5 right-1.5 flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-purple-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-purple-600"></span>
                            </span>
                        @endif
                    </a>

                    <!-- Bouton Menu Drawer -->
                    <button type="button"
                            @click="mobileMenuOpen = true"
                            class="p-2 rounded-xl text-slate-700 hover:bg-slate-100 active:scale-95 transition focus:outline-none"
                            aria-label="Menu principal">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Sous-barre contextuelle mobile (Onglets rapides) -->
            @if(request()->routeIs('jeune.mentors', 'jeune.mentorship.*', 'jeune.sessions.*'))
                <div class="md:hidden border-t border-slate-100 bg-white/95 backdrop-blur-md -mx-4 px-4 py-2 flex items-center gap-1.5 overflow-x-auto no-scrollbar select-none text-xs">
                    <a href="{{ route('jeune.mentors') }}"
                       class="flex-shrink-0 px-3 py-1.5 rounded-full font-semibold transition active:scale-95 {{ request()->routeIs('jeune.mentors') ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        🔍 Trouver un mentor
                    </a>
                    <a href="{{ route('jeune.mentorship.index') }}"
                       class="flex-shrink-0 px-3 py-1.5 rounded-full font-semibold transition active:scale-95 {{ request()->routeIs('jeune.mentorship.index') ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        👥 Mes Mentors
                    </a>
                    <a href="{{ route('jeune.sessions.index') }}"
                       class="flex-shrink-0 px-3 py-1.5 rounded-full font-semibold transition active:scale-95 {{ request()->routeIs('jeune.sessions.index', 'jeune.sessions.show') ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        ⏱️ Mes Séances
                    </a>
                    <a href="{{ route('jeune.sessions.calendar') }}"
                       class="flex-shrink-0 px-3 py-1.5 rounded-full font-semibold transition active:scale-95 {{ request()->routeIs('jeune.sessions.calendar') ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        📅 Calendrier
                    </a>
                </div>
            @elseif(request()->routeIs('jeune.outils*', 'jeune.documents*', 'jeune.resources*'))
                <div class="md:hidden border-t border-slate-100 bg-white/95 backdrop-blur-md -mx-4 px-4 py-2 flex items-center gap-1.5 overflow-x-auto no-scrollbar select-none text-xs">
                    <a href="{{ route('jeune.outils') }}"
                       class="flex-shrink-0 px-3 py-1.5 rounded-full font-semibold transition active:scale-95 {{ request()->routeIs('jeune.outils*') ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        📄 Analyseur CV
                    </a>
                    <a href="{{ route('jeune.documents') }}"
                       class="flex-shrink-0 px-3 py-1.5 rounded-full font-semibold transition active:scale-95 {{ request()->routeIs('jeune.documents*') ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        📁 Mes Documents
                    </a>
                    <a href="{{ route('jeune.resources.index') }}"
                       class="flex-shrink-0 px-3 py-1.5 rounded-full font-semibold transition active:scale-95 {{ request()->routeIs('jeune.resources*') ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        📚 Ressources
                    </a>
                </div>
            @endif

            <!-- Desktop Navigation Bar -->
            <div class="hidden md:flex justify-between h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="{{ route('jeune.dashboard') }}" class="flex items-center gap-2">
                        @if($displayOrg && $displayOrg->logo_url)
                            <img src="{{ $displayOrg->logo_url }}" alt="{{ $displayOrg->name }}" class="h-10 rounded-xl object-contain">
                        @else
                            <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-purple-600 rounded-xl flex items-center justify-center">
                                <span class="text-xl font-bold text-white">{{ substr($displayOrg ? $displayOrg->name : 'Brillio', 0, 1) }}</span>
                            </div>
                            <span class="text-xl font-bold gradient-text hidden sm:block">{{ $displayOrg ? $displayOrg->name : 'Brillio' }}</span>
                        @endif
                    </a>
                </div>

                <!-- Navigation Items (Desktop) -->
                <div class="flex items-center gap-2">
                    <!-- Menus directs -->
                    <a href="{{ route('jeune.dashboard') }}"
                        class="nav-item px-4 py-2 rounded-xl text-sm font-medium {{ request()->routeIs('jeune.dashboard') ? 'active' : 'text-gray-600 hover:bg-gray-100' }}">
                        Accueil
                    </a>

                    <!-- Autres menus -->
                    <a href="{{ route('jeune.personality') }}"
                        class="nav-item px-4 py-2 rounded-xl text-sm font-medium {{ request()->routeIs('jeune.personality') ? 'active' : 'text-gray-600 hover:bg-gray-100' }}">
                        Personnalité
                    </a>
                    <a href="{{ route('jeune.chat') }}"
                        class="nav-item px-4 py-2 rounded-xl text-sm font-medium {{ request()->routeIs('jeune.chat') ? 'active' : 'text-gray-600 hover:bg-gray-100' }}">
                        Orientation
                    </a>
                    <a href="{{ route('jeune.opportunities') }}"
                        class="nav-item px-4 py-2 rounded-xl text-sm font-medium flex items-center gap-1.5 {{ request()->routeIs('jeune.opportunities*') ? 'active' : 'text-gray-600 hover:bg-gray-100' }}">
                        <span>Opportunités</span>
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500 text-white animate-pulse shadow-xs">Soon</span>
                    </a>

                    <a href="{{ route('jeune.outils') }}"
                        class="nav-item px-4 py-2 rounded-xl text-sm font-medium flex items-center gap-1.5 {{ request()->routeIs('jeune.outils*', 'jeune.documents*', 'jeune.resources*') ? 'active' : 'text-gray-600 hover:bg-gray-100' }}">
                        <span>Outils</span>
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-primary-600 text-white animate-pulse shadow-xs">New</span>
                    </a>

                    <a href="{{ route('jeune.messages.index') }}"
                        class="nav-item px-4 py-2 rounded-xl text-sm font-medium flex items-center gap-2 {{ request()->routeIs('jeune.messages.*') ? 'active' : 'text-gray-600 hover:bg-gray-100' }}">
                        Messages
                        @php
                        $navUnread = \App\Models\Message::whereHas('mentorship', fn($q) =>
                        $q->where('mentee_id', auth()->id())->where('status','accepted'))
                        ->where('sender_id', '!=', auth()->id())->whereNull('read_at')->count();
                        @endphp
                        @if($navUnread > 0)
                        <span class="bg-indigo-600 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full">{{
                            $navUnread }}</span>
                        @endif
                    </a>

                    <!-- Dropdown Mentorat (avec pont de survol) -->
                    <div class="relative"
                         x-data="{
                             open: false,
                             closeTimer: null,
                             show() {
                                 clearTimeout(this.closeTimer);
                                 this.open = true;
                             },
                             hide() {
                                 this.closeTimer = setTimeout(() => {
                                     this.open = false;
                                 }, 300);
                             }
                         }"
                         @mouseenter="show()"
                         @mouseleave="hide()">
                        <button @click="open = !open"
                            class="nav-item px-4 py-2 rounded-xl text-sm font-medium flex items-center gap-1 {{ request()->routeIs('jeune.mentors', 'jeune.mentorship.*', 'jeune.sessions.*') ? 'active' : 'text-gray-600 hover:bg-gray-100' }}">
                            Mentorat
                            <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" x-cloak
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 translate-y-1"
                            @mouseenter="show()"
                            @mouseleave="hide()"
                            class="absolute left-0 top-full pt-1.5 w-56 z-50">
                            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-1.5 ring-1 ring-black/5">
                                <a href="{{ route('jeune.mentors') }}"
                                    class="block px-3.5 py-2 text-sm text-gray-700 hover:bg-primary-50 hover:text-primary-700 rounded-xl transition font-medium">
                                    Trouver un mentor
                                </a>
                                <a href="{{ route('jeune.mentorship.index') }}"
                                    class="block px-3.5 py-2 text-sm text-gray-700 hover:bg-primary-50 hover:text-primary-700 rounded-xl transition">
                                    Mes Mentors
                                </a>
                                <a href="{{ route('jeune.sessions.calendar') }}"
                                    class="block px-3.5 py-2 text-sm text-gray-700 hover:bg-primary-50 hover:text-primary-700 rounded-xl transition">
                                    Mon Calendrier
                                </a>
                                <a href="{{ route('jeune.sessions.index') }}"
                                    class="block px-3.5 py-2 text-sm text-gray-700 hover:bg-primary-50 hover:text-primary-700 rounded-xl transition">
                                    Mes Séances
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Profile Dropdown (Desktop) -->
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
                                <span class="text-sm font-semibold text-white">{{
                                    strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                                @endif
                            </div>
                            <span class="text-sm font-medium text-gray-700">{{ auth()->user()->name }}</span>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
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
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center justify-between">
                                <span>Mon Portefeuille ({{ auth()->user()->credits_balance }})</span>
                                <span
                                    class="animate-pulse bg-purple-100 text-purple-800 text-[10px] font-bold px-1.5 py-0.5 rounded-full">NEW</span>
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
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 pb-32 md:pb-8">
        @yield('content')
    </main>

    <!-- Footer (masqué sur mobile & app pour ne pas chevaucher la barre de navigation) -->
    <footer class="hidden md:block bg-white border-t border-gray-100 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                <p class="text-sm text-gray-500">&copy; {{ date('Y') }} {{ $displayOrg ? $displayOrg->name : 'Brillio' }}. Tous droits réservés.</p>
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
        class="hidden fixed bottom-16 md:bottom-0 left-0 right-0 bg-white border-t-2 border-primary-500 shadow-2xl z-50 p-4 sm:p-6">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-start gap-3 flex-1">
                <svg class="w-6 h-6 text-primary-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24" aria-hidden="true" focusable="false">
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
            <button id="acceptCookiesBtn"
                class="px-6 py-2 bg-primary-600 text-white font-semibold rounded-xl hover:bg-primary-700 transition whitespace-nowrap">
                Accepter
            </button>
        </div>
    </div>

    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        // Gestion du consentement aux cookies
        document.addEventListener('DOMContentLoaded', function() {
            const btn = document.getElementById('acceptCookiesBtn');
            if (btn) {
                btn.addEventListener('click', acceptCookies);
            }
        });
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
    <script nonce="{{ request()->attributes->get('csp_nonce') }}" defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.14.7/dist/cdn.min.js" integrity="sha384-NArNwzWsUSF+kY2lgW4YriEkjLqi+J+za6HrENUn/3nZqkBnWbxV22kCJEK5Uu6n" crossorigin="anonymous"></script>
    <script nonce="{{ request()->attributes->get('csp_nonce') }}" defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.7/dist/cdn.min.js" integrity="sha384-cixRWCxxaN2ZlgSKys0xeW++971nkjz01WMhvEVsYDm6hlVuq/vm14WM+CLfIkBB" crossorigin="anonymous"></script>

    <!-- Mobile Navigation Drawer (Slide-over) -->
    <dialog x-show="mobileMenuOpen"
            x-cloak
            class="relative z-[90] md:hidden p-0 m-0 border-0 bg-transparent w-full h-full max-w-full max-h-full"
            aria-modal="true"
            aria-label="Menu principal">
        <!-- Backdrop -->
        <button type="button"
                x-show="mobileMenuOpen"
                x-transition:enter="ease-in-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in-out duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 w-full h-full border-0 p-0 m-0 bg-slate-900/50 backdrop-blur-xs transition-opacity cursor-default focus:outline-none"
                @click="mobileMenuOpen = false"
                aria-label="Fermer le menu"></button>

        <div class="fixed inset-0 overflow-hidden">
            <div class="absolute inset-0 overflow-hidden">
                <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                    <div x-show="mobileMenuOpen"
                         x-transition:enter="transform transition ease-in-out duration-300"
                         x-transition:enter-start="translate-x-full"
                         x-transition:enter-end="translate-x-0"
                         x-transition:leave="transform transition ease-in-out duration-200"
                         x-transition:leave-start="translate-x-0"
                         x-transition:leave-end="translate-x-full"
                         class="pointer-events-auto w-screen max-w-xs bg-white shadow-2xl flex flex-col pt-[env(safe-area-inset-top)] pb-[calc(env(safe-area-inset-bottom)+1rem)]">

                        <!-- Drawer Header -->
                        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/70">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full overflow-hidden ring-2 ring-indigo-500/20 bg-indigo-600 flex items-center justify-center text-white font-bold text-sm">
                                    @if(auth()->user()->avatar_url)
                                        <img src="{{ auth()->user()->avatar_url }}" alt="" class="w-full h-full object-cover">
                                    @else
                                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-slate-800 truncate">{{ auth()->user()->name }}</p>
                                    <p class="text-[11px] text-slate-500 truncate">{{ auth()->user()->email }}</p>
                                </div>
                            </div>
                            <button type="button"
                                    @click="mobileMenuOpen = false"
                                    class="p-2 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition active:scale-95"
                                    aria-label="Fermer le menu">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <!-- Drawer Menu Links (Scrollable) -->
                        <div class="flex-1 overflow-y-auto px-4 py-4 space-y-6 text-sm">
                            <!-- Section: Tableau de bord -->
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2 px-3">Général</p>
                                <a href="{{ route('jeune.dashboard') }}"
                                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('jeune.dashboard') ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                                    <span class="text-base">🏠</span>
                                    <span>Accueil (Tableau de bord)</span>
                                </a>
                                <a href="{{ route('jeune.chat') }}"
                                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('jeune.chat') ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                                    <span class="text-base">⚡</span>
                                    <span>Orientation IA</span>
                                </a>
                                <a href="{{ route('jeune.personality') }}"
                                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('jeune.personality') ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                                    <span class="text-base">🧠</span>
                                    <span>Test de Personnalité</span>
                                </a>
                            </div>

                            <!-- Section: Mentorat -->
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2 px-3">Mentorat</p>
                                <div class="space-y-1">
                                    <a href="{{ route('jeune.mentors') }}"
                                       class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('jeune.mentors') ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                                        <span class="text-base">🔍</span>
                                        <span>Trouver un mentor</span>
                                    </a>
                                    <a href="{{ route('jeune.mentorship.index') }}"
                                       class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('jeune.mentorship.index') ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                                        <span class="text-base">👥</span>
                                        <span>Mes Mentors</span>
                                    </a>
                                    <a href="{{ route('jeune.sessions.index') }}"
                                       class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('jeune.sessions.index', 'jeune.sessions.show') ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                                        <span class="text-base">⏱️</span>
                                        <span>Mes Séances</span>
                                    </a>
                                    <a href="{{ route('jeune.sessions.calendar') }}"
                                       class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('jeune.sessions.calendar') ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                                        <span class="text-base">📅</span>
                                        <span>Mon Calendrier</span>
                                    </a>
                                </div>
                            </div>

                            <!-- Section: Outils & Carrière -->
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2 px-3">Outils & Carrière</p>
                                <div class="space-y-1">
                                    <a href="{{ route('jeune.outils') }}"
                                       class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('jeune.outils*') ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                                        <span class="text-base">📄</span>
                                        <span>Analyseur de CV</span>
                                    </a>
                                    <a href="{{ route('jeune.documents') }}"
                                       class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('jeune.documents*') ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                                        <span class="text-base">📁</span>
                                        <span>Mes Documents</span>
                                    </a>
                                    <a href="{{ route('jeune.resources.index') }}"
                                       class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('jeune.resources*') ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                                        <span class="text-base">📚</span>
                                        <span>Ressources & Guides</span>
                                    </a>
                                    <a href="{{ route('jeune.opportunities') }}"
                                       class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('jeune.opportunities*') ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                                        <span class="text-base">🚀</span>
                                        <span>Opportunités</span>
                                    </a>
                                </div>
                            </div>

                            <!-- Section: Compte & Portefeuille -->
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2 px-3">Mon Compte</p>
                                <div class="space-y-1">
                                    <a href="{{ route('jeune.profile') }}"
                                       class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('jeune.profile') ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                                        <span class="text-base">👤</span>
                                        <span>Mon Profil & CV</span>
                                    </a>
                                    <a href="{{ route('jeune.wallet.index') }}"
                                       class="flex items-center justify-between px-3 py-2 rounded-xl transition {{ request()->routeIs('jeune.wallet.*') ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                                        <div class="flex items-center gap-3">
                                             <span class="text-base">💎</span>
                                            <span>Portefeuille</span>
                                        </div>
                                        <span class="text-xs font-bold text-indigo-600 px-2 py-0.5 rounded-full bg-indigo-50">{{ auth()->user()->credits_balance ?? 0 }} crédits</span>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Drawer Footer (Logout) -->
                        <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                        class="w-full flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-xl transition active:scale-95">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    <span>Déconnexion</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </dialog>

    @stack('scripts')
    @include('partials.toast')
    @include('partials.jeune-bottom-nav')
    @include('partials.pwa-install-modals')
</body>

</html>