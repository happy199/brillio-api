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
    <meta name="theme-color" content="#f43f5e">

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
                            50: '#fff1f2',
                            100: '#ffe4e6',
                            200: '#fecdd3',
                            300: '#fda4af',
                            400: '#fb7185',
                            500: '#f43f5e',
                            600: '#e11d48',
                            700: '#be123c',
                            800: '#9f1239',
                            900: '#881337',
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

                    <!-- Navigation Links -->
                    <div class="hidden md:ml-10 md:flex md:space-x-8">
                        <a href="{{ route('organization.dashboard') }}"
                            class="{{ request()->routeIs('organization.dashboard') ? 'border-organization-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Tableau de bord
                        </a>
                        <a href="{{ route('organization.invitations.index') }}"
                            class="{{ request()->routeIs('organization.invitations.*') ? 'border-organization-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Invitations
                        </a>
                    </div>
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

    <script>
        function copyInvitationUrl() {
            const input = document.getElementById('invitation-url-input');
            input.select();
            input.setSelectionRange(0, 99999); // For mobile devices
            navigator.clipboard.writeText(input.value).then(function () {
                alert('Lien copié dans le presse-papiers !');
            }, function (err) {
                alert('Erreur lors de la copie');
            });
        }
    </script>
</body>

</html>