@extends('layouts.admin')

@section('title', 'Conversations Chat')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Conversations Chat</h1>
            <p class="text-gray-600">Consultez les conversations avec le chatbot IA</p>
        </div>
        <div class="flex gap-3">
            <input type="text"
                   placeholder="Rechercher un utilisateur..."
                   class="rounded-lg border-gray-300 text-sm w-64"
                   id="search-user">
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
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
    </div>

    <!-- Liste des conversations -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Utilisateur</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Titre</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Messages</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dernière activité</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($conversations as $conversation)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="h-10 w-10 flex-shrink-0">
                                <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                    <span class="text-blue-600 font-semibold">
                                        {{ strtoupper(substr($conversation->user->name, 0, 1)) }}
                                    </span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="font-medium text-gray-900">{{ $conversation->user->name }}</div>
                                <div class="text-sm text-gray-500">{{ $conversation->user->email }}</div>
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
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('admin.chat.show', $conversation) }}"
                           class="text-blue-600 hover:text-blue-800">
                            Voir
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                        Aucune conversation trouvée
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Pagination -->
        @if($conversations->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $conversations->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
