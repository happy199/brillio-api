@extends('layouts.organization')

@section('title', 'Nos Mentors')

@section('content')
<div x-data="creditDistribution({ 
    totalUsers: {{ (int)$mentors->total() }}, 
    balance: {{ (int)$organization->credits_balance }},
    distributeUrl: '{{ route('organization.credits.distribute') }}',
    csrfToken: '{{ csrf_token() }}'
})" class="space-y-6">
    <!-- Header -->
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Nos Mentors</h1>
            <p class="mt-2 text-sm text-gray-700">
                Liste des {{ $mentors->total() }} mentors liés à votre organisation ou accompagnant vos jeunes.
            </p>
        </div>
        @if (auth()->user()->organization_role !== 'viewer')
        <div class="mt-4 sm:mt-0 flex gap-3">
            <a href="{{ route('organization.invitations.create', ['role' => 'mentor']) }}"
                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-organization-600 hover:bg-organization-700 focus:outline-none focus:ring-offset-2 focus:ring-organization-500">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Inviter des mentors
            </a>
        </div>
        @endif
    </div>

    @if(!$organization->isPro())
    <!-- Subscription Teaser -->
    <div class="bg-gradient-to-r from-organization-600 to-indigo-700 rounded-2xl shadow-xl overflow-hidden text-white">
        <div class="px-8 py-10 md:px-12 md:py-12 flex flex-col md:flex-row items-center justify-between gap-8">
            <div class="max-w-xl text-center md:text-left">
                <h2 class="text-3xl font-extrabold mb-4">Débloquez la gestion des mentors</h2>
                <p class="text-organization-100 text-lg mb-6 leading-relaxed">
                    Avec le plan <strong>Pro</strong>, suivez les séances de mentorat, analysez l'impact de vos mentors
                    sur vos jeunes et distribuez des crédits pour encourager l'accompagnement.
                </p>
                <div class="flex flex-wrap gap-4 justify-center md:justify-start">
                    <a href="{{ route('organization.subscription.index') }}"
                        class="px-8 py-4 bg-white text-organization-700 font-bold rounded-xl hover:bg-organization-50 transition-all shadow-lg hover:shadow-xl active:scale-95">
                        Passer à l'offre Pro
                    </a>
                </div>
            </div>
            <div class="flex-shrink-0 relative group">
                <div class="absolute -inset-4 bg-white/20 rounded-full blur-2xl group-hover:bg-white/30 transition-all">
                </div>
                <svg class="h-48 w-48 text-white/90 relative drop-shadow-2xl" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </div>
        </div>
    </div>
    @endif

    <!-- Search & Filters -->
    <div class="bg-white shadow rounded-lg p-6">
        <form action="{{ route('organization.mentors.index') }}" method="GET"
            class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
            <!-- Search -->
            <div class="sm:col-span-4">
                <label for="search" class="block text-sm font-medium text-gray-700">Recherche</label>
                <div class="mt-1 relative rounded-md shadow-sm">
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                        placeholder="Nom, email, spécialisation..."
                        class="focus:ring-organization-500 focus:border-organization-500 block w-full pl-3 sm:text-sm border-gray-300 rounded-md">
                </div>
            </div>

            <!-- Submit -->
            <div class="sm:col-span-2 flex items-end">
                <button type="submit"
                    class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-organization-600 hover:bg-organization-700 focus:outline-none focus:ring-organization-500">
                    Filtrer
                </button>
            </div>
        </form>
    </div>

    <!-- Tabs -->
    <div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <a href="{{ route('organization.mentors.index', ['type' => 'internal']) }}"
                class="{{ $type === 'internal' ? 'border-organization-500 text-organization-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center">
                Mentors Internes
                @if($type === 'internal')
                <span
                    class="ml-2 py-0.5 px-2.5 rounded-full text-xs font-medium bg-organization-100 text-organization-600">
                    {{ $mentors->total() }}
                </span>
                @endif
            </a>
            <a href="{{ route('organization.mentors.index', ['type' => 'external']) }}"
                class="{{ $type === 'external' ? 'border-organization-500 text-organization-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center">
                Mentors Externes
                @if($type === 'external')
                <span
                    class="ml-2 py-0.5 px-2.5 rounded-full text-xs font-medium bg-organization-100 text-organization-600">
                    {{ $mentors->total() }}
                </span>
                @endif
            </a>
        </nav>
    </div>

    <!-- Mentor Grid -->
    <div
        class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 {{ !$organization->isPro() ? 'filter blur-sm pointer-events-none' : '' }}">
        @forelse($mentors as $mentor)
        <div class="bg-white overflow-hidden shadow rounded-lg hover:shadow-md transition-shadow flex flex-col">
            <div class="p-6 flex-grow">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        @if($mentor->avatar_url)
                        <img class="h-12 w-12 rounded-full object-cover" src="{{ $mentor->avatar_url }}"
                            alt="{{ $mentor->name }}">
                        @else
                        <div
                            class="h-12 w-12 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-lg">
                            {{ substr($mentor->name, 0, 1) }}
                        </div>
                        @endif
                    </div>
                    <div class="ml-4 flex-1">
                        <h3 class="text-lg font-medium text-gray-900 truncate">{{ $mentor->name }}</h3>
                        <p class="text-xs text-gray-500 truncate">{{ $mentor->mentorProfile->current_position ??
                            'Mentor' }}</p>
                    </div>
                </div>

                <div class="mt-4 border-t border-gray-100 pt-4 space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Entreprise</span>
                        <span class="font-medium text-gray-900 truncate max-w-[150px]">{{
                            $mentor->mentorProfile->current_company ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Spécialisation</span>
                        <span class="font-medium text-gray-900 truncate max-w-[150px]">{{
                            $mentor->mentorProfile->specialization_label ?? '-' }}</span>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 space-y-3">
                @if($type === 'internal' && auth()->user()->organization_role !== 'viewer')
                <button @click="openModal('single', '{{ $mentor->id }}', '{{ addslashes($mentor->name) }}')"
                    class="w-full inline-flex items-center justify-center px-4 py-2 border border-organization-600 rounded-lg text-sm font-bold text-organization-600 bg-white hover:bg-organization-50 transition-colors">
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Offrir des crédits
                </button>
                @endif
                <a href="{{ route('organization.mentors.show', $mentor) }}"
                    class="text-sm font-medium text-gray-600 hover:text-gray-900 flex items-center justify-center transition-colors">
                    Détails & Séances
                    <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-12 bg-white rounded-lg shadow">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun mentor trouvé</h3>
            <p class="mt-1 text-sm text-gray-500">Commencez par inviter des mentors via un lien d'invitation.</p>
        </div>
        @endforelse
    </div>

    @if($organization->isPro())
    <!-- Pagination -->
    <div class="mt-6">
        {{ $mentors->links() }}
    </div>
    @endif

    <!-- Credit Distribution Modal (Same as Users) -->
    @include('organization.users._credit_modal')
</div>
@endsection

@push('scripts')
@include('organization.users._credit_script')
@endpush