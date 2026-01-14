@extends('layouts.admin')

@section('title', 'Modérer les Suggestions')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="mb-6">
            <a href="{{ route('admin.specializations.index') }}" class="text-orange-600 hover:text-orange-800">
                ← Retour à la liste
            </a>
        </div>

        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Suggestions en Attente de Modération</h1>
            <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm font-semibold">
                {{ $pendingSpecializations->count() }} suggestion(s)
            </span>
        </div>

        <!-- Messages -->
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if($pendingSpecializations->isEmpty())
            <div class="bg-white rounded-lg shadow p-8 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h3 class="mt-2 text-lg font-medium text-gray-900">Aucune suggestion en attente</h3>
                <p class="mt-1 text-sm text-gray-500">Toutes les suggestions ont été traitées</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($pendingSpecializations as $spec)
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <h3 class="text-xl font-bold text-gray-900">{{ $spec->name }}</h3>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        En attente
                                    </span>
                                    @if(!$spec->created_by_admin)
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                            Suggéré par un mentor
                                        </span>
                                    @endif
                                </div>

                                @if($spec->description)
                                    <p class="text-gray-600 mb-3">{{ $spec->description }}</p>
                                @endif

                                <div class="flex items-center gap-4 text-sm text-gray-500">
                                    <span>
                                        <strong>Créé le:</strong> {{ $spec->created_at->format('d/m/Y à H:i') }}
                                    </span>
                                    <span>
                                        <strong>Mentors liés:</strong> {{ $spec->mentor_profiles_count }}
                                    </span>
                                </div>

                                @if($spec->mbtiTypes->isNotEmpty())
                                    <div class="mt-3">
                                        <span class="text-sm text-gray-500">Types MBTI: </span>
                                        @foreach($spec->mbtiTypes as $mbti)
                                            <span
                                                class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded">{{ $mbti->mbti_type_code }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <div class="flex gap-2 ml-4">
                                <!-- Éditer avant d'approuver -->
                                <a href="{{ route('admin.specializations.edit', $spec) }}"
                                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm">
                                    Éditer
                                </a>

                                <!-- Approuver -->
                                <form action="{{ route('admin.specializations.approve', $spec) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit"
                                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm"
                                        onclick="return confirm('Approuver cette spécialisation ?')">
                                        ✓ Approuver
                                    </button>
                                </form>

                                <!-- Rejeter -->
                                <form action="{{ route('admin.specializations.reject', $spec) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit"
                                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm"
                                        onclick="return confirm('Rejeter et supprimer cette suggestion ?')">
                                        ✗ Rejeter
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection