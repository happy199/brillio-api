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
                <p class="text-gray-600">Conversation avec {{ $conversation->user->name }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Infos utilisateur -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="text-center">
                    <div class="h-16 w-16 rounded-full bg-blue-100 flex items-center justify-center mx-auto">
                        <span class="text-blue-600 font-bold text-2xl">
                            {{ strtoupper(substr($conversation->user->name, 0, 1)) }}
                        </span>
                    </div>
                    <h3 class="mt-4 font-semibold text-gray-900">{{ $conversation->user->name }}</h3>
                    <p class="text-sm text-gray-500">{{ $conversation->user->email }}</p>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium mt-2 {{ $conversation->user->user_type === 'mentor' ? 'bg-orange-100 text-orange-800' : 'bg-blue-100 text-blue-800' }}">
                        {{ $conversation->user->user_type === 'mentor' ? 'Mentor' : 'Jeune' }}
                    </span>
                </div>

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
                </div>

                @if($conversation->user->personalityTest)
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
            </div>
        </div>

        <!-- Messages -->
        <div class="lg:col-span-3">
            <div class="bg-white rounded-xl shadow-sm">
                <div class="p-4 border-b">
                    <h3 class="font-semibold text-gray-900">Messages ({{ $conversation->messages->count() }})</h3>
                </div>

                <div class="p-4 space-y-4 max-h-[600px] overflow-y-auto">
                    @forelse($conversation->messages as $message)
                    <div class="flex {{ $message->role === 'user' ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[80%]">
                            <div class="flex items-center gap-2 mb-1 {{ $message->role === 'user' ? 'justify-end' : 'justify-start' }}">
                                @if($message->role === 'assistant')
                                <div class="w-6 h-6 rounded-full bg-purple-100 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                    </svg>
                                </div>
                                <span class="text-xs text-gray-500">Brillio</span>
                                @else
                                <span class="text-xs text-gray-500">{{ $conversation->user->name }}</span>
                                <div class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center">
                                    <span class="text-xs text-blue-600 font-semibold">
                                        {{ strtoupper(substr($conversation->user->name, 0, 1)) }}
                                    </span>
                                </div>
                                @endif
                            </div>
                            <div class="rounded-2xl px-4 py-3 {{ $message->role === 'user' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-800' }}">
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
            </div>
        </div>
    </div>
</div>
@endsection
