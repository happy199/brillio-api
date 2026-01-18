@extends('layouts.public')

@section('title', $user->name . ' - Profil Brillio')

@section('content')
    <div class="min-h-screen bg-gray-50 pt-24 pb-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Header Profile -->
            <div class="bg-white rounded-3xl shadow-xl shadow-indigo-100/50 border border-indigo-50 overflow-hidden mb-8">
                <!-- Banner avec gradient plus doux et décoratif -->
                <div class="h-48 bg-gradient-to-r from-[#E0E7FF] via-[#DBEAFE] to-[#E0F2FE] relative overflow-hidden">
                    <!-- Formes décoratives abstraites et douces -->
                    <div
                        class="absolute top-0 right-0 w-96 h-96 bg-gradient-to-br from-indigo-400/10 to-blue-400/10 rounded-full blur-3xl -mr-20 -mt-20">
                    </div>
                    <div
                        class="absolute bottom-0 left-0 w-72 h-72 bg-gradient-to-tr from-purple-400/10 to-pink-400/10 rounded-full blur-3xl -ml-20 -mb-20 mix-blend-multiply">
                    </div>
                </div>
                <div class="px-8 pb-8">
                    <div class="relative flex justify-between items-end -mt-16 mb-6">
                        <div class="flex items-end gap-6">
                            <div class="w-24 h-24 rounded-2xl bg-white p-1 shadow-lg">
                                @if($user->avatar_url)
                                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}"
                                        class="w-full h-full object-cover rounded-xl bg-gray-100">
                                @else
                                    <div
                                        class="w-full h-full bg-gray-100 rounded-xl flex items-center justify-center text-3xl font-bold text-gray-400">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                @endif
                            </div>
                            <div class="mb-1">
                                <h1 class="text-3xl font-bold text-gray-900">{{ $user->name }}</h1>
                                <p class="text-gray-500 font-medium">
                                    {{ $user->city ? $user->city . ', ' : '' }}{{ $user->country }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <!-- Website / Portfolio -->
                            @if($profile->portfolio_url)
                                <a href="{{ $profile->portfolio_url }}" target="_blank"
                                    class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition"
                                    title="Site web / Portfolio">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                    </svg>
                                </a>
                            @endif

                            <!-- LinkedIn -->
                            @if($user->linkedin_url)
                                <a href="{{ $user->linkedin_url }}" target="_blank"
                                    class="p-2 text-[#0A66C2] hover:bg-blue-50 rounded-lg transition" title="LinkedIn">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" />
                                    </svg>
                                </a>
                            @endif

                            <div class="h-8 w-px bg-gray-200 mx-2"></div>

                            <!-- Share Button -->
                            <button onclick="shareProfile()"
                                class="flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition font-medium text-sm">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                                </svg>
                                Partager
                            </button>
                        </div>
                    </div>

                    @if($profile->bio)
                        <div class="prose max-w-none text-gray-600">
                            {{ $profile->bio }}
                        </div>
                    @endif
                </div>
            </div>

            <script>
                function shareProfile() {
                    if (navigator.share) {
                        navigator.share({
                            title: '{{ $user->name }} - Profil Brillio',
                            text: 'Découvrez mon profil professionnel sur Brillio !',
                            url: window.location.href
                        }).catch(console.error);
                    } else {
                        navigator.clipboard.writeText(window.location.href).then(() => {
                            const btn = document.querySelector('button[onclick="shareProfile()"]');
                            const originalContent = btn.innerHTML;
                            btn.innerHTML = `<svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Copié !`;
                            setTimeout(() => {
                                btn.innerHTML = originalContent;
                            }, 2000);
                        });
                    }
                }
            </script>

            <!-- Personnalité (Full Width) -->
            @if($user->personalityTest && $user->personalityTest->personality_type)
                <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-2xl p-8 shadow-sm border border-purple-100 mb-8 relative overflow-hidden">
                     <div class="absolute top-0 right-0 w-64 h-64 bg-white/40 rounded-full blur-3xl -mr-16 -mt-16 pointer-events-none"></div>
                    
                    <div class="relative flex flex-col sm:flex-row items-center gap-6 text-center sm:text-left">
                        <div class="flex-shrink-0">
                            <div class="w-20 h-20 bg-white rounded-2xl flex items-center justify-center text-purple-600 shadow-sm">
                                <span class="text-xl font-extrabold">{{ $user->personalityTest->personality_type }}</span>
                            </div>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-xs font-bold text-purple-600 uppercase tracking-widest mb-1">Personnalité</h3>
                            <h2 class="text-2xl font-bold text-gray-900 mb-3">
                                {{ $user->personalityTest->personality_label ?? $user->personalityTest->personality_type }}
                            </h2>
                            <p class="text-gray-700 leading-relaxed">
                                {{ $user->personalityTest->personality_description }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="grid md:grid-cols-3 gap-8">
                <!-- Colonne Gauche -->
                <div class="md:col-span-2 space-y-8">

                    <!-- Infos Onboarding -->
                    <div class="grid sm:grid-cols-2 gap-4">
                        <!-- Situation Actuelle -->
                        @if(isset($user->onboarding_data['current_situation']))
                            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                                <h3 class="font-bold text-gray-900 mb-2 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    Situation
                                </h3>
                                <p class="text-gray-700 font-medium">
                                    {{ ucfirst($user->onboarding_data['current_situation']) }}
                                </p>
                                @if(isset($user->onboarding_data['education_level']))
                                    <p class="text-sm text-gray-500 mt-1">{{ ucfirst($user->onboarding_data['education_level']) }}</p>
                                @endif
                            </div>
                        @endif
                    </div>

                    <!-- Centres d'intérêts -->
                    @if(isset($user->onboarding_data['interests']) && count($user->onboarding_data['interests']) > 0)
                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                            <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                </svg>
                                Centres d'intérêts
                            </h3>
                            <div class="flex flex-wrap gap-2">
                                @foreach($user->onboarding_data['interests'] as $interest)
                                    <span class="px-3 py-1 bg-yellow-50 text-yellow-700 rounded-full text-sm font-medium">
                                        {{ $interest }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Portfolio -->
                    @if($profile->portfolio_url)
                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                            <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                </svg>
                                Portfolio / Projet
                            </h3>
                            <a href="{{ $profile->portfolio_url }}" target="_blank"
                                class="flex items-center justify-between p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition group">
                                <span
                                    class="text-gray-700 font-medium group-hover:text-indigo-600 truncate">{{ $profile->portfolio_url }}</span>
                                <svg class="w-5 h-5 text-gray-400 group-hover:text-indigo-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                            </a>
                        </div>
                    @endif

                    <!-- CV -->
                    @if($profile->cv_path)
                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                            <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                CV / Résumé
                            </h3>
                            <a href="{{ Storage::url($profile->cv_path) }}" target="_blank"
                                class="flex items-center gap-4 p-4 border-2 border-dashed border-gray-200 rounded-xl hover:border-red-200 hover:bg-red-50 transition group">
                                <div
                                    class="w-12 h-12 bg-red-100 text-red-600 rounded-lg flex items-center justify-center font-bold text-xs uppercase group-hover:scale-110 transition-transform">
                                    PDF</div>
                                <div>
                                    <p class="font-bold text-gray-900">Télécharger le CV</p>
                                    <p class="text-sm text-gray-500">Format PDF</p>
                                </div>
                            </a>
                        </div>
                    @endif
                </div>

                <!-- Colonne Droite: Badge / Info Sup -->
                <div>
                    <div
                        class="bg-gradient-to-b from-indigo-500 to-purple-600 rounded-2xl p-6 text-white text-center shadow-lg sticky top-6">
                        <div
                            class="w-16 h-16 bg-white/20 backdrop-blur rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                            </svg>
                        </div>
                        <h2 class="text-xl font-bold mb-2">Talent Brillio</h2>
                        <p class="text-white/80 text-sm mb-6">Ce profil est vérifié et actif sur la plateforme d'orientation
                            n°1 en Afrique.</p>

                        <div class="border-t border-white/20 pt-4 mt-4">
                            <a href="{{ route('home') }}"
                                class="inline-block px-4 py-2 bg-white text-indigo-600 rounded-lg text-sm font-bold shadow-sm hover:bg-indigo-50 transition">
                                Découvrir Brillio
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection