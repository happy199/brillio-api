@extends('layouts.jeune')

@section('title', 'Assistant IA')

@section('content')
    <div class="h-[calc(100vh-12rem)] flex flex-col" x-data="chatApp()">
        <!-- Chat Header -->
        <div class="bg-white rounded-t-2xl border-b p-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div
                    class="w-10 h-10 bg-gradient-to-br from-primary-500 to-secondary-500 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <h2 class="font-bold text-gray-900">Assistant Brillio</h2>
                    <p class="text-xs text-green-500 flex items-center gap-1">
                        <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                        En ligne
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <!-- Conversations Dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open"
                        class="flex items-center gap-2 px-3 py-2 hover:bg-gray-100 rounded-lg transition">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                        </svg>
                        <span class="text-sm text-gray-700 hidden sm:inline">Conversations (<span
                                x-text="conversations.length"></span>/2)</span>
                    </button>
                    <div x-show="open" @click.outside="open = false" x-cloak
                        class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-lg border py-2 z-10 max-h-96 overflow-y-auto">
                        <div class="px-4 py-2 border-b">
                            <h3 class="font-bold text-gray-900">Mes conversations</h3>
                        </div>
                        <div class="p-2 space-y-2">
                            <template x-for="conv in conversations" :key="conv.id">
                                <div class="relative group">
                                    <button @click="loadConversation(conv.id); open = false"
                                        :class="conv.id === currentConversationId ? 'bg-primary-50 border-primary-200' : 'hover:bg-gray-50'"
                                        class="w-full text-left p-3 rounded-xl border transition">
                                        <p class="font-medium text-gray-900 truncate pr-8"
                                            x-text="conv.title || 'Conversation'"></p>
                                        <p class="text-xs text-gray-500" x-text="formatDate(conv.updated_at)"></p>
                                    </button>
                                    <button @click="deleteConversation(conv.id, $event)"
                                        class="absolute top-3 right-3 p-1.5 hover:bg-red-50 rounded-lg transition opacity-0 group-hover:opacity-100"
                                        title="Supprimer">
                                        <svg class="w-4 h-4 text-gray-400 hover:text-red-500" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </template>
                            <template x-if="conversations.length === 0">
                                <p class="text-sm text-gray-500 text-center py-4">Aucune conversation</p>
                            </template>
                        </div>
                    </div>
                </div>

                <button @click="startNewConversation()" class="p-2 hover:bg-gray-100 rounded-lg transition"
                    title="Nouvelle conversation">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </button>
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="p-2 hover:bg-gray-100 rounded-lg transition">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                        </svg>
                    </button>
                    <div x-show="open" @click.outside="open = false" x-cloak
                        class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border py-2 z-10">
                        <button @click="requestHumanSupport(); open = false"
                            class="w-full px-4 py-2 text-left text-sm hover:bg-gray-50 flex items-center gap-2">
                            <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            Demander un humain
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Conversations Sidebar (Mobile Drawer) -->
        <div x-show="showHistory" x-cloak class="fixed inset-0 z-40 lg:hidden"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="absolute inset-0 bg-black/50" @click="showHistory = false"></div>
            <div class="absolute left-0 top-0 bottom-0 w-80 bg-white shadow-xl p-4 overflow-y-auto"
                x-transition:enter="transition ease-out duration-300" x-transition:enter-start="-translate-x-full"
                x-transition:enter-end="translate-x-0">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-gray-900">Mes conversations</h3>
                    <button @click="showHistory = false" class="p-2 hover:bg-gray-100 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="space-y-2">
                    <template x-for="conv in conversations" :key="conv.id">
                        <div class="relative">
                            <button @click="loadConversation(conv.id); showHistory = false"
                                :class="conv.id === currentConversationId ? 'bg-primary-50 border-primary-200' : 'hover:bg-gray-50'"
                                class="w-full text-left p-3 rounded-xl border transition">
                                <p class="font-medium text-gray-900 truncate pr-8" x-text="conv.title || 'Conversation'">
                                </p>
                                <p class="text-xs text-gray-500" x-text="formatDate(conv.updated_at)"></p>
                            </button>
                            <button @click="deleteConversation(conv.id, $event)"
                                class="absolute top-3 right-3 p-1 hover:bg-red-50 rounded-lg transition group"
                                title="Supprimer">
                                <svg class="w-4 h-4 text-gray-400 group-hover:text-red-500" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Messages Area -->
        <div class="flex-1 bg-gray-50 overflow-y-auto p-4 space-y-4" id="messagesContainer">
            <!-- Welcome Message -->
            <template x-if="messages.length === 0">
                <div class="text-center py-12">
                    <div
                        class="w-20 h-20 bg-gradient-to-br from-primary-100 to-secondary-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Salut ! Je suis ton assistant Brillio</h3>
                    <p class="text-gray-600 max-w-md mx-auto mb-8">
                        Je suis la pour t'aider dans ton orientation professionnelle. Pose-moi tes questions sur les
                        metiers, les formations ou ton avenir !
                    </p>
                    <div class="flex flex-wrap justify-center gap-3">
                        <button @click="sendSuggestion('Quels metiers correspondent a mon profil MBTI ?')"
                            class="px-4 py-2 bg-white border rounded-full text-sm hover:border-primary-500 hover:text-primary-600 transition">
                            Mon profil MBTI
                        </button>
                        <button @click="sendSuggestion('Quelles etudes faire pour devenir developpeur ?')"
                            class="px-4 py-2 bg-white border rounded-full text-sm hover:border-primary-500 hover:text-primary-600 transition">
                            Etudes developpeur
                        </button>
                        <button @click="sendSuggestion('Comment trouver un stage au Senegal ?')"
                            class="px-4 py-2 bg-white border rounded-full text-sm hover:border-primary-500 hover:text-primary-600 transition">
                            Trouver un stage
                        </button>
                    </div>
                </div>
            </template>

            <!-- Messages -->
            <template x-for="(message, index) in messages" :key="index">
                <div :class="message.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
                    <div :class="message.role === 'user' ? 'bg-primary-500 text-white rounded-2xl rounded-br-md' : 'bg-white border rounded-2xl rounded-bl-md'"
                        class="max-w-[80%] p-4 shadow-sm">
                        <template x-if="message.role === 'assistant'">
                            <div class="flex items-start gap-3">
                                <div
                                    class="w-8 h-8 bg-gradient-to-br from-primary-500 to-secondary-500 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div class="prose prose-sm" x-html="formatMessage(message.content)"></div>
                            </div>
                        </template>
                        <template x-if="message.role === 'user'">
                            <p x-text="message.content"></p>
                        </template>
                    </div>
                </div>
            </template>

            <!-- Typing Indicator -->
            <div x-show="isTyping" class="flex justify-start">
                <div class="bg-white border rounded-2xl rounded-bl-md p-4 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-8 h-8 bg-gradient-to-br from-primary-500 to-secondary-500 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div class="flex gap-1">
                            <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"
                                style="animation-delay: 0ms"></span>
                            <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"
                                style="animation-delay: 150ms"></span>
                            <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"
                                style="animation-delay: 300ms"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Input Area -->
        <div class="bg-white rounded-b-2xl border-t p-4">
            <form @submit.prevent="sendMessage()" class="flex items-end gap-3">
                <button type="button" @click="showHistory = true"
                    class="lg:hidden p-3 hover:bg-gray-100 rounded-xl transition">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                    </svg>
                </button>
                <div class="flex-1 relative">
                    <textarea x-model="newMessage" @keydown.enter.prevent="if (!$event.shiftKey) sendMessage()"
                        placeholder="Ecris ton message..." rows="1"
                        class="w-full px-4 py-3 border rounded-xl resize-none focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                        :disabled="isTyping"></textarea>
                </div>
                <button type="submit" :disabled="!newMessage.trim() || isTyping"
                    class="p-3 bg-gradient-to-r from-primary-500 to-secondary-500 text-white rounded-xl hover:shadow-lg transition disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                </button>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            function chatApp() {
                return {
                    messages: [],
                    conversations: @json($conversations ?? []),
                    currentConversationId: {{ $currentConversation->id ?? 'null' }},
                    newMessage: '',
                    isTyping: false,
                    showHistory: false,

                    init() {
                        // Load current conversation messages if exists
                        @if(isset($currentConversation) && $currentConversation->messages)
                            this.messages = @json($currentConversation->messages->map(fn($m) => ['role' => $m->role, 'content' => $m->content]));
                        @endif

                                            // Check for prefilled message from URL params
                                            const urlParams = new URLSearchParams(window.location.search);
                        const prefillMessage = urlParams.get('prefill');
                        if (prefillMessage) {
                            this.newMessage = decodeURIComponent(prefillMessage);
                            // Clean URL without reloading
                            window.history.replaceState({}, document.title, window.location.pathname);
                        }

                        this.scrollToBottom();
                    },

                    async sendMessage() {
                        if (!this.newMessage.trim() || this.isTyping) return;

                        const userMessage = this.newMessage.trim();
                        this.messages.push({ role: 'user', content: userMessage });
                        this.newMessage = '';
                        this.isTyping = true;
                        this.scrollToBottom();

                        try {
                            const response = await fetch('{{ route("jeune.chat.send") }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                },
                                body: JSON.stringify({
                                    message: userMessage,
                                    conversation_id: this.currentConversationId
                                })
                            });

                            const data = await response.json();

                            if (data.success) {
                                this.messages.push({ role: 'assistant', content: data.message });
                                if (data.conversation_id) {
                                    this.currentConversationId = data.conversation_id;
                                }
                            } else {
                                this.messages.push({ role: 'assistant', content: data.error || 'Desole, une erreur est survenue. Reessaie plus tard.' });
                            }
                        } catch (error) {
                            console.error('Error:', error);
                            this.messages.push({ role: 'assistant', content: 'Desole, une erreur est survenue. Verifie ta connexion.' });
                        }

                        this.isTyping = false;
                        this.scrollToBottom();
                    },

                    sendSuggestion(text) {
                        this.newMessage = text;
                        this.sendMessage();
                    },

                    async startNewConversation() {
                        // Vérifier la limite avant de créer
                        if (this.conversations.length >= 2) {
                            alert('Tu as atteint la limite de 2 conversations. Supprime une conversation existante pour en créer une nouvelle.');
                            return;
                        }
                        this.messages = [];
                        this.currentConversationId = null;
                    },

                    async loadConversation(id) {
                        try {
                            const response = await fetch(`/espace-jeune/chat/${id}`);
                            const data = await response.json();
                            if (data.success && data.messages) {
                                this.messages = data.messages;
                                this.currentConversationId = id;
                                this.scrollToBottom();
                            }
                        } catch (error) {
                            console.error('Error loading conversation:', error);
                        }
                    },

                    async deleteConversation(id, event) {
                        event.stopPropagation();

                        if (!confirm('Es-tu sûr(e) de vouloir supprimer cette conversation ? Tous les messages seront perdus.')) {
                            return;
                        }

                        try {
                            const response = await fetch(`/espace-jeune/chat/${id}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                }
                            });

                            const data = await response.json();
                            if (data.success) {
                                // Retirer la conversation de la liste
                                this.conversations = this.conversations.filter(c => c.id !== id);

                                // Si c'était la conversation active, la réinitialiser
                                if (this.currentConversationId === id) {
                                    this.messages = [];
                                    this.currentConversationId = null;
                                }
                            }
                        } catch (error) {
                            console.error('Error deleting conversation:', error);
                            alert('Erreur lors de la suppression de la conversation.');
                        }
                    },

                    async requestHumanSupport() {
                        if (!this.currentConversationId) {
                            alert('Envoie d\'abord un message pour demarrer une conversation.');
                            return;
                        }
                        try {
                            await fetch(`/espace-jeune/chat/${this.currentConversationId}/request-human`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                }
                            });
                            alert('Ta demande d\'assistance humaine a ete envoyee. Un conseiller te repondra bientot.');
                        } catch (error) {
                            console.error('Error:', error);
                        }
                    },

                    formatMessage(content) {
                        // Simple markdown-like formatting
                        return content
                            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                            .replace(/\*(.*?)\*/g, '<em>$1</em>')
                            .replace(/\n/g, '<br>');
                    },

                    formatDate(dateString) {
                        const date = new Date(dateString);
                        return date.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' });
                    },

                    scrollToBottom() {
                        this.$nextTick(() => {
                            const container = document.getElementById('messagesContainer');
                            if (container) {
                                container.scrollTop = container.scrollHeight;
                            }
                        });
                    }
                }
            }
        </script>
    @endpush
@endsection