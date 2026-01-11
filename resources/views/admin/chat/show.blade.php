@extends('layouts.admin')

@section('title', 'Conversation - ' . ($conversation->title ?? 'Sans titre'))

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.chat.index') }}" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $conversation->title ?? 'Sans titre' }}</h1>
                <p class="text-gray-600">Conversation avec {{ $conversation->user->name ?? 'Utilisateur supprimé' }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <!-- Statut de la conversation -->
            @if($conversation->needs_human_support && !$conversation->human_support_active)
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                    <span class="w-2 h-2 bg-red-500 rounded-full mr-2 animate-pulse"></span>
                    En attente de conseiller
                </span>
                <form action="{{ route('admin.chat.take-over', $conversation) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium">
                        Prendre en charge
                    </button>
                </form>
            @elseif($conversation->human_support_active)
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-orange-100 text-orange-800">
                    <span class="w-2 h-2 bg-orange-500 rounded-full mr-2"></span>
                    Support actif
                    @if($conversation->supportAdmin)
                        - {{ $conversation->supportAdmin->name }}
                    @endif
                </span>
                <form action="{{ route('admin.chat.end-support', $conversation) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit"
                            onclick="return confirm('Êtes-vous sûr de vouloir terminer la session de support ? L\'utilisateur reviendra au chatbot IA.')"
                            class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 text-sm font-medium">
                        Terminer le support
                    </button>
                </form>
            @else
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                    <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                    Mode IA
                </span>
            @endif

            <a href="{{ route('admin.chat.export-pdf', $conversation) }}"
               class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium">
                Exporter PDF
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Infos utilisateur -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="text-center">
                    <div class="h-16 w-16 rounded-full bg-blue-100 flex items-center justify-center mx-auto">
                        <span class="text-blue-600 font-bold text-2xl">
                            {{ strtoupper(substr($conversation->user->name ?? '?', 0, 1)) }}
                        </span>
                    </div>
                    <h3 class="mt-4 font-semibold text-gray-900">{{ $conversation->user->name ?? 'Utilisateur supprimé' }}</h3>
                    <p class="text-sm text-gray-500">{{ $conversation->user->email ?? '-' }}</p>
                    @if($conversation->user)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium mt-2 {{ $conversation->user->user_type === 'mentor' ? 'bg-orange-100 text-orange-800' : 'bg-blue-100 text-blue-800' }}">
                        {{ $conversation->user->user_type === 'mentor' ? 'Mentor' : 'Jeune' }}
                    </span>
                    @endif
                </div>

                @if($conversation->user)
                <div class="mt-6 border-t pt-6 space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Pays</span>
                        <span class="text-gray-900">{{ $conversation->user->country ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Messages</span>
                        <span class="text-gray-900">{{ $conversation->messages->count() }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Créée le</span>
                        <span class="text-gray-900">{{ $conversation->created_at->format('d/m/Y') }}</span>
                    </div>
                    @if($conversation->human_support_started_at)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Support démarré</span>
                        <span class="text-gray-900">{{ $conversation->human_support_started_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @endif
                    @if($conversation->human_support_ended_at)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Support terminé</span>
                        <span class="text-gray-900">{{ $conversation->human_support_ended_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @endif
                </div>
                @endif

                @if($conversation->user && $conversation->user->personalityTest)
                <div class="mt-6 border-t pt-6">
                    <h4 class="text-sm font-medium text-gray-900 mb-2">Personnalité</h4>
                    <div class="text-center p-4 bg-purple-50 rounded-lg">
                        <span class="text-2xl font-bold text-purple-600">
                            {{ $conversation->user->personalityTest->personality_type }}
                        </span>
                        <p class="text-sm text-purple-700 mt-1">
                            {{ $conversation->user->personalityTest->personality_label }}
                        </p>
                    </div>
                </div>
                @endif

                <!-- Actions rapides -->
                @if($conversation->user)
                <div class="mt-6 border-t pt-6">
                    <a href="{{ route('admin.users.show', $conversation->user) }}"
                       class="block w-full text-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm">
                        Voir le profil complet
                    </a>
                </div>
                @endif
            </div>
        </div>

        <!-- Messages -->
        <div class="lg:col-span-3">
            <div class="bg-white rounded-xl shadow-sm flex flex-col" style="height: 700px;">
                <div class="p-4 border-b flex justify-between items-center">
                    <h3 class="font-semibold text-gray-900">Messages ({{ $conversation->messages->count() }})</h3>
                    @if($conversation->human_support_active)
                    <span class="text-sm text-orange-600 font-medium">Mode conseiller actif</span>
                    @endif
                </div>

                <div class="flex-1 p-4 space-y-4 overflow-y-auto" id="messages-container">
                    @forelse($conversation->messages as $message)
                    <div class="flex {{ $message->role === 'user' ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[80%]">
                            <div class="flex items-center gap-2 mb-1 {{ $message->role === 'user' ? 'justify-end' : 'justify-start' }}">
                                @if($message->role === 'assistant')
                                    @if($message->is_from_human)
                                    <div class="w-6 h-6 rounded-full bg-orange-100 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </div>
                                    <span class="text-xs text-orange-600 font-medium">
                                        Conseiller {{ $message->admin ? '(' . $message->admin->name . ')' : '' }}
                                    </span>
                                    @elseif($message->is_system_message)
                                    <div class="w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <span class="text-xs text-gray-500">Système</span>
                                    @else
                                    <div class="w-6 h-6 rounded-full bg-purple-100 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                        </svg>
                                    </div>
                                    <span class="text-xs text-gray-500">Brillio IA</span>
                                    @endif
                                @else
                                <span class="text-xs text-gray-500">{{ $conversation->user->name ?? 'Utilisateur' }}</span>
                                <div class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center">
                                    <span class="text-xs text-blue-600 font-semibold">
                                        {{ strtoupper(substr($conversation->user->name ?? '?', 0, 1)) }}
                                    </span>
                                </div>
                                @endif
                            </div>
                            <div class="rounded-2xl px-4 py-3
                                @if($message->role === 'user')
                                    bg-blue-600 text-white
                                @elseif($message->is_from_human)
                                    bg-orange-100 text-orange-900 border border-orange-200
                                @elseif($message->is_system_message)
                                    bg-gray-100 text-gray-600 italic
                                @else
                                    bg-gray-100 text-gray-800
                                @endif">
                                <p class="text-sm whitespace-pre-wrap">{{ $message->content }}</p>
                            </div>
                            <p class="text-xs text-gray-400 mt-1 {{ $message->role === 'user' ? 'text-right' : 'text-left' }}">
                                {{ $message->created_at->format('d/m/Y H:i') }}
                            </p>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-12 text-gray-500">
                        Aucun message dans cette conversation
                    </div>
                    @endforelse
                </div>

                <!-- Zone de réponse du conseiller -->
                @if($conversation->human_support_active)
                <div class="p-4 border-t bg-orange-50">
                    <form action="{{ route('admin.chat.send-message', $conversation) }}" method="POST">
                        @csrf
                        <div class="flex gap-3">
                            <div class="flex-1">
                                <textarea name="content"
                                          rows="2"
                                          placeholder="Écrivez votre message en tant que conseiller..."
                                          class="w-full rounded-lg border-orange-200 focus:border-orange-400 focus:ring-orange-400 text-sm resize-none"
                                          required></textarea>
                            </div>
                            <div class="flex flex-col gap-2">
                                <button type="submit"
                                        class="px-6 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 text-sm font-medium h-full">
                                    Envoyer
                                </button>
                            </div>
                        </div>
                        <p class="text-xs text-orange-600 mt-2">
                            Vous répondez en tant que conseiller humain. L'utilisateur verra votre message comme venant de "Brillio".
                        </p>
                    </form>
                </div>
                @elseif($conversation->needs_human_support && !$conversation->human_support_active)
                <div class="p-4 border-t bg-red-50 text-center">
                    <p class="text-red-700 mb-3">Cet utilisateur attend un conseiller humain.</p>
                    <form action="{{ route('admin.chat.take-over', $conversation) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium">
                            Prendre en charge cette conversation
                        </button>
                    </form>
                </div>
                @else
                <div class="p-4 border-t bg-gray-50 text-center text-gray-500 text-sm">
                    Cette conversation est gérée par l'IA. Aucune action de votre part n'est requise.
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Scroll automatique vers le bas des messages
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('messages-container');
        if (container) {
            container.scrollTop = container.scrollHeight;
        }
    });
</script>
@endpush
@endsection
