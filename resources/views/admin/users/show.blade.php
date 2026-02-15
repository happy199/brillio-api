@extends('layouts.admin')

@section('title', 'Détails utilisateur')
@section('header', 'Profil de ' . $user->name)

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Bouton retour -->
    <div class="mb-6">
        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center text-gray-600 hover:text-gray-900">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                </path>
            </svg>
            Retour à la liste
        </a>
    </div>

    <!-- Profil principal -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-8">
            <div class="flex items-center">
                <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center shadow-lg">
                    @if($user->avatar_url)
                    <img src="{{ $user->avatar_url }}" alt="" class="w-20 h-20 rounded-full object-cover"
                        onerror="this.onerror=null; this.parentElement.innerHTML='<span class=\'text-3xl font-bold text-indigo-600\'>{{ substr($user->name, 0, 1) }}</span>';">
                    @else
                    <span class="text-3xl font-bold text-indigo-600">{{ substr($user->name, 0, 1) }}</span>
                    @endif
                </div>
                <div class="ml-6 text-white">
                    <h2 class="text-2xl font-bold">{{ $user->name }}</h2>
                    <p class="text-indigo-100">{{ $user->email }}</p>
                    <div class="flex items-center mt-2 space-x-2">
                        <span
                            class="px-2 py-1 text-xs rounded-full {{ $user->user_type === 'mentor' ? 'bg-purple-200 text-purple-800' : 'bg-blue-200 text-blue-800' }}">
                            {{ ucfirst($user->user_type) }}
                        </span>
                        @if($user->is_admin)
                        <span class="px-2 py-1 text-xs bg-red-200 text-red-800 rounded-full">Admin</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="p-6">
            <div class="grid md:grid-cols-2 gap-6">
                <!-- Informations de base -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Informations personnelles</h3>
                    <dl class="space-y-3">
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Date de naissance</dt>
                            <dd class="text-gray-900">{{ $user->birth_date ? $user->birth_date->format('d/m/Y') : '-' }}
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Pays</dt>
                            <dd class="text-gray-900">{{ $user->country ?? '-' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Ville</dt>
                            <dd class="text-gray-900">{{ $user->city ?? '-' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Téléphone</dt>
                            <dd class="text-gray-900">{{ $user->phone ?? '-' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Inscrit le</dt>
                            <dd class="text-gray-900">{{ $user->created_at->format('d/m/Y à H:i') }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Dernière connexion</dt>
                            <dd class="text-gray-900">
                                {{ $user->last_login_at ? $user->last_login_at->format('d/m/Y à H:i') : 'Jamais' }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Statistiques -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Statistiques d'utilisation</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-blue-50 rounded-lg p-4 text-center">
                            <p class="text-2xl font-bold text-blue-600">{{ $user->chatConversations()->count() }}</p>
                            <p class="text-sm text-blue-600">Conversations</p>
                        </div>
                        <div class="bg-green-50 rounded-lg p-4 text-center">
                            <p class="text-2xl font-bold text-green-600">{{ $user->academicDocuments()->count() }}</p>
                            <p class="text-sm text-green-600">Documents</p>
                        </div>
                        <div class="bg-purple-50 rounded-lg p-4 text-center">
                            <p class="text-2xl font-bold text-purple-600">
                                {{ $user->chatConversations()->withCount('messages')->get()->sum('messages_count') }}
                            </p>
                            <p class="text-sm text-purple-600">Messages</p>
                        </div>
                        <div class="bg-orange-50 rounded-lg p-4 text-center">
                            <p class="text-2xl font-bold text-orange-600">
                                {{ $user->personalityTest && $user->personalityTest->completed_at ? '1' : '0' }}
                            </p>
                            <p class="text-sm text-orange-600">Test de personnalité</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Test de personnalité -->
    @if($user->personalityTest)
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Résultat du test de personnalité</h3>

        @if($user->personalityTest->completed_at)
        <div class="flex items-center space-x-6">
            <div
                class="w-24 h-24 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-xl flex items-center justify-center">
                <span class="text-2xl font-bold text-white">{{ $user->personalityTest->personality_type }}</span>
            </div>
            <div class="flex-1">
                <h4 class="text-xl font-semibold text-gray-900">
                    {{ $user->personalityTest->personality_label ?? $user->personalityTest->personality_type }}</h4>
                <p class="text-gray-600 mt-1">{{ Str::limit($user->personalityTest->personality_description, 200) }}</p>
                <p class="text-sm text-gray-400 mt-2">Test passé le
                    {{ $user->personalityTest->completed_at->format('d/m/Y à H:i') }}</p>
            </div>
        </div>

        @if($user->personalityTest->traits_scores)
        <div class="mt-6 grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach($user->personalityTest->traits_scores as $trait => $score)
            <div class="text-center">
                <p class="text-sm text-gray-500 mb-1">{{ $trait }}</p>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ $score }}%"></div>
                </div>
                <p class="text-xs text-gray-400 mt-1">{{ $score }}%</p>
            </div>
            @endforeach
        </div>
        @endif
        @else
        <p class="text-gray-500">Test commencé mais non terminé.</p>
        @endif
    </div>
    @endif

    <!-- Profil Mentor -->
    @if($user->user_type === 'mentor' && $user->mentorProfile)
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Profil Mentor</h3>
            <span
                class="px-3 py-1 text-sm rounded-full
                            {{ $user->mentorProfile->is_validated ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                {{ $user->mentorProfile->is_validated ? 'Validé' : 'En attente' }}
            </span>
        </div>

        <div class="grid md:grid-cols-2 gap-6">
            <div>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-gray-500 text-sm">Profession</dt>
                        <dd class="text-gray-900 font-medium">{{ $user->mentorProfile->current_position ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 text-sm">Entreprise</dt>
                        <dd class="text-gray-900">{{ $user->mentorProfile->company ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 text-sm">Secteur</dt>
                        <dd class="text-gray-900">{{ $user->mentorProfile->industry ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 text-sm">Années d'expérience</dt>
                        <dd class="text-gray-900">{{ $user->mentorProfile->years_experience ?? '-' }} ans</dd>
                    </div>
                </dl>
            </div>
            <div>
                <dt class="text-gray-500 text-sm mb-2">Bio</dt>
                <dd class="text-gray-900">{{ $user->mentorProfile->bio ?? 'Aucune bio' }}</dd>
            </div>
        </div>

        @if(!$user->mentorProfile->is_validated)
        <div class="mt-6 flex space-x-3">
            <form action="{{ route('admin.mentors.approve', $user->mentorProfile) }}" method="POST">
                @csrf
                @method('PUT')
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    Valider le profil
                </button>
            </form>
            <form action="{{ route('admin.mentors.reject', $user->mentorProfile) }}" method="POST">
                @csrf
                @method('PUT')
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    Rejeter
                </button>
            </form>
        </div>
        @endif
    </div>
    @endif

    <!-- Documents récents -->
    @if($user->academicDocuments()->count() > 0)
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Documents récents</h3>
        <div class="space-y-3">
            @foreach($user->academicDocuments()->latest()->take(5)->get() as $document)
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div class="flex items-center">
                    <svg class="w-8 h-8 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    <div>
                        <p class="font-medium text-gray-900">{{ $document->original_name }}</p>
                        <p class="text-sm text-gray-500">{{ $document->document_type }} -
                            {{ number_format($document->file_size / 1024, 1) }} Ko</p>
                    </div>
                </div>
                <a href="{{ route('admin.documents.download', $document) }}"
                    class="text-indigo-600 hover:text-indigo-900">
                    Télécharger
                </a>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Conversations récentes -->
    @if($user->chatConversations()->count() > 0)
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Conversations récentes</h3>
        <div class="space-y-3">
            @foreach($user->chatConversations()->with('messages')->latest()->take(5)->get() as $conversation)
            <a href="{{ route('admin.chat.show', $conversation) }}"
                class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                <div>
                    <p class="font-medium text-gray-900">
                        {{ $conversation->title ?? 'Conversation #' . $conversation->id }}</p>
                    <p class="text-sm text-gray-500">{{ $conversation->messages->count() }} messages</p>
                </div>
                <span class="text-sm text-gray-400">{{ $conversation->updated_at->diffForHumans() }}</span>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Actions -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Actions</h3>
        <div class="flex flex-wrap gap-3">
            @if($user->id !== auth()->id())
            <form action="{{ route('admin.users.toggle-admin', $user) }}" method="POST">
                @csrf
                @method('PUT')
                <button type="submit"
                    class="px-4 py-2 {{ $user->is_admin ? 'bg-yellow-600' : 'bg-indigo-600' }} text-white rounded-lg hover:opacity-90">
                    {{ $user->is_admin ? 'Retirer les droits admin' : 'Rendre administrateur' }}
                </button>
            </form>

            <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ? Cette action est irréversible.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    Supprimer l'utilisateur
                </button>
            </form>
            @else
            <p class="text-gray-500 italic">Vous ne pouvez pas modifier votre propre compte depuis cette page.</p>
            @endif
        </div>
    </div>
</div>
@endsection