@extends('layouts.admin')

@section('title', 'Mentors')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Mentors</h1>
            <p class="text-gray-600">Gérez les profils des mentors</p>
        </div>
        <form method="GET" action="{{ route('admin.mentors.index') }}" class="flex flex-wrap items-center gap-3">
            <div class="relative">
                <label for="mentor_search_input" class="sr-only">Rechercher par nom de mentor</label>
                <input type="text" id="mentor_search_input" name="search" value="{{ request('search') }}"
                    placeholder="Rechercher par nom de mentor..."
                    class="w-64 pl-9 pr-3 py-2 rounded-lg border border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>

            <div>
                <label for="mentor_status_select" class="sr-only">Filtrer par statut</label>
                <select id="mentor_status_select" name="status" onchange="this.form.submit()"
                    class="rounded-lg border-gray-300 text-sm py-2 focus:ring-indigo-500 focus:border-indigo-500 {{ auth()->user()->isCoach() ? 'hidden' : '' }}">
                    <option value="">Tous les statuts</option>
                    <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Publiés</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Brouillons</option>
                </select>
            </div>

            <div>
                <label for="mentor_specialization_select" class="sr-only">Filtrer par spécialisation</label>
                <select id="mentor_specialization_select" name="specialization" onchange="this.form.submit()"
                    class="rounded-lg border-gray-300 text-sm py-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Toutes spécialisations</option>
                    @foreach($specializations as $key => $label)
                    <option value="{{ $key }}" {{ request('specialization') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit"
                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition shadow-sm">
                Filtrer
            </button>

            @if(request()->hasAny(['search', 'mentor_name', 'status', 'specialization', 'type']))
            <a href="{{ route('admin.mentors.index') }}"
                class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg text-sm font-medium transition" title="Réinitialiser">
                Réinitialiser
            </a>
            @endif
        </form>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        @if(!auth()->user()->isCoach())
        <div class="bg-white rounded-xl p-4 shadow-sm">
            <div class="text-sm text-gray-500">Total mentors</div>
            <div class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</div>
        </div>
        @endif
        <div class="bg-white rounded-xl p-4 shadow-sm">
            <div class="text-sm text-gray-500">Profils publiés</div>
            <div class="text-2xl font-bold text-green-600">{{ $stats['published'] }}</div>
        </div>
        @if(!auth()->user()->isCoach())
        <div class="bg-white rounded-xl p-4 shadow-sm">
            <div class="text-sm text-gray-500">Brouillons</div>
            <div class="text-2xl font-bold text-yellow-600">{{ $stats['draft'] }}</div>
        </div>
        @endif
        <div class="bg-white rounded-xl p-4 shadow-sm">
            <div class="text-sm text-gray-500">Étapes parcours</div>
            <div class="text-2xl font-bold text-blue-600">{{ $stats['total_steps'] }}</div>
        </div>
    </div>

    <!-- Liste des mentors -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Mentor</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Poste</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Spécialisation</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expérience</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Parcours</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($mentors as $mentor)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="h-10 w-10 flex-shrink-0">
                                @if($mentor->user->avatar_url)
                                <img class="h-10 w-10 rounded-full object-cover" src="{{ $mentor->user->avatar_url }}"
                                    alt="{{ $mentor->user->name }}"
                                    onerror="this.onerror=null; this.parentElement.innerHTML='<span class=\'text-orange-600 font-semibold\'>{{ strtoupper(substr($mentor->user->name, 0, 1)) }}</span>';">
                                @else
                                <div class="h-10 w-10 rounded-full bg-orange-100 flex items-center justify-center">
                                    <span class="text-orange-600 font-semibold">
                                        {{ strtoupper(substr($mentor->user->name, 0, 1)) }}
                                    </span>
                                </div>
                                @endif
                            </div>
                            <div class="ml-4">
                                <div class="font-medium text-gray-900">{{ $mentor->user->name }}</div>
                                <div class="text-sm text-gray-500">{{ $mentor->user->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-900">{{ $mentor->current_position ?? '-' }}</div>
                        <div class="text-sm text-gray-500">{{ $mentor->current_company ?? '' }}</div>
                    </td>
                    <td class="px-6 py-4">
                        @if($mentor->specializationModel && $mentor->specializationModel->status === 'approved')
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                            {{ $mentor->specializationModel->name }}
                        </span>
                        @elseif($mentor->specializationModel)
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800"
                            title="En attente de validation">
                            {{ $mentor->specializationModel->name }}
                        </span>
                        @elseif($mentor->specialization)
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                            {{ $specializations[$mentor->specialization] ?? $mentor->specialization }}
                        </span>
                        @else
                        <span class="text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">
                        {{ $mentor->years_of_experience ? $mentor->years_of_experience . ' ans' : '-' }}
                    </td>
                    <td class="px-6 py-4">
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            {{ $mentor->roadmapSteps->count() }} étapes
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        @if($mentor->is_published)
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            Publié
                        </span>
                        @else
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            Brouillon
                        </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex justify-end gap-2">
                            @if(!auth()->user()->isCoach())
                            <a href="{{ route('admin.mentors.edit', $mentor) }}"
                                class="text-indigo-600 hover:text-indigo-800" title="Modifier">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </a>
                            @endif
                            <a href="{{ route('admin.mentors.show', $mentor) }}"
                                class="text-blue-600 hover:text-blue-800" title="Voir le profil complet">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </a>
                            @if(!auth()->user()->isCoach())
                            <form action="{{ route('admin.mentors.toggle-publish', $mentor) }}" method="POST"
                                class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="text-gray-600 hover:text-gray-800"
                                    title="{{ $mentor->is_published ? 'Dépublier' : 'Publier' }}">
                                    @if($mentor->is_published)
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                    </svg>
                                    @else
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    @endif
                                </button>
                            </form>
                            <form action="{{ route('admin.mentors.demote', $mentor) }}" method="POST" class="inline"
                                onsubmit="return confirm('Rétrograder ce mentor en étudiant ? Son compte sera archivé et il recevra une notification.')">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="text-red-500 hover:text-red-700"
                                    title="Rétrograder en Jeune">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                    </svg>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                        Aucun mentor trouvé
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Pagination -->
        @if($mentors->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $mentors->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection