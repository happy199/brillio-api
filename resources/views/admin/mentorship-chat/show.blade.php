@extends('layouts.admin')

@section('title', 'Détail Conversation Mentorat')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center bg-white p-4 rounded-xl shadow-sm">
        <div class="flex items-center space-x-4">
            <a href="{{ route('admin.mentorship-chat.index') }}" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-gray-900">Conversation de Mentorat</h1>
                <p class="text-sm text-gray-600">
                    Mentor: <span class="font-semibold">{{ $mentorship->mentor->name }}</span> |
                    Jeune: <span class="font-semibold">{{ $mentorship->mentee->name }}</span>
                </p>
            </div>
        </div>
        <div class="flex items-center space-x-2">
            @if($mentorship->messages->where('is_flagged', true)->count() > 0)
            <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs font-bold animate-pulse">
                {{ $mentorship->messages->where('is_flagged', true)->count() }} messages signalés
            </span>
            @endif
            <span
                class="px-3 py-1 {{ $mentorship->status === 'accepted' ? 'bg-emerald-100 text-emerald-700' : 'bg-blue-100 text-blue-700' }} rounded-full text-xs font-bold">
                Statut: {{ $mentorship->status === 'accepted' ? 'Mentorat accepté' : ucfirst($mentorship->status) }}
            </span>
        </div>
    </div>

    @if($mentorship->reported_at)
    <!-- Report Details -->
    <div class="bg-red-50 border border-red-200 p-5 rounded-2xl shadow-sm mb-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center text-red-600 flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div>
                <h3 class="text-base font-bold text-red-900 uppercase tracking-tight">Conversation Signalée Manuellement
                </h3>
                <p class="text-xs text-red-600 font-medium">Attention requise : Un utilisateur a signalé un comportement
                    inapproprié dans cet échange.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-white/50 p-4 rounded-xl border border-red-100/50">
            <div class="space-y-3">
                <div class="flex flex-col">
                    <span class="text-[10px] uppercase font-bold text-red-400 tracking-wider">Signalé par</span>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-semibold text-gray-900">{{ $mentorship->reporter->name ??
                            'Utilisateur' }}</span>
                        @if($mentorship->reporter)
                        <span
                            class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $mentorship->reporter->user_type === 'mentor' ? 'bg-orange-100 text-orange-700' : 'bg-indigo-100 text-indigo-700' }}">
                            {{ strtoupper($mentorship->reporter->user_type) }}
                        </span>
                        @endif
                    </div>
                </div>
                <div class="flex flex-col">
                    <span class="text-[10px] uppercase font-bold text-red-400 tracking-wider">Date du signalement</span>
                    <span class="text-sm font-semibold text-gray-900">{{ $mentorship->reported_at->format('d/m/Y H:i')
                        }}</span>
                </div>
            </div>
            <div class="flex flex-col">
                <span class="text-[10px] uppercase font-bold text-red-400 tracking-wider">Motif détaillé</span>
                <p
                    class="text-sm text-gray-700 leading-relaxed italic bg-red-100/30 p-3 rounded-lg border border-red-200/50 mt-1">
                    "{{ $mentorship->report_reason ?? 'Aucun motif fourni' }}"
                </p>
            </div>
            <div class="md:col-span-2 flex justify-end pt-2">
                <form action="{{ route('admin.mentorship-chat.clear-report', $mentorship) }}" method="POST"
                    onsubmit="return confirm('Voulez-vous vraiment classer ce signalement ? Cela enlèvera l\'alerte sur cette conversation.')">
                    @csrf
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-white border border-red-200 text-red-700 text-xs font-bold uppercase tracking-widest rounded-xl hover:bg-red-50 transition-colors shadow-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                        Classer le signalement
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Chat Messages -->
    <div class="bg-gray-50 rounded-xl shadow-inner border border-gray-200 min-h-[500px] flex flex-col">
        <div class="flex-1 p-6 space-y-6 overflow-y-auto max-h-[700px]">
            @forelse($mentorship->messages as $message)
            <div class="flex {{ $message->sender_id == $mentorship->mentor_id ? 'justify-start' : 'justify-end' }}">
                <div class="max-w-[70%] space-y-1">
                    <!-- Sender Label -->
                    <div class="flex items-center space-x-2 px-1">
                        <span class="text-xs font-bold text-gray-500">
                            {{ $message->sender_id == $mentorship->mentor_id ? 'MENTOR: ' . $mentorship->mentor->name :
                            'JEUNE: ' . $mentorship->mentee->name }}
                        </span>
                        <span class="text-[10px] text-gray-400 font-normal">
                            {{ $message->created_at->format('d/m/Y H:i') }}
                        </span>
                    </div>

                    <!-- Message Body -->
                    <div
                        class="p-4 rounded-2xl shadow-sm {{ $message->sender_id == $mentorship->mentor_id ? 'bg-white text-gray-800' : 'bg-indigo-600 text-white' }} {{ $message->is_flagged ? 'border-2 border-red-500 ring-2 ring-red-200' : '' }} {{ $message->is_deleted ? 'opacity-50' : '' }}">
                        
                        @if($message->is_deleted)
                            <div class="mb-2 p-2 bg-gray-100 text-gray-500 text-xs rounded italic">
                                <i class="fas fa-trash-alt mr-1"></i> MESSAGE SUPPRIMÉ PAR L'UTILISATEUR
                            </div>
                        @endif

                        @if($message->body)
                            @if($message->is_flagged)
                                <div class="mb-2 p-2 bg-red-50 border-l-4 border-red-500 text-red-700 text-xs rounded">
                                    <p class="font-bold mb-1"><i class="fas fa-exclamation-triangle mr-1"></i> CONTENU SIGNALÉ :
                                        {{ $message->flag_reason }}</p>
                                    <p class="italic">Ceci est la version originale (non masquée pour l'admin)</p>
                                </div>
                                <p class="whitespace-pre-wrap font-mono bg-red-50/50 p-2 rounded">{{ $message->original_body ??
                                    $message->body }}</p>
                                <div
                                    class="mt-2 pt-2 border-t border-red-100 text-[10px] opacity-75 flex justify-between items-center">
                                    <span>Version masquée pour les utilisateurs: {{ Str::limit($message->body, 50) }}</span>
                                    <form action="{{ route('admin.mentorship-chat.unflag-message', $message) }}" method="POST"
                                        onsubmit="return confirm('Voulez-vous vraiment lever le signalement sur ce message ? Le texte original sera restauré pour le jeune et le mentor.')">
                                        @csrf
                                        <button type="submit"
                                            class="text-red-700 hover:text-red-900 font-bold uppercase transition-colors">
                                            Lever le signalement
                                        </button>
                                    </form>
                                </div>
                            @else
                                <p class="whitespace-pre-wrap">{{ $message->body }}</p>
                            @endif
                        @endif

                        @if($message->edited_at)
                            <div class="mt-1 text-[10px] opacity-60 italic">
                                (Modifié le {{ $message->edited_at->format('d/m H:i') }})
                            </div>
                        @endif

                        @if($message->hasAttachment())
                        <div
                            class="mt-2 pt-2 border-t {{ $message->sender_id == $mentorship->mentor_id ? 'border-gray-100' : 'border-indigo-400 text-indigo-50' }}">
                            <a href="{{ route('jeune.messages.download', $message) }}" target="_blank"
                                class="flex items-center text-xs hover:underline">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                                {{ $message->attachment_name }}
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="flex flex-col items-center justify-center h-full text-gray-400 space-y-2">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                    </path>
                </svg>
                <p>Aucun message dans cette conversation.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection