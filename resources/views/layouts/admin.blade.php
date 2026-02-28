<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Dashboard') - Brillio Admin</title>

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
    <meta name="theme-color" content="#6366f1">

    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Alpine.js pour interactivité -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Chart.js pour les graphiques -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    @stack('styles')
</head>

<body class="bg-gray-100" x-data="{ sidebarOpen: false }">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <aside class="bg-indigo-700 text-white w-64 min-h-screen flex-shrink-0 hidden md:block">
            <div class="p-4">
                <h1 class="text-2xl font-bold">Brillio</h1>
                <p class="text-indigo-200 text-sm">Administration</p>
            </div>

            <nav class="mt-4">
                <a href="{{ auth()->user()->isCoach() ? route('coach.dashboard') : route('admin.dashboard') }}"
                    class="block px-4 py-3 hover:bg-indigo-600 {{ request()->routeIs('admin.dashboard') || request()->routeIs('coach.dashboard') ? 'bg-indigo-800' : '' }}">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                            </path>
                        </svg>
                        Dashboard
                    </span>
                </a>

                @if(!auth()->user()->isCoach())
                <a href="{{ route('admin.users.index') }}"
                    class="block px-4 py-3 hover:bg-indigo-600 {{ request()->routeIs('admin.users.*') ? 'bg-indigo-800' : '' }}">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                            </path>
                        </svg>
                        Utilisateurs
                    </span>
                </a>
                @endif

                @if(!auth()->user()->isCoach())

                <a href="{{ route('admin.coaches.index') }}"
                    class="block px-4 py-3 hover:bg-indigo-600 {{ request()->routeIs('admin.coaches.*') ? 'bg-indigo-800' : '' }}">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                            </path>
                        </svg>
                        Coachs
                    </span>
                </a>

                <a href="{{ route('admin.organizations.index') }}"
                    class="block px-4 py-3 hover:bg-indigo-600 {{ request()->routeIs('admin.organizations.*') ? 'bg-indigo-800' : '' }}">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                            </path>
                        </svg>
                        Organisations
                    </span>
                </a>
                @endif

                <!-- Mentorat Dropdown -->
                <div
                    x-data="{ open: {{ request()->routeIs('admin.mentors.*') || request()->routeIs('admin.mentorship.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open"
                        class="w-full flex justify-between items-center px-4 py-3 hover:bg-indigo-600 {{ request()->routeIs('admin.mentors.*') || request()->routeIs('admin.mentorship.*') ? 'bg-indigo-800' : '' }}">
                        <span class="flex items-center">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                </path>
                            </svg>
                            Mentorats
                        </span>
                        <svg class="w-4 h-4 transition-transform duration-200" :class="{'rotate-180': open}" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                            </path>
                        </svg>
                    </button>
                    <div x-show="open" x-cloak class="bg-indigo-900">
                        <a href="{{ route('admin.mentors.index') }}"
                            class="block px-4 py-2 text-sm hover:bg-indigo-800 pl-12 text-indigo-200 hover:text-white {{ request()->routeIs('admin.mentors.*') ? 'text-white font-bold' : '' }}">
                            Annuaire des Mentors
                        </a>
                        @if(!auth()->user()->isCoach())
                        <a href="{{ route('admin.mentorship.requests') }}"
                            class="block px-4 py-2 text-sm hover:bg-indigo-800 pl-12 text-indigo-200 hover:text-white {{ request()->routeIs('admin.mentorship.requests') ? 'text-white font-bold' : '' }}">
                            Activités de Mentorat
                        </a>
                        <a href="{{ route('admin.mentorship.sessions') }}"
                            class="block px-4 py-2 text-sm hover:bg-indigo-800 pl-12 text-indigo-200 hover:text-white {{ request()->routeIs('admin.mentorship.sessions') ? 'text-white font-bold' : '' }}">
                            Séances de Mentorat
                        </a>
                        @endif
                    </div>
                </div>

                <a href="{{ route('admin.resources.index') }}"
                    class="block px-4 py-3 hover:bg-indigo-600 {{ request()->routeIs('admin.resources.*') ? 'bg-indigo-800' : '' }}">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        Ressources
                    </span>
                </a>

                @if(!auth()->user()->isCoach())

                <a href="{{ route('admin.accounting.index') }}"
                    class="block px-4 py-3 hover:bg-indigo-600 {{ request()->routeIs('admin.accounting.*') && !request()->routeIs('admin.payouts.*') ? 'bg-indigo-800' : '' }}">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                            </path>
                        </svg>
                        Comptabilité
                    </span>
                </a>

                @if(request()->routeIs('admin.accounting.*') || request()->routeIs('admin.payouts.*'))
                <div class="bg-indigo-900 pb-2">
                    <a href="{{ route('admin.payouts.index') }}"
                        class="block px-4 py-2 text-sm hover:bg-indigo-800 pl-12 text-indigo-200 hover:text-white {{ request()->routeIs('admin.payouts.*') ? 'text-white font-bold' : '' }}">
                        Retraits Mentors
                    </a>
                    <a href="{{ route('admin.accounting.history') }}"
                        class="block px-4 py-2 text-sm hover:bg-indigo-800 pl-12 text-indigo-200 hover:text-white {{ request()->routeIs('admin.accounting.history') ? 'text-white font-bold' : '' }}">
                        Historique des flux
                    </a>
                </div>
                @endif

                <a href="{{ route('admin.monetization.index') }}"
                    class="block px-4 py-3 hover:bg-indigo-600 {{ request()->routeIs('admin.monetization.*') ? 'bg-indigo-800' : '' }}">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Monétisation
                    </span>
                </a>

                <a href="{{ route('admin.subscription-plans.index') }}"
                    class="block px-4 py-3 hover:bg-indigo-600 {{ request()->routeIs('admin.subscription-plans.*') ? 'bg-indigo-800' : '' }}">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V5a2 2 0 012-2h4a2 2 0 012 2v2M7 7h10a2 2 0 012 2v10a2 2 0 01-2 2H7a2 2 0 01-2-2V9a2 2 0 012-2z" />
                        </svg>
                        Offres d'Abonnement
                    </span>
                </a>

                <a href="{{ route('admin.credit-packs.index') }}"
                    class="block px-4 py-3 hover:bg-indigo-600 {{ request()->routeIs('admin.credit-packs.*') ? 'bg-indigo-800' : '' }}">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        Packs de Crédits
                    </span>
                </a>

                @php
                $pendingSpecializations = \App\Models\Specialization::where('status', 'pending')->count();
                @endphp
                <a href="{{ route('admin.specializations.index') }}"
                    class="block px-4 py-3 hover:bg-indigo-600 {{ request()->routeIs('admin.specializations.*') ? 'bg-indigo-800' : '' }}">
                    <span class="flex items-center justify-between">
                        <span class="flex items-center">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z">
                                </path>
                            </svg>
                            Domaines d'expertise
                        </span>
                        @if($pendingSpecializations > 0)
                        <span class="bg-yellow-500 text-white text-xs font-bold px-2 py-1 rounded-full">
                            {{ $pendingSpecializations }}
                        </span>
                        @endif
                    </span>
                </a>

                <a href="{{ route('admin.analytics.index') }}"
                    class="block px-4 py-3 hover:bg-indigo-600 {{ request()->routeIs('admin.analytics.*') ? 'bg-indigo-800' : '' }}">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                            </path>
                        </svg>
                        Analytiques
                    </span>
                </a>

                @php
                $newMessages = \App\Models\ContactMessage::where('status', 'new')->count();
                @endphp
                <a href="{{ route('admin.contact-messages.index') }}"
                    class="block px-4 py-3 hover:bg-indigo-600 {{ request()->routeIs('admin.contact-messages.*') ? 'bg-indigo-800' : '' }}">
                    <span class="flex items-center justify-between">
                        <span class="flex items-center">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                </path>
                            </svg>
                            Messages de contact
                        </span>
                        @if($newMessages > 0)
                        <span class="bg-yellow-500 text-white text-xs font-bold px-2 py-1 rounded-full">
                            {{ $newMessages }}
                        </span>
                        @endif
                    </span>
                </a>

                <a href="{{ route('admin.newsletter.index') }}"
                    class="block px-4 py-3 hover:bg-indigo-600 {{ request()->routeIs('admin.newsletter.*') ? 'bg-indigo-800' : '' }}">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
                            </path>
                        </svg>
                        Newsletter
                    </span>
                </a>
                @endif

                @php
                $pendingSupportCount = \App\Models\ChatConversation::where('needs_human_support', true)
                ->where('human_support_active', false)
                ->count();
                @endphp
                <a href="{{ route('admin.chat.index') }}"
                    class="block px-4 py-3 hover:bg-indigo-600 {{ request()->routeIs('admin.chat.*') ? 'bg-indigo-800' : '' }}">
                    <span class="flex items-center justify-between">
                        <span class="flex items-center">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                                </path>
                            </svg>
                            Chat
                        </span>
                        @if($pendingSupportCount > 0)
                        <span class="bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-full animate-pulse">
                            {{ $pendingSupportCount }}
                        </span>
                        @endif
                    </span>
                </a>

                @if(!auth()->user()->isCoach())

                <a href="{{ route('admin.documents.index') }}"
                    class="block px-4 py-3 hover:bg-indigo-600 {{ request()->routeIs('admin.documents.*') ? 'bg-indigo-800' : '' }}">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                        Documents
                    </span>
                </a>
                @endif
            </nav>
        </aside>

        <!-- Main content -->
        <div class="flex-1 flex flex-col">
            <!-- Top bar -->
            <header class="bg-white shadow-sm">
                <div class="flex items-center justify-between px-6 py-4">
                    @hasSection('header_content')
                    @yield('header_content')
                    @else
                    <h2 class="text-xl font-semibold text-gray-800">@yield('header', 'Dashboard')</h2>
                    @endif

                    <div class="flex items-center space-x-4">
                        <span class="text-gray-600">{{ auth()->user()?->name }}</span>
                        <form action="{{ route('admin.logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-gray-600 hover:text-gray-800">
                                Déconnexion
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <!-- Page content -->
            <main class="flex-1 p-6">
                <!-- Flash messages -->
                @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
                @endif

                @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
                @endif

                @if(session('warning'))
                <div class="mb-4 bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
                    {{ session('warning') }}
                </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>

</html>