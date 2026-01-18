@extends('layouts.public')

@section('title', $profile->user->name . ' - Profil Brillio')

@section('content')
    <div class="min-h-screen bg-gray-50 pt-24 pb-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Header Profile -->
            <div class="bg-white rounded-3xl shadow-lg border border-gray-100 overflow-hidden mb-8">
                <div class="h-32 bg-gradient-to-r from-blue-500 to-indigo-600"></div>
                <div class="px-8 pb-8">
                    <div class="relative flex justify-between items-end -mt-12 mb-6">
                        <div class="flex items-end gap-6">
                            <div class="w-24 h-24 rounded-2xl bg-white p-1 shadow-lg">
                                <div
                                    class="w-full h-full bg-gray-100 rounded-xl flex items-center justify-center text-3xl font-bold text-gray-400">
                                    {{ substr($profile->user->name, 0, 1) }}
                                </div>
                            </div>
                            <div class="mb-1">
                                <h1 class="text-3xl font-bold text-gray-900">{{ $profile->user->name }}</h1>
                                <p class="text-gray-500 font-medium">{{ $profile->user->city }},
                                    {{ $profile->user->country }}</p>
                            </div>
                        </div>

                        @if($profile->linkedin_url)
                            <a href="{{ $profile->linkedin_url }}" target="_blank"
                                class="p-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" />
                                </svg>
                            </a>
                        @endif
                    </div>

                    @if($profile->bio)
                        <div class="prose max-w-none text-gray-600">
                            {{ $profile->bio }}
                        </div>
                    @endif
                </div>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <!-- Colonne Gauche -->
                <div class="md:col-span-2 space-y-8">
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
                        class="bg-gradient-to-b from-indigo-500 to-purple-600 rounded-2xl p-6 text-white text-center shadow-lg">
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
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection