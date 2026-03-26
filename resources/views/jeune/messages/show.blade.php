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

    [x-cloak] {
        display: none !important;
    }
</style>
@endpush

@section('content')
<div class="max-w-3xl mx-auto" x-data="{ isReportModalOpen: false }">
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

        <div class="flex-1">
            <h2 class="font-semibold text-gray-900">{{ $mentorship->mentor->name }}</h2>
            <span class="text-xs text-emerald-600 font-medium flex items-center gap-1">
                <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>
                Mentor actif
            </span>
        </div>

        @if(!$mentorship->isReported())
        <button type="button" @click="isReportModalOpen = true"
            class="text-xs text-gray-400 hover:text-red-500 flex items-center gap-1 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            Signaler
        </button>
        @else
        <span class="text-xs text-red-400 font-medium flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                <path
                    d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z" />
            </svg>
            Signalé
        </span>
        @endif
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
            <div class="flex items-start gap-4 {{ $isMine ? 'flex-row-reverse' : '' }} group/msg"
                x-data="{{ Js::from(['isEditing' => false, 'showDeleteModal' => false, 'body' => $message->body]) }}">
                <div class="message-bubble relative">
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

                    @if($message->is_deleted)
                        <div class="px-4 py-2.5 rounded-2xl bg-gray-50 border border-gray-100 text-gray-400 italic text-sm">
                            <svg class="w-3.5 h-3.5 inline mb-0.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Ce message a été supprimé
                        </div>
                    @else
                        <!-- Message Actions (Desktop hover) -->
                        @if($isMine)
                        <div class="absolute -left-28 top-0 flex items-center justify-end gap-2 opacity-0 group-hover/msg:opacity-100 transition-all duration-200">
                            <button @click="isEditing = true" class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition shadow-sm bg-white/90 backdrop-blur-sm border border-gray-100" title="Modifier">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                            </button>
                            <button @click="showDeleteModal = true" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition shadow-sm bg-white/90 backdrop-blur-sm border border-gray-100" title="Supprimer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                        @endif

                        <!-- Native-free Delete Confirmation Modal -->
                        <template x-teleport="body">
                            <div x-show="showDeleteModal" 
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0"
                                 x-transition:enter-end="opacity-100"
                                 x-transition:leave="transition ease-in duration-200"
                                 x-transition:leave-start="opacity-100"
                                 x-transition:leave-end="opacity-0"
                                 class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gray-900/40 backdrop-blur-sm"
                                 x-cloak>
                                <div @click.away="showDeleteModal = false" 
                                     x-show="showDeleteModal"
                                     x-transition:enter="transition ease-out duration-300"
                                     x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave="transition ease-in duration-200"
                                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                                     class="bg-white rounded-3xl shadow-2xl max-w-sm w-full p-6 text-center border border-gray-100">
                                    <div class="w-16 h-16 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </div>
                                    <h3 class="text-xl font-bold text-gray-900 mb-2">Supprimer le message ?</h3>
                                    <p class="text-gray-500 mb-8 leading-relaxed">Cette action est définitive. Le contenu du message sera effacé pour tout le monde.</p>
                                    <div class="flex flex-col gap-3">
                                        <form action="{{ route('jeune.messages.destroy', $message) }}" method="POST" class="w-full">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-full py-3.5 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-2xl transition shadow-lg shadow-red-200">
                                                Oui, supprimer
                                            </button>
                                        </form>
                                        <button @click="showDeleteModal = false" class="w-full py-3.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-2xl transition">
                                            Annuler
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Edit Form -->
                        <div x-show="isEditing" x-cloak class="w-full min-w-[200px]">
                            <form action="{{ route('jeune.messages.update', $message) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <textarea name="body" x-model="body" rows="3"
                                    class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm p-3"></textarea>
                                <div class="flex justify-end gap-2 mt-2">
                                    <button type="button" @click="isEditing = false" class="text-xs text-gray-500 hover:text-gray-700 font-medium">Annuler</button>
                                    <button type="submit" class="text-xs bg-indigo-600 text-white px-3 py-1.5 rounded-lg hover:bg-indigo-700 transition font-medium">Enregistrer</button>
                                </div>
                            </form>
                        </div>

                        <!-- Content -->
                        <div x-show="!isEditing">
                            @if($message->body)
                                <div class="px-4 py-2.5 rounded-2xl {{ $isMine ? 'bg-indigo-600 text-white rounded-br-sm' : 'bg-gray-100 text-gray-800 rounded-bl-sm' }}">
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
                                </a>
                            @endif

                            <p class="text-[11px] mt-1 {{ $isMine ? 'text-right text-indigo-300' : 'text-gray-400' }}">
                                {{ $message->created_at->format('d/m H:i') }}
                                @if($message->edited_at)
                                    · <span class="italic text-[10px] opacity-80">modifié</span>
                                @endif
                                @if($isMine && $message->read_at)
                                    · <span class="text-emerald-400">Lu</span>
                                @endif
                            </p>
                        </div>
                    @endif

                    @if(! $isMine)
                        </div>
                    </div>
                    @endif
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
            <form action="{{ route('jeune.messages.store', $mentorship) }}" method="POST" enctype="multipart/form-data"
                x-data="{ fileName: '' }">
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

            <!-- Information de sécurité -->
            <div class="mt-4 flex items-start gap-2 p-3 bg-indigo-50/50 rounded-xl border border-indigo-100/50">
                <svg class="w-4 h-4 text-indigo-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-[10px] leading-normal text-indigo-600/70 italic">
                    Pour assurer la sécurité et le respect des échanges, l'équipe Brillio peut avoir accès aux
                    discussions.
                    Merci de maintenir des échanges cordiaux et professionnels et éviter de transmettre des informations
                    personnelles.
                </p>
            </div>
        </div>
    </div>

    <!-- Modal de Signalement -->
    <div x-show="isReportModalOpen" @keydown.escape.window="isReportModalOpen = false"
        class="fixed inset-0 z-[60] overflow-y-auto" x-cloak>
        <div class="flex items-center justify-center min-h-screen p-4 text-center sm:block sm:p-0">
            <div x-show="isReportModalOpen" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0" @click="isReportModalOpen = false"
                class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="isReportModalOpen" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">

                <form method="post" action="{{ route('jeune.messages.report', $mentorship) }}" class="p-6">
                    @csrf
                    <h2 class="text-lg font-medium text-gray-900">Signaler cette conversation</h2>
                    <p class="mt-1 text-sm text-gray-600">
                        Veuillez expliquer pourquoi vous signalez cet échange. Un administrateur Brillio examinera la
                        conversation pour garantir votre sécurité.
                    </p>
                    <div class="mt-6">
                        <label for="reason" class="block text-sm font-medium text-gray-700 mb-1">Motif du
                            signalement</label>
                        <textarea id="reason" name="reason" rows="4"
                            class="block w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm sm:text-sm p-3"
                            placeholder="Décrivez le problème ici..." required></textarea>
                    </div>
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" @click="isReportModalOpen = false"
                            class="px-4 py-2 bg-white border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                            Annuler
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-red-600 border border-transparent rounded-xl text-sm font-medium text-white hover:bg-red-700 transition">
                            Confirmer le signalement
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    const chatArea = document.getElementById('chat-area');
    if (chatArea) chatArea.scrollTop = chatArea.scrollHeight;
</script>
@endpush