<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Espace Organisation') - Brillio</title>

    <!-- Favicons -->
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#10b981">

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
                        organization: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            200: '#bbf7d0',
                            300: '#86efac',
                            400: '#4ade80',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                            800: '#065f46',
                            900: '#064e3b',
                        }
                    }
                }
            }
        }
    </script>

    @stack('styles')
</head>

<body class="bg-gray-50 font-sans">

    <!-- Top Navigation Bar -->
    <nav class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('organization.dashboard') }}" class="flex items-center space-x-3">
                        <div
                            class="w-8 h-8 bg-gradient-to-br from-organization-400 to-organization-600 rounded-lg flex items-center justify-center">
                            <span class="text-white font-bold text-sm">B</span>
                        </div>
                        <span class="text-xl font-bold text-gray-900">Brillio<span
                                class="text-organization-500">Partner</span></span>
                    </a>
                </div>

                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-600">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('organization.logout') }}" class="inline">
                        @csrf
                        <button type="submit"
                            class="text-sm text-organization-600 hover:text-organization-700 font-medium">
                            Déconnexion
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if(session('success'))
        <div class="mb-6 bg-organization-50 border border-organization-200 text-organization-800 px-4 py-3 rounded-lg">
            {{ session('success') }}
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
</body>

</html>