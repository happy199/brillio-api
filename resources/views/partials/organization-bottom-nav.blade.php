<!-- Bottom Navigation Bar pour Mobile (Espace Organisation) -->
<nav class="md:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-lg border-t border-gray-200/80 shadow-[0_-4px_25px_rgba(0,0,0,0.06)] pb-[env(safe-area-inset-bottom)]" aria-label="Navigation principale mobile organisation">
    <div class="grid grid-cols-5 h-16 max-w-lg mx-auto">
        <!-- Dashboard -->
        <a href="{{ route('organization.dashboard') }}"
           class="flex flex-col items-center justify-center gap-1 transition-all {{ request()->routeIs('organization.dashboard') ? 'text-organization-600 font-semibold' : 'text-gray-500 hover:text-gray-900' }}">
            <svg class="w-5 h-5 {{ request()->routeIs('organization.dashboard') ? 'stroke-[2.5]' : 'stroke-2' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <span class="text-[10px] tracking-tight">Accueil</span>
        </a>

        <!-- Jeunes -->
        <a href="{{ route('organization.users.index') }}"
           class="flex flex-col items-center justify-center gap-1 transition-all {{ request()->routeIs('organization.users.*') ? 'text-organization-600 font-semibold' : 'text-gray-500 hover:text-gray-900' }}">
            <svg class="w-5 h-5 {{ request()->routeIs('organization.users.*') ? 'stroke-[2.5]' : 'stroke-2' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            <span class="text-[10px] tracking-tight">Jeunes</span>
        </a>

        <!-- Mentors -->
        <a href="{{ route('organization.mentors.index') }}"
           class="flex flex-col items-center justify-center gap-1 transition-all {{ request()->routeIs('organization.mentors.*') ? 'text-organization-600 font-semibold' : 'text-gray-500 hover:text-gray-900' }}">
            <svg class="w-5 h-5 {{ request()->routeIs('organization.mentors.*') ? 'stroke-[2.5]' : 'stroke-2' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            <span class="text-[10px] tracking-tight">Mentors</span>
        </a>

        <!-- Séances / Calendrier -->
        <a href="{{ route('organization.sessions.index') }}"
           class="flex flex-col items-center justify-center gap-1 transition-all {{ request()->routeIs('organization.sessions.*') ? 'text-organization-600 font-semibold' : 'text-gray-500 hover:text-gray-900' }}">
            <svg class="w-5 h-5 {{ request()->routeIs('organization.sessions.*') ? 'stroke-[2.5]' : 'stroke-2' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span class="text-[10px] tracking-tight">Séances</span>
        </a>

        <!-- Profil / Organisation -->
        <a href="{{ route('organization.profile.edit') }}"
           class="flex flex-col items-center justify-center gap-1 transition-all {{ request()->routeIs('organization.profile.*') ? 'text-organization-600 font-semibold' : 'text-gray-500 hover:text-gray-900' }}">
            <div class="w-5 h-5 rounded-full overflow-hidden flex items-center justify-center ring-1 {{ request()->routeIs('organization.profile.*') ? 'ring-organization-600' : 'ring-gray-300' }}">
                @if(auth()->check() && auth()->user()->organization && auth()->user()->organization->logo_url)
                    <img src="{{ auth()->user()->organization->logo_url }}" alt="" class="w-full h-full object-cover">
                @else
                    <span class="text-[9px] font-bold text-gray-700">{{ substr(auth()->user()->name ?? 'O', 0, 1) }}</span>
                @endif
            </div>
            <span class="text-[10px] tracking-tight">Profil</span>
        </a>
    </div>
</nav>
