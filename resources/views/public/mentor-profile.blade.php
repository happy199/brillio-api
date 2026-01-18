<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $publicData['name'] }} - Mentor Brillio</title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="{{ Str::limit($publicData['bio'] ?? 'Mentor professionnel sur Brillio', 160) }}">
    <meta name="keywords" content="mentor, {{ $publicData['specialization'] }}, carrière, orientation professionnelle">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="profile">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="{{ $publicData['name'] }} - Mentor {{ $publicData['specialization'] }}">
    <meta property="og:description" content="{{ Str::limit($publicData['bio'] ?? 'Mentor professionnel', 200) }}">
    <meta property="og:image" content="{{ $publicData['picture'] ?? asset('images/brillio-og-image.jpg') }}">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:title" content="{{ $publicData['name'] }} - Mentor Brillio">
    <meta property="twitter:description" content="{{ Str::limit($publicData['bio'] ?? 'Mentor professionnel', 200) }}">
    
    <!-- LinkedIn -->
    <meta property="og:site_name" content="Brillio">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-4 py-4 flex justify-between items-center">
            <div class="flex items-center gap-2">
                <span class="text-2xl font-bold text-orange-600">Brillio</span>
            </div>
            <a href="{{ route('home') }}" class="px-6 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition">
                Rejoindre la communauté
            </a>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-6xl mx-auto px-4 py-8">
        <!-- Profile Header -->
        <div class="bg-white rounded-2xl shadow-lg p-8 mb-6">
            <div class="flex flex-col md:flex-row gap-6 items-start">
                @if($publicData['picture'])
                    <img src="{{ $publicData['picture'] }}" alt="{{ $publicData['name'] }}" 
                         class="w-24 h-24 rounded-full object-cover flex-shrink-0 border-4 border-orange-100">
                @else
                    <div class="w-24 h-24 rounded-full bg-gradient-to-br from-orange-500 to-pink-500 flex items-center justify-center text-white text-3xl font-bold flex-shrink-0">
                        {{ strtoupper(substr($publicData['name'], 0, 2)) }}
                    </div>
                @endif
                
                <div class="flex-1">
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $publicData['name'] }}</h1>
                    <p class="text-xl text-gray-600 mb-2">{{ $publicData['current_position'] }}{{ $publicData['current_company'] ? ' chez ' . $publicData['current_company'] : '' }}</p>
                    
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
                    </div>

                    <!-- Social Links -->
                    <div class="flex gap-3">
                        @if($publicData['linkedin_url'])
                            <a href="{{ $publicData['linkedin_url'] }}" target="_blank" rel="noopener" class="text-blue-600 hover:text-blue-800">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                            </a>
                        @endif
                        @if($publicData['website_url'])
                            <a href="{{ $publicData['website_url'] }}" target="_blank" rel="noopener" class="text-gray-600 hover:text-gray-800">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
                            </a>
                        @endif
                        
                        <!-- Share Button -->
                        <button onclick="shareProfile()" class="ml-auto px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path></svg>
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
                    <div class="w-16 h-16 bg-purple-100 rounded-2xl flex items-center justify-center text-purple-600 font-bold text-xl uppercase">
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
        <div class="bg-gradient-to-r from-orange-50 to-pink-50 rounded-2xl shadow-lg p-8 mb-6 border-l-4 border-orange-500">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">💡 Conseil de carrière</h2>
            <p class="text-gray-700 leading-relaxed italic">{{ $publicData['advice'] }}</p>
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
                        <div class="w-12 h-12 rounded-full {{ $step['step_type'] === 'education' ? 'bg-blue-100 text-blue-600' : 'bg-green-100 text-green-600' }} flex items-center justify-center">
                            @if($step['step_type'] === 'education')
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 14l9-5-9-5-9 5 9 5z"></path><path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"></path></svg>
                            @else
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
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
            <p class="text-lg mb-6 opacity-90">Rejoignez Brillio pour découvrir des centaines de mentors et construire votre propre parcours professionnel</p>
            <a href="{{ route('home') }}" class="inline-block px-8 py-4 bg-white text-orange-600 rounded-lg font-bold text-lg hover:bg-gray-100 transition">
                Rejoindre la communauté gratuitement
            </a>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white mt-16 py-8">
        <div class="max-w-6xl mx-auto px-4 text-center">
            <p class="text-gray-400">© {{ date('Y') }} Brillio - Plateforme d'orientation professionnelle</p>
            <div class="mt-4 flex justify-center gap-6">
                <a href="{{ url('/politique-de-confidentialite') }}" class="text-gray-400 hover:text-white">Confidentialité</a>
                <a href="{{ route('terms') }}" class="text-gray-400 hover:text-white">Conditions</a>
                <a href="{{ route('contact') }}" class="text-gray-400 hover:text-white">Contact</a>
            </div>
        </div>
    </footer>

    <script>
        function shareProfile() {
            if (navigator.share) {
                navigator.share({
                    title: '{{ $publicData["name"] }} - Mentor Brillio',
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
