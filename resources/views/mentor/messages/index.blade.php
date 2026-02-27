@extends('layouts.mentor')

@section('title', 'Messagerie')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Messagerie</h1>
        <p class="text-sm text-gray-500 mt-1">Échangez avec vos mentés</p>
    </div>

    @if($mentorships->isEmpty())
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-12 text-center">
        <div class="w-16 h-16 bg-orange-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
            </svg>
        </div>
        <h2 class="text-lg font-semibold text-gray-900 mb-2">Aucune conversation</h2>
        <p class="text-gray-500 text-sm">Vous n'avez pas encore de mentés actifs.</p>
    </div>
    @else
    <div class="space-y-3">
        @foreach($mentorships as $mentorship)
        @php
        $lastMessage = $mentorship->messages->first();
        $unread = $unreadCounts[$mentorship->id] ?? 0;
        $mentee = $mentorship->mentee;
        @endphp
        <a href="{{ route('mentor.messages.show', $mentorship) }}"
            class="flex items-center gap-4 bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md hover:border-orange-200 transition group">
            <!-- Avatar -->
            <div class="relative flex-shrink-0">
                @if($mentee->avatar_url)
                <img src="{{ $mentee->avatar_url }}" alt="{{ $mentee->name }}"
                    class="w-12 h-12 rounded-full object-cover">
                @else
                <div
                    class="w-12 h-12 rounded-full bg-gradient-to-br from-primary-400 to-purple-500 flex items-center justify-center">
                    <span class="text-white font-bold">{{ strtoupper(substr($mentee->name, 0, 1)) }}</span>
                </div>
                @endif
                @if($unread > 0)
                <span
                    class="absolute -top-1 -right-1 w-5 h-5 bg-orange-500 text-white text-xs font-bold rounded-full flex items-center justify-center">
                    {{ $unread }}
                </span>
                @endif
            </div>

            <!-- Infos -->
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-gray-900 group-hover:text-orange-600 transition truncate">
                        {{ $mentee->name }}
                    </h3>
                    @if($lastMessage)
                    <span class="text-xs text-gray-400 flex-shrink-0 ml-2">
                        {{ $lastMessage->created_at->diffForHumans() }}
                    </span>
                    @endif
                </div>
                <p class="text-sm text-gray-500 truncate mt-0.5">
                    @if($lastMessage)
                    @if($lastMessage->body)
                    {{ Str::limit($lastMessage->body, 60) }}
                    @else
                    📎 {{ $lastMessage->attachment_name }}
                    @endif
                    @else
                    <span class="italic">Commencer la conversation</span>
                    @endif
                </p>
            </div>

            <svg class="w-5 h-5 text-gray-300 group-hover:text-orange-400 flex-shrink-0" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </a>
        @endforeach
    </div>
    @endif
</div>
@endsection