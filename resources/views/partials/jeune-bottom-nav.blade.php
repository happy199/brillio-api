@php
    $unreadCount = \App\Models\Message::whereHas('mentorship', fn($q) =>
        $q->where('mentee_id', auth()->id())->where('status', 'accepted'))
        ->where('sender_id', '!=', auth()->id())->whereNull('read_at')->count();
@endphp

<!-- Bottom Navigation Bar pour Mobile & PWA (Figma-Harmonized) -->
<nav class="md:hidden fixed bottom-0 left-0 right-0 z-40 pwa-bottom-dock pb-[calc(env(safe-area-inset-bottom)+0.25rem)]" aria-label="Navigation principale mobile">
    <div class="grid grid-cols-5 h-16 max-w-md mx-auto items-center px-1">
        <!-- Accueil -->
        <a href="{{ route('jeune.dashboard') }}"
           class="flex flex-col items-center justify-center py-1 transition-all active:scale-90 {{ request()->routeIs('jeune.dashboard') ? 'text-indigo-600 font-bold' : 'text-slate-500 hover:text-slate-900' }}">
            <svg class="w-5 h-5 {{ request()->routeIs('jeune.dashboard') ? 'stroke-[2.5]' : 'stroke-2' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <span class="text-[10px] mt-0.5 tracking-tight">Accueil</span>
            @if(request()->routeIs('jeune.dashboard'))
                <span class="pwa-nav-dot"></span>
            @endif
        </a>

        <!-- Orientation IA -->
        <a href="{{ route('jeune.chat') }}"
           class="flex flex-col items-center justify-center py-1 transition-all active:scale-90 {{ request()->routeIs('jeune.chat', 'jeune.personality') ? 'text-indigo-600 font-bold' : 'text-slate-500 hover:text-slate-900' }}">
            <div class="relative">
                <svg class="w-5 h-5 {{ request()->routeIs('jeune.chat', 'jeune.personality') ? 'stroke-[2.5]' : 'stroke-2' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                <span class="absolute -top-1 -right-1.5 flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-purple-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-purple-600"></span>
                </span>
            </div>
            <span class="text-[10px] mt-0.5 tracking-tight">Orientation</span>
            @if(request()->routeIs('jeune.chat', 'jeune.personality'))
                <span class="pwa-nav-dot"></span>
            @endif
        </a>

        <!-- Mentors -->
        <a href="{{ route('jeune.mentors') }}"
           class="flex flex-col items-center justify-center py-1 transition-all active:scale-90 {{ request()->routeIs('jeune.mentors', 'jeune.mentorship.*') ? 'text-indigo-600 font-bold' : 'text-slate-500 hover:text-slate-900' }}">
            <svg class="w-5 h-5 {{ request()->routeIs('jeune.mentors', 'jeune.mentorship.*') ? 'stroke-[2.5]' : 'stroke-2' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            <span class="text-[10px] mt-0.5 tracking-tight">Mentors</span>
            @if(request()->routeIs('jeune.mentors', 'jeune.mentorship.*'))
                <span class="pwa-nav-dot"></span>
            @endif
        </a>

        <!-- Messages -->
        <a href="{{ route('jeune.messages.index') }}"
           class="flex flex-col items-center justify-center py-1 transition-all active:scale-90 relative {{ request()->routeIs('jeune.messages.*') ? 'text-indigo-600 font-bold' : 'text-slate-500 hover:text-slate-900' }}">
            <div class="relative">
                <svg class="w-5 h-5 {{ request()->routeIs('jeune.messages.*') ? 'stroke-[2.5]' : 'stroke-2' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                @if($unreadCount > 0)
                    <span class="absolute -top-1 -right-2 bg-purple-600 text-white text-[9px] font-extrabold px-1 rounded-full min-w-[15px] h-[15px] flex items-center justify-center">
                        {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                    </span>
                @endif
            </div>
            <span class="text-[10px] mt-0.5 tracking-tight">Messages</span>
            @if(request()->routeIs('jeune.messages.*'))
                <span class="pwa-nav-dot"></span>
            @endif
        </a>

        <!-- Profil -->
        <a href="{{ route('jeune.profile') }}"
           class="flex flex-col items-center justify-center py-1 transition-all active:scale-90 {{ request()->routeIs('jeune.profile', 'jeune.wallet.*') ? 'text-indigo-600 font-bold' : 'text-slate-500 hover:text-slate-900' }}">
            <div class="w-5 h-5 rounded-full overflow-hidden flex items-center justify-center ring-1.5 {{ request()->routeIs('jeune.profile', 'jeune.wallet.*') ? 'ring-indigo-600' : 'ring-slate-300' }}">
                @if(auth()->user()->avatar_url)
                    <img src="{{ auth()->user()->avatar_url }}" alt="" class="w-full h-full object-cover">
                @else
                    <span class="text-[9px] font-bold text-slate-700">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                @endif
            </div>
            <span class="text-[10px] mt-0.5 tracking-tight">Profil</span>
            @if(request()->routeIs('jeune.profile', 'jeune.wallet.*'))
                <span class="pwa-nav-dot"></span>
            @endif
        </a>
    </div>
</nav>
