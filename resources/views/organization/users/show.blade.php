@extends('layouts.organization')

@section('title', 'Profil de ' . $user->name)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <a href="{{ route('organization.users.index') }}"
                class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700">
                <svg class="mr-1 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Retour
            </a>
            <h1 class="text-2xl font-bold text-gray-900">{{ $user->name }}</h1>
        </div>
        <div class="flex items-center space-x-3">
            <!-- Export Options -->
            <div class="flex items-center bg-white border border-gray-200 rounded-lg shadow-sm mr-2">
                <a href="{{ route('organization.users.export', [$user, 'format' => 'pdf']) }}"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 rounded-l-lg border-r border-gray-200"
                    title="Télécharger en PDF">
                    <svg class="mr-2 h-4 w-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9a1 1 0 000 2h6a1 1 0 100-2H7z" />
                    </svg>
                    PDF
                </a>
                <a href="{{ route('organization.users.export', [$user, 'format' => 'csv']) }}"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 rounded-r-lg"
                    title="Télécharger en CSV">
                    <svg class="mr-2 h-4 w-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                            clip-rule="evenodd" />
                    </svg>
                    CSV
                </a>
            </div>

            <div class="text-right mr-2 hidden sm:block">
                <div class="text-xs text-gray-500 uppercase font-semibold">Complétion</div>
                <div class="text-sm font-bold text-indigo-600">{{ $user->profile_completion_percentage }}%</div>
            </div>
            <span
                class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium {{ $user->profile_completion_percentage === 100 ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                {{ $user->profile_completion_percentage === 100 ? 'Complet' : 'Incomplet' }}
            </span>
        </div>
    </div>

    <div class="relative min-h-[600px]">
        @if(!$organization->isPro())
        <div
            class="absolute inset-0 z-10 bg-white/60 backdrop-blur-[4px] rounded-lg flex flex-col items-center justify-center text-center p-8">
            <div class="bg-white p-8 rounded-xl shadow-2xl border border-gray-200 max-w-md sticky top-1/3">
                <div
                    class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-indigo-100 text-indigo-600 mb-6">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Fonctionnalité Pro</h3>
                <p class="text-gray-500 mb-8">
                    L'accès détaillé au profil des jeunes, incluant leurs documents, activités et mentorats, est réservé
                    aux membres Pro.
                </p>
                <a href="{{ route('organization.subscriptions.index') }}"
                    class="inline-flex w-full justify-center items-center rounded-md bg-indigo-600 px-5 py-3 text-base font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-colors">
                    Passer au plan Pro
                </a>
            </div>
        </div>
        @endif

        <div
            class="grid grid-cols-1 gap-6 lg:grid-cols-3 {{ !$organization->isPro() ? 'filter blur-[6px] select-none pointer-events-none opacity-50' : '' }}">
            <!-- Left Column -->
            <div class="space-y-6 lg:col-span-1">
                <!-- Profile Card -->
                <div class="bg-white shadow rounded-lg p-6">
                    <div class="flex flex-col items-center">
                        @if($user->avatar_url)
                        <img class="h-32 w-32 rounded-full object-cover" src="{{ $user->avatar_url }}"
                            alt="{{ $user->name }}">
                        @else
                        <div
                            class="h-32 w-32 rounded-full bg-organization-100 flex items-center justify-center text-organization-600 font-bold text-3xl">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                        @endif
                        <h2 class="mt-4 text-xl font-bold text-gray-900">{{ $user->name }}</h2>
                        <p class="text-gray-500">{{ $user->email }}</p>
                        <p class="mt-1 text-sm text-gray-500">Inscrit le {{ $user->created_at->format('d/m/Y') }}</p>
                    </div>

                    <div class="mt-6 border-t border-gray-100 pt-6 space-y-4">
                        {{-- Content hidden/blurred --}}
                        <div class="h-4 bg-gray-200 rounded w-3/4 mx-auto"></div>
                        <div class="h-4 bg-gray-200 rounded w-1/2 mx-auto"></div>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="space-y-6 lg:col-span-2">
                {{-- Fake Content for blurring --}}
                <div class="bg-white shadow rounded-lg p-6 h-64"></div>
                <div class="bg-white shadow rounded-lg p-6 h-64"></div>
            </div>
        </div>
    </div>
</div>
</div>
@endsection