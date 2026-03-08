@extends('layouts.organization')

@section('title', 'Détail de la conversation')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('organization.conversations.index') }}" class="p-2 hover:bg-gray-100 rounded-full transition">
            <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
        </a>
        <div class="flex items-center gap-3">
            <div class="flex -space-x-2">
                <img class="inline-block h-10 w-10 rounded-full ring-2 ring-white object-cover"
                    src="{{ $mentorship->mentee->avatar_url }}" alt="{{ $mentorship->mentee->name }}">
                <img class="inline-block h-10 w-10 rounded-full ring-2 ring-white object-cover"
                    src="{{ $mentorship->mentor->avatar_url }}" alt="{{ $mentorship->mentor->name }}">
            </div>
            <div>
                <h1 class="text-xl font-bold text-gray-900">Conversation : {{ $mentorship->mentee->name }} & {{
                    $mentorship->mentor->name }}</h1>
                <p class="text-xs text-gray-500">Statut du mentorat : {{ $mentorship->translated_status }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white shadow rounded-xl border border-gray-200 overflow-hidden flex flex-col h-[700px]">
        <!-- Header / Info -->
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
            <div class="flex items-center gap-4 text-sm text-gray-500">
                <div class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                    <span class="font-medium text-gray-700">Jeune parrainé</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                    <span class="font-medium text-gray-700">Mentor membre</span>
                </div>
            </div>
            <div
                class="bg-amber-100 text-amber-800 text-xs px-2.5 py-1 rounded-full font-bold uppercase tracking-wider">
                Lecture Seule
            </div>
        </div>

        <!-- Chat History -->
        <div class="flex-1 overflow-y-auto p-6 space-y-6 bg-gray-50/30">
            @forelse($messages as $message)
            <div class="flex {{ $message->sender_id === $mentorship->mentee_id ? 'justify-start' : 'justify-end' }}">
                <div
                    class="flex gap-3 max-w-[80%] {{ $message->sender_id === $mentorship->mentee_id ? 'flex-row' : 'flex-row-reverse' }}">
                    <img src="{{ $message->sender->avatar_url }}"
                        class="h-8 w-8 rounded-full object-cover flex-shrink-0">
                    <div>
                        <div
                            class="flex items-center gap-2 mb-1 {{ $message->sender_id === $mentorship->mentee_id ? '' : 'justify-end' }}">
                            <span class="text-xs font-bold text-gray-900">{{ $message->sender->name }}</span>
                            <span class="text-[10px] text-gray-400">{{ $message->created_at->format('H:i') }}</span>
                        </div>
                        <div
                            class="p-3 rounded-2xl shadow-sm {{ $message->sender_id === $mentorship->mentee_id ? 'bg-white text-gray-800 rounded-tl-none border border-gray-100' : 'bg-indigo-600 text-white rounded-tr-none' }}">
                            @if($message->body)
                            <p class="text-sm whitespace-pre-wrap">{{ $message->body }}</p>
                            @endif

                            @if($message->hasAttachment())
                            <div class="mt-2 pt-2 {{ $message->body ? 'border-t border-gray-100/20' : '' }}">
                                <a href="{{ route('organization.conversations.download', $message) }}"
                                    class="flex items-center gap-2 p-2 rounded-lg {{ $message->sender_id === $mentorship->mentee_id ? 'bg-gray-50 text-indigo-600 hover:bg-gray-100' : 'bg-white/10 text-white hover:bg-white/20' }} transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-bold truncate">{{ $message->attachment_name }}</p>
                                        <p class="text-[10px] opacity-70">Cliquer pour télécharger</p>
                                    </div>
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="h-full flex flex-col items-center justify-center text-gray-400 space-y-3">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
                <p class="text-sm italic">Aucun message échangé dans cette conversation.</p>
            </div>
            @endforelse
        </div>

        <!-- Footer / Disabled Input Area -->
        <div class="p-4 bg-white border-t border-gray-200">
            <div class="flex items-center gap-3 bg-gray-50 rounded-full px-4 py-2 border border-dashed border-gray-300">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                <span class="text-gray-400 text-sm italic">Interface de suivi uniquement. La réponse aux messages est
                    réservée aux participants.</span>
            </div>
        </div>
    </div>
</div>
@endsection