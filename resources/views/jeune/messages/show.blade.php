@extends('layouts.jeune')

@section('title', 'Messagerie — ' . $mentorship->mentor->name)

@push('styles')
<style>
    .chat-container {
        height: calc(100vh - 260px);
        min-height: 400px;
    }

    .message-bubble {
        max-width: 72%;
    }

    .chat-area {
        scroll-behavior: smooth;
    }
</style>
@endpush

@section('content')
<div class="max-w-3xl mx-auto">
    <!-- Header -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-4 flex items-center gap-4">
        <a href="{{ route('jeune.messages.index') }}"
            class="p-2 rounded-xl text-gray-400 hover:bg-gray-100 hover:text-gray-700 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        @if($mentorship->mentor->avatar_url)
        <img src="{{ $mentorship->mentor->avatar_url }}" alt="" class="w-10 h-10 rounded-full object-cover">
        @else
        <div
            class="w-10 h-10 rounded-full bg-gradient-to-br from-orange-400 to-red-500 flex items-center justify-center flex-shrink-0">
            <span class="text-white font-bold">{{ strtoupper(substr($mentorship->mentor->name, 0, 1)) }}</span>
        </div>
        @endif
        <div>
            <h2 class="font-semibold text-gray-900">{{ $mentorship->mentor->name }}</h2>
            <span class="text-xs text-emerald-600 font-medium flex items-center gap-1">
                <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>
                Mentor actif
            </span>
        </div>
    </div>

    <!-- Zone chat -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm flex flex-col chat-container overflow-hidden">
        <!-- Messages -->
        <div id="chat-area" class="flex-1 overflow-y-auto p-4 space-y-4 chat-area">
            @if($mentorship->messages->isEmpty())
            <div class="flex flex-col items-center justify-center h-full text-center py-12">
                <div class="w-14 h-14 bg-indigo-100 rounded-2xl flex items-center justify-center mb-3">
                    <svg class="w-7 h-7 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                </div>
                <p class="text-gray-500 text-sm">Envoyez votre premier message à <strong>{{ $mentorship->mentor->name
                        }}</strong></p>
            </div>
            @else
            @foreach($mentorship->messages as $message)
            @php $isMine = $message->sender_id === auth()->id(); @endphp
            <div class="flex {{ $isMine ? 'justify-end' : 'justify-start' }}">
                <div class="message-bubble">
                    @if(! $isMine)
                    <div class="flex items-end gap-2">
                        @if($message->sender->avatar_url)
                        <img src="{{ $message->sender->avatar_url }}"
                            class="w-7 h-7 rounded-full object-cover flex-shrink-0">
                        @else
                        <div
                            class="w-7 h-7 rounded-full bg-gradient-to-br from-orange-400 to-red-500 flex items-center justify-center flex-shrink-0">
                            <span class="text-white text-xs font-bold">{{ strtoupper(substr($message->sender->name, 0,
                                1)) }}</span>
                        </div>
                        @endif
                        <div>
                            @endif
                            @if($isMine)
                            <div>
                                @endif

                                @if($message->body)
                                <div
                                    class="px-4 py-2.5 rounded-2xl {{ $isMine ? 'bg-indigo-600 text-white rounded-br-sm' : 'bg-gray-100 text-gray-800 rounded-bl-sm' }}">
                                    <p class="text-sm whitespace-pre-wrap">{{ $message->body }}</p>
                                </div>
                                @endif

                                @if($message->hasAttachment())
                                <a href="{{ route('jeune.messages.download', $message) }}"
                                    class="mt-1 flex items-center gap-2 px-3 py-2 rounded-xl text-sm {{ $isMine ? 'bg-indigo-500 text-white hover:bg-indigo-400' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }} transition">
                                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                    </svg>
                                    <span class="truncate max-w-[200px]">{{ $message->attachment_name }}</span>
                                    <svg class="w-3.5 h-3.5 flex-shrink-0 opacity-70" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                </a>
                                @endif

                                <p
                                    class="text-[11px] mt-1 {{ $isMine ? 'text-right text-indigo-300' : 'text-gray-400' }}">
                                    {{ $message->created_at->format('d/m H:i') }}
                                    @if($isMine && $message->read_at)
                                    · <span class="text-emerald-400">Lu</span>
                                    @endif
                                </p>
                            </div>
                            @if(! $isMine)
                        </div>@endif
                    </div>
                </div>
                @endforeach
                @endif
            </div>

            <!-- Formulaire d'envoi -->
            <div class="border-t border-gray-100 p-4 bg-gray-50 rounded-b-2xl">
                @if($errors->any())
                <p class="text-xs text-red-500 mb-2">{{ $errors->first() }}</p>
                @endif
                <form action="{{ route('jeune.messages.store', $mentorship) }}" method="POST"
                    enctype="multipart/form-data" x-data="{ fileName: '' }">
                    @csrf
                    <div class="flex gap-2 items-end">
                        <div class="flex-1">
                            <textarea name="body" rows="2" id="message-input"
                                class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm resize-none p-3"
                                placeholder="Votre message...">{{ old('body') }}</textarea>
                            <div x-show="fileName" class="text-xs text-indigo-600 mt-1 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                </svg>
                                <span x-text="fileName"></span>
                            </div>
                        </div>

                        <!-- Bouton fichier -->
                        <label
                            class="flex-shrink-0 cursor-pointer p-3 rounded-xl bg-gray-100 hover:bg-indigo-100 text-gray-500 hover:text-indigo-600 transition"
                            title="Joindre un fichier">
                            <input type="file" name="attachment" class="hidden"
                                @change="fileName = $event.target.files[0]?.name ?? ''">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                            </svg>
                        </label>

                        <!-- Bouton envoyer -->
                        <button type="submit"
                            class="flex-shrink-0 p-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endsection

    @push('scripts')
    <script>
        // Auto-scroll vers le bas
        const chatArea = document.getElementById('chat-area');
        if (chatArea) chatArea.scrollTop = chatArea.scrollHeight;
    </script>
    @endpush