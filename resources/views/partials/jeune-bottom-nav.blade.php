@php
    $unreadCount = \App\Models\Message::whereHas('mentorship', fn($q) =>
        $q->where('mentee_id', auth()->id())->where('status', 'accepted'))
        ->where('sender_id', '!=', auth()->id())->whereNull('read_at')->count();
@endphp

<!-- Bottom Navigation Bar pour Mobile & PWA (Figma-Harmonized) -->
<nav class="md:hidden fixed bottom-0 left-0 right-0 z-[70] bg-white border-t border-slate-200/90 shadow-[0_-8px_30px_rgba(0,0,0,0.08)] pb-[calc(env(safe-area-inset-bottom)+0.35rem)]"
     style="background-color: #ffffff !important; pointer-events: auto !important; -webkit-tap-highlight-color: transparent;"
     aria-label="Navigation principale mobile">
    <div class="grid grid-cols-5 h-16 max-w-md mx-auto items-stretch px-1">
        <!-- Accueil -->
        <a href="{{ route('jeune.dashboard') }}"
           class="w-full h-full flex flex-col items-center justify-center cursor-pointer transition-colors active:bg-slate-100/80 rounded-xl relative z-20 text-center {{ request()->routeIs('jeune.dashboard') ? 'text-[#5844E3] font-bold' : 'text-slate-500 hover:text-slate-900' }}"
           style="touch-action: manipulation; -webkit-tap-highlight-color: transparent;">
            <svg class="pointer-events-none w-5 h-5 {{ request()->routeIs('jeune.dashboard') ? 'stroke-[2.5]' : 'stroke-2' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <span class="pointer-events-none text-[10px] mt-0.5 tracking-tight font-medium">Accueil</span>
            @if(request()->routeIs('jeune.dashboard'))
                <span class="pointer-events-none w-1 h-1 rounded-full bg-[#5844E3] mt-0.5"></span>
            @endif
        </a>

        <!-- Orientation IA -->
        <a href="{{ route('jeune.chat') }}"
           class="w-full h-full flex flex-col items-center justify-center cursor-pointer transition-colors active:bg-slate-100/80 rounded-xl relative z-20 text-center {{ request()->routeIs('jeune.chat', 'jeune.personality') ? 'text-[#5844E3] font-bold' : 'text-slate-500 hover:text-slate-900' }}"
           style="touch-action: manipulation; -webkit-tap-highlight-color: transparent;">
            <div class="pointer-events-none relative">
                <svg class="w-5 h-5 {{ request()->routeIs('jeune.chat', 'jeune.personality') ? 'stroke-[2.5]' : 'stroke-2' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                <span class="absolute -top-1 -right-1.5 flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-purple-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-purple-600"></span>
                </span>
            </div>
            <span class="pointer-events-none text-[10px] mt-0.5 tracking-tight font-medium">Orientation</span>
            @if(request()->routeIs('jeune.chat', 'jeune.personality'))
                <span class="pointer-events-none w-1 h-1 rounded-full bg-[#5844E3] mt-0.5"></span>
            @endif
        </a>

        <!-- Mentors -->
        <a href="{{ route('jeune.mentors') }}"
           class="w-full h-full flex flex-col items-center justify-center cursor-pointer transition-colors active:bg-slate-100/80 rounded-xl relative z-20 text-center {{ request()->routeIs('jeune.mentors', 'jeune.mentorship.*') ? 'text-[#5844E3] font-bold' : 'text-slate-500 hover:text-slate-900' }}"
           style="touch-action: manipulation; -webkit-tap-highlight-color: transparent;">
            <svg class="pointer-events-none w-5 h-5 {{ request()->routeIs('jeune.mentors', 'jeune.mentorship.*') ? 'stroke-[2.5]' : 'stroke-2' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            <span class="pointer-events-none text-[10px] mt-0.5 tracking-tight font-medium">Mentors</span>
            @if(request()->routeIs('jeune.mentors', 'jeune.mentorship.*'))
                <span class="pointer-events-none w-1 h-1 rounded-full bg-[#5844E3] mt-0.5"></span>
            @endif
        </a>

        <!-- Messages -->
        <a href="{{ route('jeune.messages.index') }}"
           class="w-full h-full flex flex-col items-center justify-center cursor-pointer transition-colors active:bg-slate-100/80 rounded-xl relative z-20 text-center {{ request()->routeIs('jeune.messages.*') ? 'text-[#5844E3] font-bold' : 'text-slate-500 hover:text-slate-900' }}"
           style="touch-action: manipulation; -webkit-tap-highlight-color: transparent;">
            <div class="pointer-events-none relative">
                <svg class="w-5 h-5 {{ request()->routeIs('jeune.messages.*') ? 'stroke-[2.5]' : 'stroke-2' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                @if($unreadCount > 0)
                    <span class="absolute -top-1 -right-2 bg-purple-600 text-white text-[9px] font-extrabold px-1 rounded-full min-w-[15px] h-[15px] flex items-center justify-center">
                        {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                    </span>
                @endif
            </div>
            <span class="pointer-events-none text-[10px] mt-0.5 tracking-tight font-medium">Messages</span>
            @if(request()->routeIs('jeune.messages.*'))
                <span class="pointer-events-none w-1 h-1 rounded-full bg-[#5844E3] mt-0.5"></span>
            @endif
        </a>

        <!-- Profil -->
        <a href="{{ route('jeune.profile') }}"
           class="w-full h-full flex flex-col items-center justify-center cursor-pointer transition-colors active:bg-slate-100/80 rounded-xl relative z-20 text-center {{ request()->routeIs('jeune.profile', 'jeune.wallet.*') ? 'text-[#5844E3] font-bold' : 'text-slate-500 hover:text-slate-900' }}"
           style="touch-action: manipulation; -webkit-tap-highlight-color: transparent;">
            <div class="pointer-events-none w-5 h-5 rounded-full overflow-hidden flex items-center justify-center ring-1.5 {{ request()->routeIs('jeune.profile', 'jeune.wallet.*') ? 'ring-indigo-600' : 'ring-slate-300' }}">
                @if(auth()->user()->avatar_url)
                    <img src="{{ auth()->user()->avatar_url }}" alt="" class="w-full h-full object-cover">
                @else
                    <span class="text-[9px] font-bold text-slate-700">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                @endif
            </div>
            <span class="pointer-events-none text-[10px] mt-0.5 tracking-tight font-medium">Profil</span>
            @if(request()->routeIs('jeune.profile', 'jeune.wallet.*'))
                <span class="pointer-events-none w-1 h-1 rounded-full bg-[#5844E3] mt-0.5"></span>
            @endif
        </a>
    </div>
</nav>

<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    (function() {
        const bottomNav = document.querySelector('nav[aria-label="Navigation principale mobile"]');
        if (bottomNav) {
            // Speculative prefetch au touchstart pour navigation instantanée
            bottomNav.addEventListener('touchstart', function(e) {
                const link = e.target.closest('a[href]');
                if (link && link.href && !link.dataset.prefetched) {
                    const pre = document.createElement('link');
                    pre.rel = 'prefetch';
                    pre.href = link.href;
                    document.head.appendChild(pre);
                    link.dataset.prefetched = 'true';
                }
            }, { passive: true });
        }
    })();
</script>
