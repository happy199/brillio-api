<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- SEO Meta Tags --}}
    <x-seo-meta page="login" />

    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-PPX01GY0R9"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }
        gtag('js', new Date());
        gtag('config', 'G-PPX01GY0R9');
    </script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    @php
    $org = $current_organization ?? null;
    $isBranded = $org && $org->isEnterprise();
    $primaryColor = $isBranded && $org->primary_color ? $org->primary_color : '#6366f1';
    $secondaryColor = $isBranded && $org->accent_color ? $org->accent_color : '#d946ef';
    @endphp

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

    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #d946ef 100%);
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
        }

        .btn-oauth {
            transition: all 0.2s ease;
        }

        .btn-oauth:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .floating-shapes {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
        }

        .floating-shapes .shape {
            position: absolute;
            border-radius: 50%;
            opacity: 0.1;
            animation: float 20s infinite ease-in-out;
        }

        .floating-shapes .shape:nth-child(1) {
            width: 400px;
            height: 400px;
            background: #fff;
            top: -100px;
            left: -100px;
            animation-delay: 0s;
        }

        .floating-shapes .shape:nth-child(2) {
            width: 300px;
            height: 300px;
            background: #fff;
            bottom: -50px;
            right: -50px;
            animation-delay: -5s;
        }

        .floating-shapes .shape:nth-child(3) {
            width: 200px;
            height: 200px;
            background: #fff;
            top: 50%;
            left: 50%;
            animation-delay: -10s;
        }

        @keyframes float {

            0%,
            100% {
                transform: translate(0, 0) rotate(0deg);
            }

            25% {
                transform: translate(20px, -20px) rotate(5deg);
            }

            50% {
                transform: translate(-10px, 20px) rotate(-5deg);
            }

            75% {
                transform: translate(-20px, -10px) rotate(3deg);
            }
        }
    </style>
</head>

<body class="font-sans antialiased min-h-screen gradient-bg">
    <!-- Floating shapes -->
    <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <div class="relative z-10 min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <!-- Logo -->
        <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2">
                <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center shadow-lg">
                    <span class="text-2xl font-bold text-primary-600">B</span>
                </div>
                <span class="text-3xl font-bold text-white">Brillio</span>
            </a>
            <h2 class="mt-6 text-2xl font-bold text-white">
                @yield('heading')
            </h2>
            <p class="mt-2 text-white/80">
                @yield('subheading')
            </p>
        </div>

        <!-- Card -->
        <div class="mt-8 sm:mx-auto sm:w-full @yield('card_width', 'sm:max-w-md')">
            <div class="glass-card py-8 px-6 shadow-2xl rounded-2xl sm:px-10">
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
            <a href="{{ route('home') }}" class="text-white/80 hover:text-white text-sm inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Retour a l'accueil
            </a>
        </div>
    </div>
</body>

</html>