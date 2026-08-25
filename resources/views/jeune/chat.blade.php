@extends('layouts.jeune')

@php
    $videoCallAdvisorCost = $videoCallAdvisorCost ?? \App\Models\SystemSetting::getValue('feature_cost_video_call_advisor', 50);
@endphp

@section('title', 'Orientation')

@section('content')
    <div class="h-[calc(100vh-12rem)] flex flex-col" x-data="chatApp()">
        <!-- Chat Header -->
        <div class="bg-white rounded-t-2xl border-b p-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-primary-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <h2 class="font-bold text-gray-900">Assistant {{ isset($current_organization) ? $current_organization->name : 'Brillio' }}</h2>
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
                                x-text="conversations.length"></span>)</span>
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

                <!-- Advisor Support Button -->
                <template x-if="currentConversationId">
                    <div class="flex items-center gap-2">
                        <!-- Request Button (Inactive) -->
                        <template x-if="!needsHumanSupport && !isHumanSupportActive">
                            <button @click="requestHumanSupport()"
                                class="hidden md:flex items-center gap-2 px-3 py-2 bg-indigo-50 text-indigo-700 border border-indigo-200 rounded-xl text-[11px] font-bold hover:bg-indigo-100 transition-all duration-300">
                                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                <span>Parler à un conseiller d'orientation expert de mon pays</span>
                                <span class="bg-indigo-600 text-white px-1.5 py-0.5 rounded-full text-[9px] font-bold">10C</span>
                            </button>
                        </template>

                        <!-- Cancel Button (Pending) -->
                        <template x-if="needsHumanSupport && !isHumanSupportActive">
                            <button @click="cancelHumanSupport()"
                                class="flex items-center gap-2 px-3 py-2 bg-red-50 text-red-600 border border-red-200 rounded-xl text-[11px] font-bold hover:bg-red-100 transition-all duration-300">
                                <div class="relative flex h-2 w-2">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                                </div>
                                <span>Annuler la demande de conseil</span>
                            </button>
                        </template>

                        <!-- Active Support Indicator & Video Call Button -->
                        <template x-if="isHumanSupportActive">
                            <div class="flex items-center gap-2">
                                <div class="flex items-center gap-2 px-3 py-2 bg-green-50 text-green-700 border border-green-200 rounded-xl text-[11px] font-bold italic">
                                    <span class="relative flex h-2 w-2">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                                    </span>
                                    Conseiller en ligne
                                </div>
                                <form :action="'/chat/conversations/' + currentConversationId + '/propose-video-call'" method="POST" class="inline">
                                    @csrf
                                    <button type="submit"
                                        onclick="return confirm('Proposer et lancer un appel vidéo d\'orientation pour {{ $videoCallAdvisorCost }} crédits ?')"
                                        class="flex items-center gap-1.5 px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-[11px] font-bold shadow-sm transition-all duration-300">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                        <span>Appel vidéo</span>
                                        <span class="bg-indigo-800 text-white px-1.5 py-0.5 rounded-full text-[9px] font-bold">{{ $videoCallAdvisorCost }}C</span>
                                    </button>
                                </form>
                            </div>
                        </template>
                    </div>
                </template>

                <button @click="startNewConversation()" class="p-2 hover:bg-gray-100 rounded-lg transition relative"
                    title="Nouvelle conversation">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <template x-if="conversations.length >= 1">
                        <span class="absolute -top-1 -right-1 bg-indigo-100 text-indigo-700 text-[9px] px-1.5 py-0.5 rounded-full font-bold border border-indigo-200">10C</span>
                    </template>
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
                        <template x-if="!needsHumanSupport && !isHumanSupportActive">
                            <button @click="requestHumanSupport(); open = false"
                                class="w-full px-4 py-2 text-left text-sm hover:bg-gray-50 flex items-center justify-between gap-2 md:hidden">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                    <span>Demander un conseiller</span>
                                </div>
                                <span class="bg-indigo-600 text-white px-1.5 py-0.5 rounded-full text-[9px] font-bold">10C</span>
                            </button>
                        </template>
                        <button @click="deleteConversation(currentConversationId); open = false"
                            class="w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Supprimer la conversation
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
                    <div class="w-20 h-20 bg-primary-50 rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Salut ! Je suis ton assistant {{ isset($current_organization) ? $current_organization->name : 'Brillio' }}</h3>
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
                        <button @click="sendSuggestion('Comment trouver un stage {{ $location }} ?')"
                            class="px-4 py-2 bg-white border rounded-full text-sm hover:border-primary-500 hover:text-primary-600 transition">
                            Trouver un stage
                        </button>
                    </div>
                </div>
            </template>

            <!-- Messages -->
            <template x-for="(message, index) in messages" :key="index">
                <div class="w-full">
                    <!-- System Message / Divider -->
                    <template x-if="message.is_system_message">
                        <div class="flex items-center gap-4 my-8">
                            <div class="flex-1 h-px bg-gray-100"></div>
                            <div class="max-w-[80%] px-4 py-2 bg-slate-50 rounded-lg border border-slate-100 shadow-sm text-[10px] font-bold text-slate-500 uppercase tracking-widest text-center leading-relaxed" x-text="message.content"></div>
                            <div class="flex-1 h-px bg-gray-100"></div>
                        </div>
                    </template>

                    <!-- Standard Message -->
                    <template x-if="!message.is_system_message">
                        <div :class="message.role === 'user' ? 'flex justify-end' : 'flex justify-start'" class="mb-4">
                            <div :class="message.role === 'user'
                                    ? 'bg-primary-500 text-white rounded-2xl rounded-br-md shadow-md'
                                    : (message.is_from_human
                                        ? 'bg-amber-50 border-amber-200 border rounded-2xl rounded-bl-md shadow-sm'
                                        : 'bg-white border rounded-2xl rounded-bl-md shadow-sm')"
                                class="max-w-[85%] sm:max-w-[80%] p-4 transition-all duration-300">

                                <template x-if="message.role === 'assistant'">
                                    <div class="flex items-start gap-3">
                                        <div :class="message.is_from_human ? 'bg-amber-500' : 'bg-primary-600'"
                                            class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 shadow-sm">
                                            <template x-if="!message.is_from_human">
                                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                                </svg>
                                            </template>
                                            <template x-if="message.is_from_human">
                                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                            </template>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 mb-1.5">
                                                <span class="text-[10px] font-bold uppercase tracking-wider"
                                                    :class="message.is_from_human ? 'text-amber-700' : 'text-primary-700'"
                                                    x-text="message.is_from_human ? (message.sender_name || 'Coach Partner') : 'Assistant {{ isset($current_organization) ? str_replace("'", "\'", $current_organization->name) : 'Brillio' }}'"></span>
                                                <template x-if="message.is_from_human">
                                                    <span class="bg-amber-100 text-amber-700 text-[9px] px-1.5 py-0.5 rounded-full font-bold border border-amber-200">CONSEILLER PÉDAGOGIQUE</span>
                                                </template>
                                            </div>
                                            <div class="prose prose-sm prose-slate max-w-none text-gray-800 leading-relaxed" x-html="formatMessage(message.content)"></div>

                                            <!-- Carte d'appel vidéo (Accepted) -->
                                            <template x-if="message.advisor_video_call && (message.advisor_video_call.status === 'accepted' || message.advisor_video_call.status === 'completed')">
                                                <div class="mt-3 bg-gradient-to-br from-indigo-900 to-indigo-800 text-white p-4 rounded-2xl shadow-lg border border-indigo-700 space-y-2">
                                                    <div class="flex items-center gap-2 text-indigo-200 text-xs font-bold uppercase tracking-wider">
                                                        <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                                        </svg>
                                                        Entretien vidéo avec le conseiller
                                                    </div>
                                                    <p class="text-xs text-indigo-100">Votre séance de visioconférence est prête. Cliquez pour rejoindre la réunion dans un nouvel onglet.</p>
                                                    <a :href="'/advisor-meeting/' + message.advisor_video_call.meeting_id"
                                                       target="_blank"
                                                       rel="noopener"
                                                       class="inline-flex items-center gap-2 px-4 py-2 bg-white text-indigo-900 hover:bg-indigo-50 font-bold rounded-xl text-xs shadow-md transition-all">
                                                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                        </svg>
                                                        Rejoindre l'appel vidéo
                                                    </a>
                                                </div>
                                            </template>

                                            <!-- Carte d'appel vidéo (Pending Proposal) -->
                                            <template x-if="message.advisor_video_call && message.advisor_video_call.status === 'pending_acceptance'">
                                                <div class="mt-3 bg-gradient-to-br from-amber-50 to-orange-50 text-amber-950 p-4 rounded-2xl border border-amber-200 shadow-sm space-y-3">
                                                    <div class="flex items-center gap-2 text-amber-800 text-xs font-bold uppercase tracking-wider">
                                                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                                        </svg>
                                                        Proposition d'appel vidéo
                                                    </div>
                                                    <p class="text-xs text-amber-900 font-medium">
                                                        Le conseiller vous propose de passer en appel vidéo pour approfondir votre orientation (<span x-text="message.advisor_video_call.credits_cost || {{ $videoCallAdvisorCost }}"></span> crédits).
                                                    </p>
                                                    <div class="flex flex-wrap items-center gap-2 pt-1">
                                                        <form :action="'/chat/video-calls/' + message.advisor_video_call.id + '/accept'" method="POST" class="inline">
                                                            @csrf
                                                            <button type="submit"
                                                                class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow transition-colors inline-flex items-center gap-1">
                                                                <span>Accepter</span>
                                                                <span class="bg-emerald-800 text-white px-1.5 py-0.5 rounded-full text-[9px]" x-text="(message.advisor_video_call.credits_cost || {{ $videoCallAdvisorCost }}) + 'C'"></span>
                                                            </button>
                                                        </form>
                                                        <form :action="'/chat/video-calls/' + message.advisor_video_call.id + '/refuse'" method="POST" class="inline">
                                                            @csrf
                                                            <button type="submit"
                                                                class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold rounded-xl text-xs transition-colors">
                                                                Refuser
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>

                                <template x-if="message.role === 'user'">
                                    <p class="text-[15px] leading-relaxed" x-text="message.content"></p>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            <!-- Typing Indicator -->
            <div x-show="isTyping" class="flex justify-start">
                <div class="bg-white border rounded-2xl rounded-bl-md p-4 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-primary-600 rounded-lg flex items-center justify-center">
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
                    <textarea x-model="newMessage"
                        @input="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'"
                        @keydown.enter.prevent="if (!$event.shiftKey) sendMessage()"
                        x-init="$nextTick(() => { $el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px' })"
                        placeholder="Ecris ton message..." rows="1"
                        class="w-full px-4 py-3 border rounded-xl resize-none focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all duration-200"
                        style="min-height: 48px; max-height: 200px;"
                        :disabled="isTyping"></textarea>
                </div>
                <button type="submit" :disabled="!newMessage.trim() || isTyping"
                    class="p-3 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                </button>
            </form>
        </div>
    </div>

    @push('scripts')
        <script nonce="{{ request()->attributes->get('csp_nonce') }}">
            function chatApp() {
                return {
                    messages: [],
                    conversations: @json($conversations ?? []),
                    currentConversationId: {{ $currentConversation->id ?? 'null' }},
                    needsHumanSupport: {{ ($currentConversation && $currentConversation->needs_human_support) ? 'true' : 'false' }},
                    isHumanSupportActive: {{ ($currentConversation && $currentConversation->human_support_active) ? 'true' : 'false' }},
                    newMessage: '',
                    isTyping: false,
                    showHistory: false,
                    pollTimer: null,

                    init() {
                        // Load current conversation messages if exists
                        @if(isset($currentConversation) && $currentConversation->messages)
                            @php
                                $advisorCallsMap = \App\Models\AdvisorVideoCall::where('conversation_id', $currentConversation->id)->get()->keyBy('id');
                                $orgName = isset($current_organization) ? $current_organization->name : 'Brillio';
                            @endphp
                            this.messages = {!! json_encode($currentConversation->messages->map(function($m) use ($advisorCallsMap, $orgName) {
                                $callData = null;
                                if (preg_match('/\[ADVISOR_VIDEO_CALL:(\d+)\]/', $m->content, $matches)) {
                                    $c = $advisorCallsMap->get($matches[1]) ?? \App\Models\AdvisorVideoCall::find($matches[1]);
                                    if ($c) {
                                        $callData = [
                                            'id' => $c->id,
                                            'status' => $c->status,
                                            'credits_cost' => $c->credits_cost,
                                            'meeting_id' => $c->meeting_id,
                                            'initiated_by' => $c->initiated_by,
                                        ];
                                    }
                                }
                                return [
                                    'id' => $m->id,
                                    'role' => $m->role,
                                    'content' => $m->content,
                                    'is_from_human' => (bool)$m->is_from_human,
                                    'is_system_message' => (bool)$m->is_system_message,
                                    'sender_name' => $m->is_from_human ? ($m->admin?->name ?? 'Conseiller') : 'Assistant ' . $orgName,
                                    'advisor_video_call' => $callData,
                                ];
                            })->toArray()) !!};
                        @endif

                        // Check for prefilled message from URL params
                        const urlParams = new URLSearchParams(window.location.search);
                        const prefillMessage = urlParams.get('prefill') || urlParams.get('message');
                        if (prefillMessage) {
                            this.newMessage = decodeURIComponent(prefillMessage);
                            // Clean URL without reloading
                            window.history.replaceState({}, document.title, window.location.pathname);
                        }

                        this.scrollToBottom();
                        this.startPolling();

                        document.addEventListener('visibilitychange', () => {
                            if (document.visibilityState === 'visible') {
                                this.pollMessages();
                            }
                        });
                    },

                    startPolling() {
                        if (this.pollTimer) clearInterval(this.pollTimer);
                        this.pollTimer = setInterval(() => {
                            this.pollMessages();
                        }, 2500);
                    },

                    async pollMessages() {
                        if (!this.currentConversationId || this.isTyping) return;
                        try {
                            const response = await fetch(`/espace-jeune/chat/${this.currentConversationId}`);
                            const data = await response.json();
                            if (data.success && data.messages) {
                                const currentSig = JSON.stringify(this.messages.map(m => ({ id: m.id, status: m.advisor_video_call?.status })));
                                const newSig = JSON.stringify(data.messages.map(m => ({ id: m.id, status: m.advisor_video_call?.status })));
                                if (currentSig !== newSig) {
                                    const previousLength = this.messages.length;
                                    this.messages = data.messages;
                                    if (data.needs_human_support !== undefined) this.needsHumanSupport = data.needs_human_support;
                                    if (data.is_human_support_active !== undefined) this.isHumanSupportActive = data.is_human_support_active;
                                    if (data.messages.length > previousLength) {
                                        this.scrollToBottom();
                                    }
                                }
                            }
                        } catch (e) {
                            console.error('Polling error:', e);
                        }
                    },

                    async sendMessage() {
                        if (!this.newMessage.trim() || this.isTyping) return;

                        const userMessage = this.newMessage.trim();
                        this.messages.push({ role: 'user', content: userMessage });
                        this.newMessage = '';

                        // Reset textarea height
                        const textarea = document.querySelector('textarea[x-model="newMessage"]');
                        if (textarea) {
                            textarea.style.height = '48px';
                        }

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
                                 if (data.message) {
                                     this.messages.push({
                                         role: 'assistant',
                                         content: data.message,
                                         is_from_human: data.is_from_human || false,
                                         sender_name: data.sender_name || 'Assistant {{ isset($current_organization) ? str_replace("'", "\'", $current_organization->name) : 'Brillio' }}'
                                     });
                                 }
                                if (data.conversation_id) {
                                    // S'il s'agit d'une nouvelle conversation, l'ajouter à la liste
                                    if (!this.currentConversationId) {
                                        this.conversations.unshift({
                                            id: data.conversation_id,
                                            title: data.conversation_title || 'Nouvelle conversation',
                                            updated_at: new Date().toISOString()
                                        });
                                    }
                                    this.currentConversationId = data.conversation_id;
                                }

                                // Update support status if provided
                                if (data.needs_human_support !== undefined) {
                                    this.needsHumanSupport = data.needs_human_support;
                                }
                                if (data.is_human_support_active !== undefined) {
                                    this.isHumanSupportActive = data.is_human_support_active;
                                }
                            } else {
                                if (data.redirect_to_wallet) {
                                    if (confirm(data.error + "\n\nVoulez-vous être redirigé vers votre portefeuille pour recharger ?")) {
                                        const target = data.wallet_url;
                                        if (target && (target.startsWith('/') || target.startsWith(window.location.origin))) {
                                            window.location.href = target;
                                        } else {
                                            window.location.href = '/jeune/portefeuille';
                                        }
                                        return;
                                    }
                                }
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

                                // Sync support flags
                                if (data.needs_human_support !== undefined) {
                                    this.needsHumanSupport = data.needs_human_support;
                                }
                                if (data.is_human_support_active !== undefined) {
                                    this.isHumanSupportActive = data.is_human_support_active;
                                }

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
                                    this.needsHumanSupport = false;
                                    this.isHumanSupportActive = false;
                                }
                            }
                        } catch (error) {
                            console.error('Error deleting conversation:', error);
                        }
                    },

                    async requestHumanSupport() {
                        if (!this.currentConversationId) return;

                        try {
                            const response = await fetch(`/espace-jeune/chat/${this.currentConversationId}/request-human`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                }
                            });

                             const data = await response.json();
                             if (data.success) {
                                 this.needsHumanSupport = true;
                                 await this.loadConversation(this.currentConversationId);
                              } else if (data.redirect_to_wallet) {
                                  const target = data.wallet_url;
                                  if (target && (target.startsWith('/') || target.startsWith(window.location.origin))) {
                                      window.location.href = target;
                                  } else {
                                      window.location.href = '/jeune/portefeuille';
                                  }
                             } else {
                                 alert(data.error || 'Une erreur est survenue.');
                             }
                        } catch (error) {
                            console.error('Error requesting human support:', error);
                        }
                    },

                    async cancelHumanSupport() {
                        if (!this.currentConversationId) return;

                        try {
                            const response = await fetch(`/espace-jeune/chat/${this.currentConversationId}/cancel-human`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                }
                            });

                            const data = await response.json();
                            if (data.success) {
                                this.needsHumanSupport = false;
                                await this.loadConversation(this.currentConversationId);
                            }
                        } catch (error) {
                            console.error('Error cancelling human support:', error);
                        }
                    },

                    formatMessage(content) {
                        if (!content) return '';

                        // Nettoyer la balise technique [ADVISOR_VIDEO_CALL:X] du texte affiché
                        let cleanText = content.replace(/\[ADVISOR_VIDEO_CALL:\d+\]/g, '').trim();

                        // Détecter si le message contient un lien d'enregistrement vidéo
                        let videoCardHtml = '';
                        const videoLinkMatch = cleanText.match(/(?:🎥\s*Enregistrement vidéo[^\n]*?\[[^\]]+\]\(([^\)]+)\)|https?:\/\/[^\s\)]+video_recordings[^\s\)]+|\/storage\/video_recordings[^\s\)]+|https?:\/\/res\.cloudinary\.com[^\s\)]+video[^\s\)]+)/i);

                        if (videoLinkMatch) {
                            const videoUrl = videoLinkMatch[1] || videoLinkMatch[0];
                            videoCardHtml = `
                                <div class="my-3 bg-gradient-to-br from-indigo-900 via-slate-900 to-purple-950 text-white p-4 rounded-xl shadow-lg border border-indigo-500/30 space-y-3 not-prose">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-indigo-500/20 flex items-center justify-center border border-indigo-400/30">
                                            <svg class="w-5 h-5 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-xs text-indigo-200 uppercase tracking-wider">Enregistrement de l'Entretien Vidéo</h4>
                                            <p class="text-[11px] text-gray-300">Vidéo disponible pour consultation et réécoute</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 pt-1">
                                        <button type="button" onclick="window.openVideoPlayerModal('${videoUrl}')" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg text-xs transition shadow cursor-pointer">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                            Visionner la vidéo
                                        </button>
                                        <a href="${videoUrl}" download target="_blank" class="inline-flex items-center gap-1.5 px-3 py-2 bg-white/10 hover:bg-white/20 text-gray-200 font-semibold rounded-lg text-xs transition cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                            Télécharger
                                        </a>
                                    </div>
                                </div>
                            `;

                            // Nettoyer la phrase brute du message pour éviter le texte doublon
                            cleanText = cleanText.replace(/🎥\s*Enregistrement vidéo[^\n]*?(\[[^\]]+\]\([^\)]+\)|\/storage[^\s]+|https?:\/\/[^\s]+)/gi, '').trim();
                        }

                        // Nettoyage des balises HTML non désirées
                        let formatted = cleanText.replace(/<\/?[^>]+(>|$)/g, "");

                        // Rendu Markdown simplifié
                        formatted = formatted
                            .replace(/^### (.*$)/gim, '<h3 class="font-bold text-gray-900 mt-3 mb-1">$1</h3>')
                            .replace(/^## (.*$)/gim, '<h2 class="font-bold text-gray-900 mt-4 mb-2">$1</h2>')
                            .replace(/^# (.*$)/gim, '<h1 class="font-bold text-gray-900 mt-5 mb-3">$1</h1>')
                            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                            .replace(/\*(.*?)\*/g, '<em>$1</em>')
                            .replace(/^\s*[\-\*]\s+(.*)$/gim, '<div class="flex gap-2"><span>•</span><span>$1</span></div>')
                            .replace(/^\s*(\d+\.)\s+(.*)$/gim, '<div class="flex gap-2"><span>$1</span><span>$2</span></div>')
                            .replace(/\n/g, '<br>');

                        return (formatted ? `<p>${formatted}</p>` : '') + videoCardHtml;
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

        <!-- Modal Lecteur Vidéo Global -->
        <dialog id="videoPlayerModal" class="fixed inset-0 z-50 p-0 m-0 w-full h-full max-w-none max-h-none bg-transparent backdrop:bg-gray-900/80 hidden border-0 overflow-y-auto" aria-labelledby="jeune-video-modal-title">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <button type="button" aria-label="Fermer le lecteur vidéo" class="fixed inset-0 w-full h-full bg-gray-900/80 backdrop-blur-sm transition-opacity border-0 cursor-default" onclick="window.closeVideoPlayerModal()"></button>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="relative inline-block align-bottom bg-gray-900 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full border border-gray-800 z-10">
                    <div class="bg-gray-900 px-6 py-4 flex justify-between items-center border-b border-gray-800">
                        <h3 id="jeune-video-modal-title" class="text-sm font-bold text-white flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-red-500 animate-pulse"></span>
                            Lecture de l'Enregistrement Vidéo
                        </h3>
                        <button type="button" onclick="window.closeVideoPlayerModal()" class="text-gray-400 hover:text-white transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="p-4 bg-black flex justify-center items-center">
                        <video id="modalVideoElement" controls autoplay class="w-full max-h-[75vh] rounded-lg shadow-2xl">
                            <source id="modalVideoSource" src="" type="video/webm">
                            <track kind="captions" src="" srclang="fr" label="Français">
                        </video>
                    </div>
                    <div class="bg-gray-900 px-6 py-3 flex justify-end">
                        <button type="button" onclick="window.closeVideoPlayerModal()" class="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-white rounded-lg text-xs font-bold transition">
                            Fermer
                        </button>
                    </div>
                </div>
            </div>
        </dialog>

        <script>
            window.openVideoPlayerModal = function(url) {
                const modal = document.getElementById('videoPlayerModal');
                const video = document.getElementById('modalVideoElement');
                const source = document.getElementById('modalVideoSource');
                if (modal && video && source) {
                    source.src = url;
                    video.load();
                    modal.classList.remove('hidden');
                    if (typeof modal.showModal === 'function' && !modal.open) {
                        modal.showModal();
                    }
                    video.play().catch(e => console.log('Autoplay handled:', e));
                }
            };

            window.closeVideoPlayerModal = function() {
                const modal = document.getElementById('videoPlayerModal');
                const video = document.getElementById('modalVideoElement');
                if (modal && video) {
                    video.pause();
                    modal.classList.add('hidden');
                    if (typeof modal.close === 'function' && modal.open) {
                        modal.close();
                    }
                }
            };
        </script>
    @endpush
@endsection