@extends('layouts.admin')

@section('title', 'Conversations Chat')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Conversations Chat</h1>
            <p class="text-gray-600">Consultez et gérez les conversations avec le chatbot IA</p>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
        <div class="bg-white rounded-xl p-4 shadow-sm">
            <div class="text-sm text-gray-500">Total conversations</div>
            <div class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_conversations']) }}</div>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm">
            <div class="text-sm text-gray-500">Total messages</div>
            <div class="text-2xl font-bold text-blue-600">{{ number_format($stats['total_messages']) }}</div>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm">
            <div class="text-sm text-gray-500">Messages utilisateurs</div>
            <div class="text-2xl font-bold text-green-600">{{ number_format($stats['user_messages']) }}</div>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm">
            <div class="text-sm text-gray-500">Réponses IA</div>
            <div class="text-2xl font-bold text-purple-600">{{ number_format($stats['assistant_messages']) }}</div>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border-2 {{ $stats['pending_support'] > 0 ? 'border-red-500' : 'border-transparent' }}">
            <div class="text-sm text-gray-500">En attente de conseiller</div>
            <div class="text-2xl font-bold {{ $stats['pending_support'] > 0 ? 'text-red-600' : 'text-gray-900' }}">
                {{ number_format($stats['pending_support']) }}
                @if($stats['pending_support'] > 0)
                <span class="inline-block w-3 h-3 bg-red-500 rounded-full animate-pulse ml-2"></span>
                @endif
            </div>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm">
            <div class="text-sm text-gray-500">Support actif</div>
            <div class="text-2xl font-bold text-orange-600">{{ number_format($stats['active_support']) }}</div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="bg-white rounded-xl shadow-sm p-4">
        <form method="GET" action="{{ route('admin.chat.index') }}" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">Rechercher</label>
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Nom, email, titre ou contenu..."
                       class="w-full rounded-lg border-gray-300 text-sm">
            </div>
            <div class="w-48">
                <label class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                <select name="status" class="w-full rounded-lg border-gray-300 text-sm">
                    <option value="">Tous les statuts</option>
                    <option value="needs_support" {{ request('status') === 'needs_support' ? 'selected' : '' }}>En attente de conseiller</option>
                    <option value="in_support" {{ request('status') === 'in_support' ? 'selected' : '' }}>Support en cours</option>
                    <option value="normal" {{ request('status') === 'normal' ? 'selected' : '' }}>Normal (IA uniquement)</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm">
                    Filtrer
                </button>
                @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('admin.chat.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm">
                    Réinitialiser
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Liste des conversations -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Utilisateur</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Titre</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Messages</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dernière activité</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($conversations as $conversation)
                <tr class="hover:bg-gray-50 {{ $conversation->needs_human_support && !$conversation->human_support_active ? 'bg-red-50' : '' }}">
                    <td class="px-6 py-4">
                        @if($conversation->needs_human_support && !$conversation->human_support_active)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                <span class="w-2 h-2 bg-red-500 rounded-full mr-1 animate-pulse"></span>
                                En attente
                            </span>
                        @elseif($conversation->human_support_active)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                <span class="w-2 h-2 bg-orange-500 rounded-full mr-1"></span>
                                Support actif
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <span class="w-2 h-2 bg-green-500 rounded-full mr-1"></span>
                                Normal
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="h-10 w-10 flex-shrink-0">
                                <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                    <span class="text-blue-600 font-semibold">
                                        {{ strtoupper(substr($conversation->user->name ?? '?', 0, 1)) }}
                                    </span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="font-medium text-gray-900">{{ $conversation->user->name ?? 'Utilisateur supprimé' }}</div>
                                <div class="text-sm text-gray-500">{{ $conversation->user->email ?? '-' }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-900">{{ Str::limit($conversation->title ?? 'Sans titre', 40) }}</div>
                        <div class="text-xs text-gray-500">Créée le {{ $conversation->created_at->format('d/m/Y') }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            {{ $conversation->messages_count }} messages
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        {{ $conversation->updated_at->diffForHumans() }}
                    </td>
                    <td class="px-6 py-4 text-right space-x-2">
                        <a href="{{ route('admin.chat.show', $conversation) }}"
                           class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            Voir
                        </a>
                        @if($conversation->needs_human_support && !$conversation->human_support_active)
                            <form action="{{ route('admin.chat.take-over', $conversation) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-green-600 hover:text-green-800 text-sm font-medium">
                                    Prendre en charge
                                </button>
                            </form>
                        @endif
                        <a href="{{ route('admin.chat.export-pdf', $conversation) }}"
                           class="text-gray-600 hover:text-gray-800 text-sm font-medium">
                            PDF
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                        Aucune conversation trouvée
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Pagination -->
        @if($conversations->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $conversations->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
