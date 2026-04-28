@extends('layouts.admin')

@section('title', 'Gestion des Établissements')

@section('header', 'Recommandations : Établissements')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <h1 class="text-2xl font-bold text-gray-900">Établissements & Centres de formation</h1>
        <div class="flex flex-wrap gap-3">
            <!-- Bouton IA -->
            <button x-data @click="$dispatch('open-ai-modal')" 
                class="inline-flex items-center px-4 py-2 bg-indigo-100 border border-indigo-300 rounded-lg font-semibold text-xs text-indigo-700 uppercase tracking-widest hover:bg-indigo-200 transition shadow-sm">
                <i class="fas fa-magic mr-2"></i> Auto-générer (IA)
            </button>
            <a href="{{ route('admin.establishments.create') }}" 
                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition shadow-sm">
                <i class="fas fa-plus mr-2"></i> Ajouter manuellement
            </a>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white shadow-sm border border-gray-200 rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Logo/Photo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom & Localisation</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Matching MBTI</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Intérêts</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($establishments as $establishment)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($establishment->photo_path)
                                <img src="{{ Storage::url($establishment->photo_path) }}" alt="" class="w-10 h-10 rounded-lg object-cover border border-gray-100">
                            @else
                                <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400">
                                    <i class="fas fa-building text-sm"></i>
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-bold text-gray-900">{{ $establishment->name }}</div>
                            <div class="text-xs text-gray-500">{{ $establishment->city }}, {{ $establishment->country }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $establishment->type === 'university' ? 'bg-blue-100 text-blue-800' : 'bg-emerald-100 text-emerald-800' }}">
                                {{ $establishment->type === 'university' ? 'Université' : 'Centre de formation' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1">
                                @if($establishment->mbti_types)
                                    @foreach(array_slice($establishment->mbti_types, 0, 3) as $type)
                                        <span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded text-[10px] font-bold">{{ $type }}</span>
                                    @endforeach
                                    @if(count($establishment->mbti_types) > 3)
                                        <span class="text-[10px] text-gray-400 font-bold">+{{ count($establishment->mbti_types) - 3 }}</span>
                                    @endif
                                @else
                                    <span class="text-xs text-gray-400 italic">Aucun</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a href="{{ route('admin.establishments.interests', $establishment) }}" class="inline-flex items-center py-1 px-3 bg-gray-50 border border-gray-200 rounded-lg text-sm font-semibold text-gray-700 hover:bg-white transition group">
                                <i class="fas fa-user-graduate mr-2 text-indigo-500 group-hover:scale-110 transition"></i>
                                {{ $establishment->interests_count }}
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($establishment->is_published)
                                <span class="flex items-center text-green-600 text-xs font-bold">
                                    <span class="w-1.5 h-1.5 bg-green-600 rounded-full mr-1.5"></span> Publié
                                </span>
                            @else
                                <span class="flex items-center text-gray-400 text-xs font-bold">
                                    <span class="w-1.5 h-1.5 bg-gray-400 rounded-full mr-1.5"></span> Brouillon
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.establishments.edit', $establishment) }}" class="text-indigo-600 hover:text-indigo-900 border border-indigo-100 bg-indigo-50 p-2 rounded-lg transition" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.establishments.destroy', $establishment) }}" method="POST" onsubmit="return confirm('Supprimer cet établissement ?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 border border-red-100 bg-red-50 p-2 rounded-lg transition" title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            <i class="fas fa-university text-4xl mb-4 text-gray-200 block"></i>
                            <p class="text-lg font-medium">Aucun établissement enregistré.</p>
                            <p class="text-sm mt-1">Commencez par ajouter un établissement ou utilisez l'IA.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($establishments->hasPages())
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            {{ $establishments->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modal IA -->
<div x-data="{ show: false, mbti: 'INTJ', loading: false }" 
    @open-ai-modal.window="show = true"
    x-show="show" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
    <div class="fixed inset-0 bg-black/50" @click="show = false"></div>
    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden transform transition-all">
            <form action="{{ route('admin.establishments.auto-generate') }}" method="POST" @submit="loading = true">
                @csrf
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-xl font-bold text-gray-900">Auto-génération IA (Recherche Globale)</h2>
                </div>
                <div class="p-6 space-y-4">
                    <p class="text-sm text-gray-500 leading-relaxed">
                        L'IA va rechercher de nouveaux établissements réels adaptés aux profils MBTI, en priorisant le <b>Bénin</b>. S'il n'y en a plus, elle recherchera automatiquement dans d'autres pays d'Afrique (Togo, Sénégal, Côte d'Ivoire, Maroc...). Elle assignera intelligemment les profils MBTI appropriés.
                    </p>
                    <div x-show="loading" class="flex flex-col items-center py-4 space-y-3">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                        <p class="text-xs font-bold text-indigo-600 uppercase tracking-widest">Recherche en cours...</p>
                    </div>
                </div>
                <div class="px-6 py-4 bg-gray-50 flex justify-end gap-3">
                    <button type="button" @click="show = false" class="px-4 py-2 text-sm font-bold text-gray-600 hover:text-gray-800" :disabled="loading">Annuler</button>
                    <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg font-bold hover:bg-indigo-700 transition" :disabled="loading">
                        Lancer la recherche
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
