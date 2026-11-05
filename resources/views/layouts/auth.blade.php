<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- SEO Meta Tags --}}
    <x-seo-meta page="login" />

    @include('partials.analytics')

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    @php
    $org = $current_organization ?? null;
    $isBranded = $org && $org->isPro();
    $primaryColor = $isBranded && $org->primary_color ? $org->primary_color : '#6366f1';
    $secondaryColor = $isBranded && $org->accent_color ? $org->accent_color : '#d946ef';
    @endphp

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
    <!-- Tailwind CSS -->
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
                            600: '#5145e5',
                            700: '#4536ca',
                            800: '#3a2fa3',
                            900: '#332c81',
                        },
                        accent: {
                            50: '#fdf4ff',
                            100: '#fae8ff',
                            200: '#f5d0fe',
                            300: '#f0abfc',
                            400: '#e879f9',
                            500: '{{ $secondaryColor }}',
                            600: '#c026d3',
                            700: '#a21caf',
                            800: '#86198f',
                            900: '#701a75',
                        },
                        organization: {
                            50: '{{ $isBranded ? $primaryColor . "10" : "#fff1f2" }}',
                            100: '{{ $isBranded ? $primaryColor . "20" : "#ffe4e6" }}',
                            200: '{{ $isBranded ? $primaryColor . "30" : "#fecdd3" }}',
                            300: '{{ $secondaryColor }}',
                            400: '{{ $secondaryColor }}',
                            500: '{{ $primaryColor }}',
                            600: '{{ $secondaryColor }}',
                            700: '{{ $secondaryColor }}',
                            800: '{{ $isBranded ? $primaryColor : "#9f1239" }}',
                            900: '{{ $isBranded ? $primaryColor : "#881337" }}',
                        },
                        branding: {
                            success: {
                                bg: '{{ $isBranded ? $primaryColor . "10" : "#f0fdf4" }}',
                                border: '{{ $isBranded ? $primaryColor . "30" : "#bbf7d0" }}',
                                text: '{{ $isBranded ? $primaryColor : "#166534" }}',
                            },
                            error: {
                                bg: '{{ $isBranded ? "#fef2f2" : "#fef2f2" }}',
                                border: '{{ $isBranded ? "#fecaca" : "#fecaca" }}',
                                text: '{{ $isBranded ? "#991b1b" : "#991b1b" }}',
                            }
                        }
                    }
                }
            }
        }
    </script>
    @vite(['resources/js/app.js'])

    <!-- Alpine.js pour l'interactivité -->
    <script nonce="{{ request()->attributes->get('csp_nonce') }}" defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.14.7/dist/cdn.min.js" integrity="sha384-NArNwzWsUSF+kY2lgW4YriEkjLqi+J+za6HrENUn/3nZqkBnWbxV22kCJEK5Uu6n" crossorigin="anonymous"></script>
    <script nonce="{{ request()->attributes->get('csp_nonce') }}" defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.7/dist/cdn.min.js" integrity="sha384-cixRWCxxaN2ZlgSKys0xeW++971nkjz01WMhvEVsYDm6hlVuq/vm14WM+CLfIkBB" crossorigin="anonymous"></script>

    <style>
        [x-cloak] { display: none !important; }
        .auth-bg {
            background-color: #F8FAFC;
            background-image: radial-gradient(rgba(148, 163, 184, 0.18) 1px, transparent 1px);
            background-size: 24px 24px;
        }

        .auth-card {
            background: #ffffff;
            border: 1px solid #E2E8F0;
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05), 0 8px 10px -6px rgba(15, 23, 42, 0.03);
        }

        .btn-oauth {
            transition: all 0.2s ease;
        }

        .btn-oauth:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body class="font-sans antialiased min-h-screen auth-bg text-slate-900">

    <div class="relative z-10 min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <!-- Logo -->
        <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
            @php
                $siteName = $org ? $org->name : 'Brillio';
            @endphp
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 group">
                @if($org && $org->logo_url)
                    <img src="{{ $org->logo_url }}" alt="{{ $siteName }}" class="h-11 w-auto object-contain rounded-xl shadow-xs bg-white p-1 ring-1 ring-slate-200">
                    <span class="text-2xl font-extrabold tracking-tight text-slate-900">{{ $siteName }}</span>
                @else
                    <img src="{{ asset('images/logo/brillio-logo.png') }}" alt="Brillio" width="154" height="60" class="h-12 w-auto object-contain group-hover:scale-105 transition-transform">
                @endif
            </a>
            <h2 class="mt-5 text-2xl font-extrabold tracking-tight text-slate-900">
                @yield('heading')
            </h2>
            <p class="mt-1.5 text-sm text-slate-500 font-normal">
                @yield('subheading')
            </p>
        </div>

        <!-- Card -->
        <div class="mt-8 sm:mx-auto sm:w-full @yield('card_width', 'sm:max-w-md')">
            <div class="auth-card py-8 px-6 rounded-3xl sm:px-10">
                @if(session('error'))
                <div class="mb-4 p-4 rounded-xl bg-branding-error-bg border border-branding-error-border">
                    <p class="text-sm font-medium text-branding-error-text">{{ session('error') }}</p>
                </div>
                @endif

                @if(session('success'))
                <div class="mb-4 p-4 rounded-xl bg-branding-success-bg border border-branding-success-border">
                    <p class="text-sm font-medium text-branding-success-text">{{ session('success') }}</p>
                </div>
                @endif

                @yield('content')
            </div>

            @hasSection('footer')
            <div class="mt-6 text-center">
                @yield('footer')
            </div>
            @endif
        </div>

        <!-- Back to home -->
        <div class="mt-8 text-center">
            <a href="{{ route('home') }}" class="text-slate-500 hover:text-slate-900 text-sm font-medium inline-flex items-center gap-2 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Retour à l'accueil
            </a>
        </div>
    </div>

    <!-- Supabase OAuth Redirect Fix -->
    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        (function () {
            if (window.location.hash && window.location.hash.includes('access_token=')) {
                const pendingProvider = "{{ session('oauth_provider', 'google') }}";
                const pendingType = "{{ session('oauth_type', 'jeune') }}";

                console.log("[Auth] Hash detected on auth page. Provider:", pendingProvider, "Type:", pendingType);

                if (pendingProvider && pendingType === 'jeune') {
                    window.location.href = "/jeune/oauth/" + pendingProvider + "/callback" + window.location.hash;
                } else if (pendingType === 'mentor') {
                    window.location.href = "/mentor/linkedin/callback" + window.location.hash;
                }
            }
        })();
    </script>
</body>

</html>