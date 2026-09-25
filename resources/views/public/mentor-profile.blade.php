@php
    $displayOrg = $current_organization ?? ($mentor->user->organization ?? $mentor->user->organizations->first());
@endphp
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $publicData['name'] }} - Mentor {{ $displayOrg ? $displayOrg->name : 'Brillio' }}</title>

    <!-- SEO Meta Tags -->
    <meta name="description" content="{{ Str::limit($publicData['bio'] ?? 'Mentor professionnel sur ' . ($displayOrg ? $displayOrg->name : 'Brillio'), 160) }}">
    <meta name="keywords" content="mentor, {{ $publicData['specialization'] }}, carrière, orientation professionnelle">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="profile">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="{{ $publicData['name'] }} - Mentor {{ $publicData['specialization'] }}">
    <meta property="og:description" content="{{ Str::limit($publicData['bio'] ?? 'Mentor professionnel', 200) }}">
    <meta property="og:image" content="{{ $publicData['picture'] ?? asset('LOGOBRILLIONOIR.png') }}">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:title" content="{{ $publicData['name'] }} - Mentor {{ $displayOrg ? $displayOrg->name : 'Brillio' }}">
    <meta property="twitter:description" content="{{ Str::limit($publicData['bio'] ?? 'Mentor professionnel', 200) }}">

    <!-- LinkedIn -->
    <meta property="og:site_name" content="{{ $displayOrg ? $displayOrg->name : 'Brillio' }}">

    <!-- Google tag (gtag.js) -->
    <script nonce="{{ request()->attributes->get('csp_nonce') }}" async src="https://www.googletagmanager.com/gtag/js?id=G-PPX01GY0R9"></script>
    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }
        gtag('js', new Date());
        gtag('config', 'G-PPX01GY0R9');
    </script>

    <script nonce="{{ request()->attributes->get('csp_nonce') }}" src="https://cdn.tailwindcss.com" nonce="{{ request()->attributes->get('csp_nonce') }}"></script>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-4 py-4 flex justify-between items-center">
            <a href="{{ route('auth.login') }}" class="flex items-center gap-2 hover:opacity-80 transition">
                <span class="text-2xl font-bold text-orange-600">{{ $displayOrg ? $displayOrg->name : 'Brillio' }}</span>
            </a>
            <a href="{{ route('home') }}"
                class="px-6 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition">
                Rejoindre la communauté
            </a>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-6xl mx-auto px-4 py-8">
        <!-- Profile Header -->
        <div class="bg-white rounded-2xl shadow-lg p-8 mb-6">
            <div class="flex flex-col md:flex-row gap-6 items-start">
                @php
                    $nameParts = explode(' ', $publicData['name']);
                    $initials = strtoupper(substr($nameParts[0] ?? 'A', 0, 1) . substr($nameParts[1] ?? '', 0, 1));
                    if (strlen($initials) < 2) {
                        $initials = strtoupper(substr($publicData['name'] ?? 'A', 0, 2));
                    }
                    $colors = ['from-blue-400 to-blue-600', 'from-orange-400 to-red-500', 'from-purple-400 to-purple-600', 'from-green-400 to-green-600'];
                    $color = $colors[crc32($mentor->user->id) % count($colors)];
                @endphp
                <div class="relative w-24 h-24 rounded-full bg-gradient-to-br {{ $color }} flex items-center justify-center text-white text-3xl font-bold flex-shrink-0 border-4 border-orange-100 overflow-hidden">
                    <span>{{ $initials }}</span>
                    @if($mentor->user->avatar_url)
                    <img src="{{ $mentor->user->avatar_url }}" alt="{{ $publicData['name'] }}" class="absolute inset-0 w-full h-full object-cover bg-white" onerror="this.style.display='none'">
                    @endif
                </div>

                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-2">
                        <h1 class="text-3xl font-bold text-gray-900">{{ $publicData['name'] }}</h1>
                        @if($mentor->is_validated)
                        <span
                            class="inline-flex items-center justify-center w-6 h-6 bg-green-100 text-green-600 rounded-full"
                            title="Profil vérifié">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd"></path>
                            </svg>
                        </span>
                        @endif
                    </div>
                    <p class="text-xl text-gray-600 mb-2">{{ $publicData['current_position'] }}{{
                        $publicData['current_company'] ? ' chez ' . $publicData['current_company'] : '' }}</p>

                    <div class="flex flex-wrap gap-3 mb-4">
                        @if($publicData['specialization'])
                        <span class="px-3 py-1 bg-orange-100 text-orange-700 rounded-full text-sm font-medium">
                            {{ $publicData['specialization'] }}
                        </span>
                        @endif
                        @if($publicData['years_of_experience'])
                        <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm font-medium">
                            {{ $publicData['years_of_experience'] }} ans d'expérience
                        </span>
                        @endif
                        @if($mentor->average_rating)
                        <span class="px-3 py-1 bg-amber-100 text-amber-800 rounded-full text-sm font-bold flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-amber-500 fill-current" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                            {{ number_format($mentor->average_rating, 1) }} / 5 ({{ $mentor->evaluations_count }} {{ Str::plural('avis', $mentor->evaluations_count) }})
                        </span>
                        @endif
                    </div>

                    <!-- Social Links -->
                    <div class="flex gap-3">
                        @if($publicData['linkedin_url'])
                        <a href="{{ $publicData['linkedin_url'] }}" target="_blank" rel="noopener"
                            class="text-blue-600 hover:text-blue-800">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" />
                            </svg>
                        </a>
                        @endif
                        @if($publicData['website_url'])
                        <a href="{{ $publicData['website_url'] }}" target="_blank" rel="noopener"
                            class="text-gray-600 hover:text-gray-800">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9">
                                </path>
                            </svg>
                        </a>
                        @endif

                        <!-- Share Button -->
                        <button onclick="shareProfile()"
                            class="ml-auto px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z">
                                </path>
                            </svg>
                            Partager
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bio -->
        @if($publicData['bio'])
        <div class="bg-white rounded-2xl shadow-lg p-8 mb-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">À propos</h2>
            <p class="text-gray-700 leading-relaxed whitespace-pre-line">{{ $publicData['bio'] }}</p>
        </div>
        @endif

        <!-- Personality Test Results (Only if available) -->
        @if(isset($publicData['personality']) && $publicData['personality'])
        <div class="bg-white rounded-2xl shadow-lg p-8 mb-6 border-l-4 border-purple-500">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Personnalité</h2>
            <div class="flex items-start gap-6">
                <div class="flex-shrink-0">
                    <div
                        class="w-16 h-16 bg-purple-100 rounded-2xl flex items-center justify-center text-purple-600 font-bold text-xl uppercase">
                        {{ $publicData['personality']['type'] }}
                    </div>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $publicData['personality']['label'] }}</h3>
                    <p class="text-gray-700 leading-relaxed">{{ $publicData['personality']['description'] }}</p>
                </div>
            </div>
        </div>
        @endif

        <!-- Advice -->
        @if($publicData['advice'])
        <div
            class="bg-gradient-to-r from-orange-50 to-pink-50 rounded-2xl shadow-lg p-8 mb-6 border-l-4 border-orange-500">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">💡 Conseil de carrière</h2>
            <p class="text-gray-700 leading-relaxed italic">{{ $publicData['advice'] }}</p>
        </div>
        @endif

        <!-- Public Reviews Section -->
        @php
            $mentorEvaluations = $mentor->user ? $mentor->user->receivedEvaluations()->with('mentee')->latest()->get() : collect();
        @endphp
        <div class="bg-white rounded-2xl shadow-lg p-8 mb-6">
            <div class="flex items-center justify-between border-b border-gray-100 pb-4 mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                        <svg class="w-6 h-6 text-amber-500 fill-current" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        Avis &amp; Évaluations des jeunes
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">Retours d'expérience des étudiants et jeunes accompagnés par ce mentor.</p>
                </div>

                @if($mentor->average_rating)
                    <div class="text-right">
                        <div class="text-3xl font-black text-gray-900 flex items-center gap-1 justify-end">
                            <span>{{ number_format($mentor->average_rating, 1) }}</span>
                            <span class="text-sm font-normal text-gray-500">/ 5</span>
                        </div>
                        <p class="text-xs text-amber-600 font-bold">{{ $mentor->evaluations_count }} {{ Str::plural('avis', $mentor->evaluations_count) }}</p>
                    </div>
                @endif
            </div>

            <div class="space-y-4">
                @forelse($mentorEvaluations as $eval)
                    <div class="bg-gray-50 rounded-xl p-5 border border-gray-100 space-y-2">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-orange-100 text-orange-700 font-bold text-sm flex items-center justify-center">
                                    {{ strtoupper(substr($eval->mentee->name ?? 'J', 0, 1)) }}
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-gray-900">{{ $eval->mentee->name ?? 'Étudiant' }}</h4>
                                    <p class="text-xs text-gray-500">{{ $eval->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-1 bg-amber-50 px-2.5 py-1 rounded-full border border-amber-200">
                                <div class="flex text-amber-400">
                                    @for($i = 1; $i <= 5; $i++)
                                        <svg class="w-4 h-4 {{ $i <= $eval->rating ? 'fill-current' : 'text-gray-300' }}" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    @endfor
                                </div>
                                <span class="text-xs font-bold text-amber-700 ml-1">{{ $eval->rating }}/5</span>
                            </div>
                        </div>
                        @if($eval->comment)
                            <p class="text-gray-700 text-sm italic whitespace-pre-wrap pl-2">« {{ trim($eval->comment) }} »</p>
                        @endif
                    </div>
                @empty
                    <div class="text-center py-6 bg-gray-50 rounded-xl border border-dashed border-gray-200">
                        <svg class="w-10 h-10 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                        </svg>
                        <p class="text-gray-500 text-sm">Aucune évaluation enregistrée pour le moment.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Resources Section -->
        @if(isset($resources) && $resources->count() > 0)
        <div class="bg-white rounded-2xl shadow-lg p-8 mb-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Ressources publiées</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($resources as $resource)
                    <a href="{{ route('auth.login') }}?redirect={{ urlencode(route('jeune.resources.show', $resource->slug)) }}" class="block group">
                        <div class="border border-gray-200 rounded-2xl overflow-hidden hover:border-orange-300 hover:shadow-lg transition bg-white h-full flex flex-col relative">
                            @if($resource->thumbnail_url)
                                <div class="h-48 w-full overflow-hidden relative z-10">
                                    <img src="{{ $resource->thumbnail_url }}" alt="{{ $resource->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                </div>
                            @else
                                <div class="h-48 w-full bg-gradient-to-br from-orange-50 to-pink-50 flex items-center justify-center relative z-10">
                                    <svg class="w-16 h-16 text-orange-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                </div>
                            @endif

                            <div class="p-5 flex flex-col flex-1 relative z-10 bg-white">
                                <h3 class="font-bold text-lg text-gray-900 group-hover:text-orange-600 transition mb-2">{{ $resource->title }}</h3>
                                <p class="text-sm text-gray-600 mb-4 line-clamp-3 flex-1">{{ Str::limit($resource->description ?? '', 100) }}</p>
                            </div>

                            <!-- Overlay CTA on Hover -->
                            <div class="absolute inset-0 bg-white/60 backdrop-blur-[2px] opacity-0 group-hover:opacity-100 transition flex items-center justify-center z-20">
                                <span class="px-4 py-2 bg-orange-600 text-white font-bold rounded-lg shadow-md transform translate-y-4 group-hover:translate-y-0 transition flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                    Connectez-vous pour lire
                                </span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Roadmap -->
        @if($publicData['roadmap']->isNotEmpty())
        <div class="bg-white rounded-2xl shadow-lg p-8 mb-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Parcours professionnel</h2>
            <div class="space-y-6">
                @foreach($publicData['roadmap'] as $step)
                <div class="flex gap-4">
                    <div class="flex-shrink-0">
                        <div
                            class="w-12 h-12 rounded-full {{ $step['step_type'] === 'education' ? 'bg-blue-100 text-blue-600' : 'bg-green-100 text-green-600' }} flex items-center justify-center">
                            @if($step['step_type'] === 'education')
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M12 14l9-5-9-5-9 5 9 5z"></path>
                                <path
                                    d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z">
                                </path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222">
                                </path>
                            </svg>
                            @else
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                </path>
                            </svg>
                            @endif
                        </div>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-900">{{ $step['title'] }}</h3>
                        @if($step['institution_company'])
                        <p class="text-gray-600">{{ $step['institution_company'] }}</p>
                        @endif
                        @if($step['start_date'] || $step['end_date'])
                        <p class="text-sm text-gray-500 mt-1">
                            {{ $step['start_date'] ? \Carbon\Carbon::parse($step['start_date'])->format('Y') : '' }}
                            @if($step['end_date'])
                            - {{ \Carbon\Carbon::parse($step['end_date'])->format('Y') }}
                            @endif
                        </p>
                        @endif
                        @if($step['description'])
                        <p class="text-gray-700 mt-2">{{ $step['description'] }}</p>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- CTA -->
        <div class="bg-gradient-to-r from-orange-600 to-pink-600 rounded-2xl shadow-lg p-8 text-center text-white">
            <h2 class="text-3xl font-bold mb-4">Inspiré par ce parcours ?</h2>
            <p class="text-lg mb-6 opacity-90">Rejoignez {{ $displayOrg ? $displayOrg->name : 'Brillio' }} pour découvrir des centaines de mentors et construire
                votre propre parcours professionnel</p>
            <a href="{{ route('home') }}"
                class="inline-block px-8 py-4 bg-white text-orange-600 rounded-lg font-bold text-lg hover:bg-gray-100 transition">
                Rejoindre la communauté gratuitement
            </a>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white mt-16 py-8">
        <div class="max-w-6xl mx-auto px-4 text-center">
            <p class="text-gray-400">© {{ date('Y') }} {{ $displayOrg ? $displayOrg->name : 'Brillio' }} - Plateforme d'orientation professionnelle</p>
            <div class="mt-4 flex justify-center gap-6">
                <a href="{{ url('/politique-de-confidentialite') }}"
                    class="text-gray-400 hover:text-white">Confidentialité</a>
                <a href="{{ route('terms') }}" class="text-gray-400 hover:text-white">Conditions</a>
                <a href="{{ route('contact') }}" class="text-gray-400 hover:text-white">Contact</a>
            </div>
        </div>
    </footer>

    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        function shareProfile() {
            if (navigator.share) {
                navigator.share({
                    title: '{{ $publicData["name"] }} - Mentor {{ $displayOrg ? addslashes($displayOrg->name) : "Brillio" }}',
                    text: 'Découvrez le parcours de {{ $publicData["name"] }}, mentor en {{ $publicData["specialization"] }}',
                    url: window.location.href
                }).catch(err => console.log('Erreur de partage:', err));
            } else {
                // Fallback: copier le lien
                navigator.clipboard.writeText(window.location.href);
                alert('Lien copié dans le presse-papier !');
            }
        }
    </script>
</body>

</html>