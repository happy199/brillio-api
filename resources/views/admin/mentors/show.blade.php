@extends('layouts.admin')

@section('title', 'Détail Mentor - ' . $mentor->user->name)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.mentors.index') }}" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $mentor->user->name }}</h1>
                <p class="text-gray-600">Profil mentor</p>
            </div>
        </div>
        <div class="flex gap-3">
            <form action="{{ route('admin.mentors.toggle-publish', $mentor) }}" method="POST">
                @csrf
                @method('PATCH')
                <button type="submit" class="px-4 py-2 rounded-lg {{ $mentor->is_published ? 'bg-yellow-100 text-yellow-700 hover:bg-yellow-200' : 'bg-green-100 text-green-700 hover:bg-green-200' }}">
                    {{ $mentor->is_published ? 'Dépublier' : 'Publier' }}
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Profil -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Card utilisateur -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="text-center">
                    @if($mentor->user->profile_photo_path)
                        <img class="h-24 w-24 rounded-full mx-auto object-cover"
                             src="{{ Storage::url($mentor->user->profile_photo_path) }}"
                             alt="{{ $mentor->user->name }}">
                    @else
                        <div class="h-24 w-24 rounded-full bg-orange-100 flex items-center justify-center mx-auto">
                            <span class="text-orange-600 font-bold text-3xl">
                                {{ strtoupper(substr($mentor->user->name, 0, 1)) }}
                            </span>
                        </div>
                    @endif
                    <h3 class="mt-4 text-lg font-semibold text-gray-900">{{ $mentor->user->name }}</h3>
                    <p class="text-gray-500">{{ $mentor->current_position ?? 'Position non renseignée' }}</p>
                    @if($mentor->current_company)
                        <p class="text-sm text-gray-400">{{ $mentor->current_company }}</p>
                    @endif
                </div>

                <div class="mt-6 border-t pt-6 space-y-4">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Email</span>
                        <span class="text-gray-900">{{ $mentor->user->email }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Pays</span>
                        <span class="text-gray-900">{{ $mentor->user->country ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Ville</span>
                        <span class="text-gray-900">{{ $mentor->user->city ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Expérience</span>
                        <span class="text-gray-900">{{ $mentor->years_of_experience ? $mentor->years_of_experience . ' ans' : '-' }}</span>
                    </div>
                    <div class="border-t pt-4">
                        <span class="text-gray-500 text-sm block mb-2">Domaine d'expertise</span>
                        @if($mentor->specializationModel && $mentor->specializationModel->status === 'approved')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-orange-100 text-orange-800">
                                {{ $mentor->specializationModel->name }}
                            </span>
                        @elseif($mentor->specializationModel)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                {{ $mentor->specializationModel->name }} (En attente)
                            </span>
                        @elseif($mentor->specialization)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-600">
                                {{ $specializations[$mentor->specialization] ?? $mentor->specialization }}
                            </span>
                        @else
                            <span class="text-gray-400">Aucun domaine renseigné</span>
                        @endif
                    </div>
                    @if($mentor->user->linkedin_url)
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500">LinkedIn</span>
                        <a href="{{ $mentor->user->linkedin_url }}" target="_blank" class="text-blue-600 hover:text-blue-800">
                            Voir profil
                        </a>
                    </div>
                    @endif
                </div>

                <div class="mt-6 border-t pt-6">
                    <span class="text-gray-500 text-sm">Inscrit le</span>
                    <p class="text-gray-900">{{ $mentor->user->created_at->format('d/m/Y à H:i') }}</p>
                </div>
            </div>

            <!-- Bio -->
            @if($mentor->bio)
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="font-semibold text-gray-900 mb-3">Biographie</h3>
                <p class="text-gray-600 text-sm leading-relaxed">{{ $mentor->bio }}</p>
            </div>
            @endif
        </div>

        <!-- Parcours -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="font-semibold text-gray-900 mb-6">Parcours ({{ $mentor->roadmapSteps->count() }} étapes)</h3>

                @if($mentor->roadmapSteps->isEmpty())
                    <div class="text-center py-12 text-gray-500">
                        <svg class="w-12 h-12 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <p>Aucune étape de parcours renseignée</p>
                    </div>
                @else
                    <div class="space-y-6">
                        @foreach($mentor->roadmapSteps->sortBy('position') as $step)
                        <div class="flex gap-4">
                            <!-- Icône -->
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center
                                    @switch($step->step_type)
                                        @case('education') bg-blue-100 text-blue-600 @break
                                        @case('work') bg-green-100 text-green-600 @break
                                        @case('certification') bg-orange-100 text-orange-600 @break
                                        @case('achievement') bg-purple-100 text-purple-600 @break
                                        @default bg-gray-100 text-gray-600
                                    @endswitch
                                ">
                                    @switch($step->step_type)
                                        @case('education')
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                                            </svg>
                                            @break
                                        @case('work')
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                            </svg>
                                            @break
                                        @case('certification')
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                                            </svg>
                                            @break
                                        @default
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                            </svg>
                                    @endswitch
                                </div>
                            </div>

                            <!-- Contenu -->
                            <div class="flex-1 border-l-2 border-gray-200 pl-4 pb-6">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-xs font-medium px-2 py-0.5 rounded
                                        @switch($step->step_type)
                                            @case('education') bg-blue-100 text-blue-700 @break
                                            @case('work') bg-green-100 text-green-700 @break
                                            @case('certification') bg-orange-100 text-orange-700 @break
                                            @case('achievement') bg-purple-100 text-purple-700 @break
                                            @default bg-gray-100 text-gray-700
                                        @endswitch
                                    ">
                                        @switch($step->step_type)
                                            @case('education') Formation @break
                                            @case('work') Expérience @break
                                            @case('certification') Certification @break
                                            @case('achievement') Accomplissement @break
                                            @default {{ $step->step_type }}
                                        @endswitch
                                    </span>
                                </div>
                                <h4 class="font-medium text-gray-900">{{ $step->title }}</h4>
                                @if($step->institution_company)
                                    <p class="text-gray-600 text-sm">{{ $step->institution_company }}</p>
                                @endif
                                <p class="text-gray-400 text-xs mt-1">
                                    @if($step->start_date)
                                        {{ $step->start_date->format('M Y') }}
                                        @if($step->end_date)
                                            - {{ $step->end_date->format('M Y') }}
                                        @else
                                            - Présent
                                        @endif
                                    @endif
                                    @if($step->location)
                                        · {{ $step->location }}
                                    @endif
                                </p>
                                @if($step->description)
                                    <p class="text-gray-500 text-sm mt-2">{{ $step->description }}</p>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
