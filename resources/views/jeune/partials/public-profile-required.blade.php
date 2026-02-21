@if(!auth()->user()->jeuneProfile?->is_public)
<div class="bg-amber-50 border-l-4 border-amber-400 p-6 rounded-2xl mb-8 shadow-sm">
    <div class="flex flex-col md:flex-row gap-6 items-start md:items-center justify-between">
        <div class="flex gap-4">
            <div class="flex-shrink-0 pt-1">
                <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center text-amber-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
            </div>
            <div>
                <h3 class="text-lg font-bold text-amber-900">Action requise : Ton profil n'est pas encore public</h3>
                <p class="mt-1 text-sm text-amber-700 leading-relaxed">
                    Pour accéder à tes mentors, tes séances et ton calendrier, ton profil doit être visible.
                    Cela permet aux mentors de mieux te connaître et assure une relation de confiance et de sécurité sur
                    la plateforme.
                </p>
                <div class="mt-3 flex flex-wrap gap-2">
                    @php
                    $missing = auth()->user()->missing_profile_fields;
                    @endphp
                    @if(count($missing) > 0)
                    <span class="text-xs font-semibold text-amber-800 uppercase tracking-wider">A compléter :</span>
                    @foreach($missing as $field)
                    <span
                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800 border border-amber-200">
                        {{ $field }}
                    </span>
                    @endforeach
                    @endif
                </div>
            </div>
        </div>
        <div class="flex-shrink-0 w-full md:w-auto">
            <form action="{{ route('jeune.profile.publish') }}" method="POST">
                @csrf
                <button type="submit"
                    class="w-full md:w-auto inline-flex items-center justify-center px-6 py-3 border border-transparent text-sm font-bold rounded-xl text-white bg-amber-600 hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 transition shadow-sm group">
                    Publier mon profil maintenant
                    <svg class="ml-2 -mr-1 w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </button>
            </form>
        </div>
    </div>
</div>
@endif