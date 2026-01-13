@extends('layouts.jeune')

@section('title', 'Changer mon mot de passe')

@section('content')
    <div class="max-w-2xl mx-auto space-y-6">
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('jeune.profile') }}" class="p-2 hover:bg-gray-100 rounded-lg transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <h1 class="text-2xl font-bold text-gray-900">Changer mon mot de passe</h1>
        </div>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-2xl p-6 shadow-sm">
            @if($isOAuthUser && !$hasPassword)
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6">
                    <div class="flex gap-3">
                        <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <p class="font-medium text-blue-900">Compte connecté via
                                {{ ucfirst(auth()->user()->auth_provider) }}
                            </p>
                            <p class="text-sm text-blue-700 mt-1">Vous pouvez définir un mot de passe pour vous connecter aussi
                                par email. Votre connexion {{ ucfirst(auth()->user()->auth_provider) }} restera active.</p>
                        </div>
                    </div>
                </div>
            @endif

            <form action="{{ route('jeune.password.update') }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                @if($hasPassword)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Mot de passe actuel</label>
                        <input type="password" name="current_password" required
                            class="w-full px-4 py-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                @endif

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        {{ $hasPassword ? 'Nouveau mot de passe' : 'Créer un mot de passe' }}
                    </label>
                    <input type="password" name="new_password" required
                        class="w-full px-4 py-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <p class="text-xs text-gray-500 mt-1">Minimum 8 caractères</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Confirmer le mot de passe</label>
                    <input type="password" name="new_password_confirmation" required
                        class="w-full px-4 py-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>

                <div class="flex gap-3">
                    <a href="{{ route('jeune.profile') }}"
                        class="flex-1 py-3 border rounded-xl font-medium text-gray-700 hover:bg-gray-50 transition text-center">
                        Annuler
                    </a>
                    <button type="submit"
                        class="flex-1 py-3 bg-gradient-to-r from-primary-500 to-secondary-500 text-white font-semibold rounded-xl hover:shadow-lg transition">
                        {{ $hasPassword ? 'Modifier' : 'Définir' }} le mot de passe
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection