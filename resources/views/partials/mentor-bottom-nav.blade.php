@php
    $unreadCount = \App\Models\Message::whereHas('mentorship', fn($q) =>
        $q->where('mentor_id', auth()->id())->where('status', 'accepted'))
        ->where('sender_id', '!=', auth()->id())->whereNull('read_at')->count();
@endphp

<!-- Bottom Navigation Bar pour Mobile & PWA (Espace Mentor - Figma Harmonized) -->
<nav class="md:hidden fixed bottom-0 left-0 right-0 z-[70] bg-white border-t border-slate-200/90 shadow-[0_-8px_30px_rgba(0,0,0,0.08)] pb-[calc(env(safe-area-inset-bottom)+0.35rem)]"
     style="background-color: #ffffff !important; pointer-events: auto !important; -webkit-tap-highlight-color: transparent;"
     aria-label="Navigation principale mobile mentor">
    <div class="grid grid-cols-5 h-16 max-w-md mx-auto items-stretch px-1">
        <!-- Dashboard -->
        <a href="{{ route('mentor.dashboard') }}"
           class="w-full h-full flex flex-col items-center justify-center cursor-pointer transition-colors active:bg-slate-100/80 rounded-xl relative z-20 text-center {{ request()->routeIs('mentor.dashboard') ? 'text-indigo-600 font-bold' : 'text-slate-500 hover:text-slate-900' }}"
           style="touch-action: manipulation; -webkit-tap-highlight-color: transparent;">
            <svg class="pointer-events-none w-5 h-5 {{ request()->routeIs('mentor.dashboard') ? 'stroke-[2.5]' : 'stroke-2' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <span class="pointer-events-none text-[10px] mt-0.5 tracking-tight font-medium">Accueil</span>
            @if(request()->routeIs('mentor.dashboard'))
                <span class="pointer-events-none w-1 h-1 rounded-full bg-indigo-600 mt-0.5"></span>
            @endif
        </a>

        <!-- Mes Mentorés -->
        <a href="{{ route('mentor.mentorship.index') }}"
           class="w-full h-full flex flex-col items-center justify-center cursor-pointer transition-colors active:bg-slate-100/80 rounded-xl relative z-20 text-center {{ request()->routeIs('mentor.mentorship.index', 'mentor.mentorship.requests') ? 'text-indigo-600 font-bold' : 'text-slate-500 hover:text-slate-900' }}"
           style="touch-action: manipulation; -webkit-tap-highlight-color: transparent;">
            <svg class="pointer-events-none w-5 h-5 {{ request()->routeIs('mentor.mentorship.index', 'mentor.mentorship.requests') ? 'stroke-[2.5]' : 'stroke-2' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            <span class="pointer-events-none text-[10px] mt-0.5 tracking-tight font-medium">Mentorés</span>
            @if(request()->routeIs('mentor.mentorship.index', 'mentor.mentorship.requests'))
                <span class="pointer-events-none w-1 h-1 rounded-full bg-indigo-600 mt-0.5"></span>
            @endif
        </a>

        <!-- Séances & Calendrier -->
        <a href="{{ route('mentor.mentorship.calendar') }}"
           class="w-full h-full flex flex-col items-center justify-center cursor-pointer transition-colors active:bg-slate-100/80 rounded-xl relative z-20 text-center {{ request()->routeIs('mentor.mentorship.calendar', 'mentor.mentorship.sessions.*') ? 'text-indigo-600 font-bold' : 'text-slate-500 hover:text-slate-900' }}"
           style="touch-action: manipulation; -webkit-tap-highlight-color: transparent;">
            <svg class="pointer-events-none w-5 h-5 {{ request()->routeIs('mentor.mentorship.calendar', 'mentor.mentorship.sessions.*') ? 'stroke-[2.5]' : 'stroke-2' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span class="pointer-events-none text-[10px] mt-0.5 tracking-tight font-medium">Séances</span>
            @if(request()->routeIs('mentor.mentorship.calendar', 'mentor.mentorship.sessions.*'))
                <span class="pointer-events-none w-1 h-1 rounded-full bg-indigo-600 mt-0.5"></span>
            @endif
        </a>

        <!-- Messages -->
        <a href="{{ route('mentor.messages.index') }}"
           class="w-full h-full flex flex-col items-center justify-center cursor-pointer transition-colors active:bg-slate-100/80 rounded-xl relative z-20 text-center {{ request()->routeIs('mentor.messages.*') ? 'text-indigo-600 font-bold' : 'text-slate-500 hover:text-slate-900' }}"
           style="touch-action: manipulation; -webkit-tap-highlight-color: transparent;">
            <div class="pointer-events-none relative">
                <svg class="w-5 h-5 {{ request()->routeIs('mentor.messages.*') ? 'stroke-[2.5]' : 'stroke-2' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                @if($unreadCount > 0)
                    <span class="absolute -top-1 -right-2 bg-purple-600 text-white text-[9px] font-extrabold px-1 rounded-full min-w-[15px] h-[15px] flex items-center justify-center">
                        {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                    </span>
                @endif
            </div>
            <span class="pointer-events-none text-[10px] mt-0.5 tracking-tight font-medium">Messages</span>
            @if(request()->routeIs('mentor.messages.*'))
                <span class="pointer-events-none w-1 h-1 rounded-full bg-indigo-600 mt-0.5"></span>
            @endif
        </a>

        <!-- Profil -->
        <a href="{{ route('mentor.profile') }}"
           class="w-full h-full flex flex-col items-center justify-center cursor-pointer transition-colors active:bg-slate-100/80 rounded-xl relative z-20 text-center {{ request()->routeIs('mentor.profile') ? 'text-indigo-600 font-bold' : 'text-slate-500 hover:text-slate-900' }}"
           style="touch-action: manipulation; -webkit-tap-highlight-color: transparent;">
            <div class="pointer-events-none w-5 h-5 rounded-full overflow-hidden flex items-center justify-center ring-1.5 {{ request()->routeIs('mentor.profile') ? 'ring-indigo-600' : 'ring-slate-300' }}">
                @if(auth()->user()->avatar_url)
                    <img src="{{ auth()->user()->avatar_url }}" alt="" class="w-full h-full object-cover">
                @else
                    <span class="text-[9px] font-bold text-slate-700">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                @endif
            </div>
            <span class="pointer-events-none text-[10px] mt-0.5 tracking-tight font-medium">Profil</span>
            @if(request()->routeIs('mentor.profile'))
                <span class="pointer-events-none w-1 h-1 rounded-full bg-indigo-600 mt-0.5"></span>
            @endif
        </a>
    </div>
</nav>

<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    (function() {
        const bottomNav = document.querySelector('nav[aria-label="Navigation principale mobile mentor"]');
        if (bottomNav) {
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
