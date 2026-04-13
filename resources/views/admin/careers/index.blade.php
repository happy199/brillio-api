@extends('layouts.admin')

@section('title', 'Gestion des Métiers')

@section('content')
<div class="mb-24">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Référentiel des Métiers</h1>
                <p class="text-gray-500 text-sm mt-1">Gérez le catalogue des carrières suggérées aux jeunes après leurs tests.</p>
            </div>
            <div class="flex items-center gap-3">
                <button id="btn-bulk-audit" class="inline-flex items-center px-6 py-3 bg-white border border-indigo-100 text-indigo-600 rounded-xl hover:bg-indigo-50 transition shadow-sm font-bold text-sm">
                    <i class="fas fa-magic mr-2"></i>
                    Auditer le Référentiel
                </button>
                <a href="{{ route('admin.careers.create') }}" class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition shadow-lg shadow-indigo-100 font-bold text-sm">
                    <i class="fas fa-plus mr-2"></i>
                    Ajouter un Métier
                </a>
            </div>
        </div>
    </div>

    <!-- Progress Modal (Hidden by default) -->
    <div id="audit-modal" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full p-8 space-y-6">
            <div class="text-center">
                <div class="w-20 h-20 bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl">
                    <i class="fas fa-robot animate-pulse"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900">Audit du Référentiel</h3>
                <p class="text-gray-500 text-sm mt-2" id="audit-status">Analyse des données manquantes...</p>
            </div>

            <div class="space-y-2">
                <div class="flex justify-between text-xs font-bold text-gray-400 uppercase tracking-wider">
                    <span id="audit-progress-text">0%</span>
                    <span id="audit-count-text">0 / 0</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden">
                    <div id="audit-progress-bar" class="bg-indigo-600 h-full transition-all duration-500" style="width: 0%"></div>
                </div>
            </div>

            <div id="audit-current-job" class="text-center text-sm font-medium text-indigo-600 bg-indigo-50 py-2 rounded-lg truncate px-4 hidden">
                Traitement : ...
            </div>

            <div class="flex gap-3">
                <button id="btn-cancel-audit" class="flex-1 py-3 border border-gray-200 text-gray-600 rounded-xl hover:bg-gray-50 transition font-bold text-sm">
                    Annuler
                </button>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 bg-emerald-50 border border-emerald-100 text-emerald-700 px-4 py-3 rounded-xl flex items-center shadow-sm">
            <i class="fas fa-check-circle mr-3 text-emerald-500"></i>
            {{ session('success') }}
        </div>
    @endif

    <!-- Filtres -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6 border border-gray-100">
        <form action="{{ route('admin.careers.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-7 gap-4 items-end">
            <!-- Recherche -->
            <div class="lg:col-span-2 space-y-1">
                <label for="search" class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Rechercher</label>
                <div class="relative">
                    <input type="text" name="search" id="search" value="{{ request('search') }}" 
                        placeholder="Titre du métier..." 
                        class="w-full rounded-lg border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm text-sm h-11 pl-10">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                        <i class="fas fa-search text-xs"></i>
                    </div>
                </div>
            </div>

            <!-- Impact IA -->
            <div class="space-y-1">
                <label for="impact" class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Impact IA</label>
                <select name="impact" id="impact" class="w-full rounded-lg border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm text-sm h-11">
                    <option value="">Tous</option>
                    <option value="low" {{ request('impact') == 'low' ? 'selected' : '' }}>Stable</option>
                    <option value="medium" {{ request('impact') == 'medium' ? 'selected' : '' }}>Favorable</option>
                    <option value="high" {{ request('impact') == 'high' ? 'selected' : '' }}>Challengé</option>
                </select>
            </div>

            <!-- Demande -->
            <div class="space-y-1">
                <label for="demand" class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Demande</label>
                <select name="demand" id="demand" class="w-full rounded-lg border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm text-sm h-11">
                    <option value="">Toutes</option>
                    @foreach($demandLevels as $level)
                        <option value="{{ $level }}" {{ request('demand') == $level ? 'selected' : '' }}>{{ $level }}</option>
                    @endforeach
                </select>
            </div>

            <!-- MBTI -->
            <div class="space-y-1">
                <label for="mbti" class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">MBTI</label>
                <select name="mbti" id="mbti" class="w-full rounded-lg border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm text-sm h-11">
                    <option value="">Tous</option>
                    @php $mbtiList = ['INTJ', 'INTP', 'ENTJ', 'ENTP', 'INFJ', 'INFP', 'ENFJ', 'ENFP', 'ISTJ', 'ISFJ', 'ESTJ', 'ESFJ', 'ISTP', 'ISFP', 'ESTP', 'ESFP']; @endphp
                    @foreach($mbtiList as $type)
                        <option value="{{ $type }}" {{ request('mbti') == $type ? 'selected' : '' }}>{{ $type }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Secteur -->
            <div class="space-y-1">
                <label for="sector" class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Secteur</label>
                <select name="sector" id="sector" class="w-full rounded-lg border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm text-sm h-11">
                    <option value="">Tous</option>
                    @php $sectorsList = ['tech', 'business', 'creative', 'science', 'social', 'communication', 'leadership', 'artisanat', 'agronomie', 'droit', 'sante', 'finance']; @endphp
                    @foreach($sectorsList as $s)
                        <option value="{{ $s }}" {{ request('sector') == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Boutons -->
            <div class="flex gap-2">
                <button type="submit" class="flex-1 h-11 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition font-bold text-xs shadow-md shadow-indigo-100" title="Filtrer">
                    <i class="fas fa-filter"></i>
                </button>
                <a href="{{ route('admin.careers.index') }}" class="flex-1 h-11 bg-gray-50 text-gray-500 rounded-lg hover:bg-gray-100 transition font-bold text-xs flex items-center justify-center border border-gray-200" title="Réinitialiser">
                    <i class="fas fa-undo"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Liste des Métiers -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-100">
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-widest">Titre du Métier</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-widest hidden lg:table-cell">Description</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-widest">Impact IA</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-widest">Demande</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-widest text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($careers as $career)
                        <tr class="hover:bg-gray-50/80 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-900">{{ $career->title }}</div>
                                <div class="text-xs text-gray-400 mt-0.5 flex items-center">
                                    <i class="fas fa-tag mr-1 opacity-50"></i>
                                    {{ collect($career->sectors_list)->take(2)->map(fn($s) => ucfirst($s))->join(', ') ?: 'Général' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 hidden lg:table-cell">
                                <p class="text-sm text-gray-500 max-w-xs truncate">{{ $career->description }}</p>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $impactColors = [
                                        'low' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                        'medium' => 'bg-indigo-50 text-indigo-700 border-indigo-100',
                                        'high' => 'bg-rose-50 text-rose-700 border-rose-100',
                                    ];
                                    $impactLabels = [
                                        'low' => 'Stable',
                                        'medium' => 'Favorable',
                                        'high' => 'Challengé',
                                    ];
                                    $impact = $career->ai_impact_level ?: 'low';
                                @endphp
                                <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $impactColors[$impact] ?? $impactColors['low'] }}">
                                    {{ $impactLabels[$impact] ?? 'Stable' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm font-medium text-gray-600 bg-gray-100 px-2 py-1 rounded-lg">
                                    {{ $career->demand_level ?: '-' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.careers.edit', $career->id) }}" class="p-2 bg-indigo-50 text-indigo-600 rounded-lg hover:bg-indigo-100 transition" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.careers.destroy', $career->id) }}" method="POST" class="inline delete-career-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="p-2 bg-rose-50 text-rose-600 rounded-lg hover:bg-rose-100 transition delete-career-btn" title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                                <i class="fas fa-folder-open mb-3 text-3xl opacity-20"></i>
                                <p>Aucun métier trouvé dans le référentiel.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($careers->hasPages())
            <div class="px-6 py-4 bg-gray-50/50 border-t border-gray-100">
                {{ $careers->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script nonce="{{ request()->attributes->get('csp_nonce') }}">
document.addEventListener('DOMContentLoaded', function() {
    const btnAudit = document.getElementById('btn-bulk-audit');
    const modal = document.getElementById('audit-modal');
    const btnCancel = document.getElementById('btn-cancel-audit');
    const statusText = document.getElementById('audit-status');
    const progressText = document.getElementById('audit-progress-text');
    const countText = document.getElementById('audit-count-text');
    const progressBar = document.getElementById('audit-progress-bar');
    const currentJobBox = document.getElementById('audit-current-job');
    
    let isCancelled = false;

    btnAudit.addEventListener('click', async function() {
        if (!confirm('Cette action va analyser tout le référentiel et compléter les champs manquants via l\'IA. Continuer ?')) return;
        
        modal.classList.remove('hidden');
        isCancelled = false;
        
        try {
            // Step 1: Get incomplete IDs
            statusText.innerText = "Identification des métiers incomplets...";
            const response = await fetch('{{ route("admin.careers.bulk-audit") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            });
            const data = await response.json();
            
            if (data.count === 0) {
                statusText.innerText = "Tout est à jour ! Aucune donnée manquante.";
                setTimeout(() => modal.classList.add('hidden'), 2000);
                return;
            }

            const ids = data.ids;
            const total = data.count;
            countText.innerText = `0 / ${total}`;
            currentJobBox.classList.remove('hidden');

            for (let i = 0; i < ids.length; i++) {
                if (isCancelled) break;

                const id = ids[i];
                statusText.innerText = `Complétion automatique en cours...`;
                
                // Update progress
                const progress = Math.round(((i) / total) * 100);
                progressBar.style.width = `${progress}%`;
                progressText.innerText = `${progress}%`;
                countText.innerText = `${i} / ${total}`;

                // Process single
                const processUrl = '{{ route("admin.careers.process-audit", ":id") }}'.replace(':id', id);
                const res = await fetch(processUrl, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });
                const resData = await res.json();
                
                if (resData.title) {
                    currentJobBox.innerText = `Dernier traité : ${resData.title}`;
                }
            }

            if (!isCancelled) {
                progressBar.style.width = `100%`;
                progressText.innerText = `100%`;
                countText.innerText = `${total} / ${total}`;
                statusText.innerText = "Audit terminé avec succès ! Rechargement...";
                setTimeout(() => window.location.reload(), 1500);
            }

        } catch (error) {
            console.error(error);
            alert("Une erreur est survenue lors de l'audit.");
            modal.classList.add('hidden');
        }
    });

    btnCancel.addEventListener('click', () => {
        isCancelled = true;
        modal.classList.add('hidden');
    });

    // Delete handling
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.delete-career-btn');
        if (btn) {
            if (confirm('Êtes-vous sûr de vouloir supprimer ce métier ?')) {
                btn.closest('form').submit();
            }
        }
    });
});
</script>
@endpush
@endsection


