@extends('layouts.admin')

@section('title', 'Ajouter un Métier')

@section('content')
    <div class="max-w-5xl mx-auto mb-24">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Ajouter un Métier</h1>
                <p class="text-gray-500 text-sm mt-1">Créez une nouvelle fiche métier pour le catalogue d'orientation.</p>
            </div>
            <a href="{{ route('admin.careers.index') }}"
                class="inline-flex items-center px-4 py-2 bg-white border border-gray-200 text-gray-600 rounded-xl hover:bg-gray-50 transition shadow-sm font-semibold text-sm">
                <i class="fas fa-arrow-left mr-2 opacity-50"></i>
                Retour à la liste
            </a>
        </div>

        @if ($errors->any())
            <div class="mb-8 bg-rose-50 border border-rose-100 text-rose-700 px-6 py-4 rounded-2xl shadow-sm">
                <div class="flex items-center mb-2">
                    <i class="fas fa-exclamation-circle mr-2 text-rose-500"></i>
                    <span class="font-bold">Erreurs de validation :</span>
                </div>
                <ul class="list-disc list-inside text-sm space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <form action="{{ route('admin.careers.store') }}" method="POST" class="p-8 space-y-8">
                @csrf

                <!-- Section Principale -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-2">
                        <label for="title" class="text-sm font-bold text-gray-700 uppercase tracking-wider">Titre du Métier
                            <span class="text-rose-500">*</span></label>
                        <div class="flex gap-2">
                            <div class="relative flex-1">
                                <input type="text"
                                    class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm transition-all h-12 pr-10"
                                    id="title" name="title" value="{{ old('title') }}"
                                    placeholder="Ex: Développeur Fullstack" required>
                                <div
                                    class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
                                    <i class="fas fa-briefcase text-xs"></i>
                                </div>
                            </div>
                            <button type="button"
                                class="h-12 px-4 bg-indigo-50 text-indigo-600 rounded-xl hover:bg-indigo-100 transition flex items-center gap-2 font-bold text-sm whitespace-nowrap border border-indigo-100"
                                id="btn-generate-ai">
                                <i class="fas fa-magic"></i> ✨ IA
                            </button>
                        </div>
                        <p class="text-[10px] text-gray-400 font-medium">Saisissez un titre puis cliquez sur ✨ IA pour
                            générer le contenu.</p>
                    </div>

                    <div class="space-y-2">
                        <label for="demand_level" class="text-sm font-bold text-gray-700 uppercase tracking-wider">Demande
                            locale (Afrique)</label>
                        <input type="text"
                            class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm transition-all h-12"
                            id="demand_level" name="demand_level" value="{{ old('demand_level') }}"
                            placeholder="Ex: Haute, Moyenne...">
                    </div>
                </div>

                <!-- Description & Contexte -->
                <div class="space-y-6">
                    <div class="space-y-2">
                        <label for="description"
                            class="text-sm font-bold text-gray-700 uppercase tracking-wider">Description <span
                                class="text-rose-500">*</span></label>
                        <textarea
                            class="w-full rounded-2xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm transition-all p-4"
                            id="description" name="description" rows="4"
                            placeholder="Décrivez les missions principales de ce métier..."
                            required>{{ old('description') }}</textarea>
                    </div>

                    <div class="space-y-2">
                        <label for="african_context"
                            class="text-sm font-bold text-gray-700 uppercase tracking-wider">Contexte Africain</label>
                        <textarea
                            class="w-full rounded-2xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm transition-all p-4 text-sm"
                            id="african_context" name="african_context" rows="3"
                            placeholder="Pourquoi est-ce pertinent en Afrique aujourd'hui ?">{{ old('african_context') }}</textarea>
                    </div>
                </div>

                <!-- Perspectives & Impact IA -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-2">
                        <label for="future_prospects"
                            class="text-sm font-bold text-gray-700 uppercase tracking-wider">Perspectives d'avenir</label>
                        <input type="text"
                            class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm transition-all h-12"
                            id="future_prospects" name="future_prospects" value="{{ old('future_prospects') }}"
                            placeholder="Ex: Forte croissance digitale">
                    </div>

                    <div class="space-y-2">
                        <label for="ai_impact_level" class="text-sm font-bold text-gray-700 uppercase tracking-wider">Impact de l'IA</label>
                        <select
                            class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm transition-all h-12"
                            id="ai_impact_level" name="ai_impact_level">
                            <option value="">Sélectionner</option>
                            <option value="low" {{ old('ai_impact_level') == 'low' ? 'selected' : '' }}>Faible (Stable)</option>
                            <option value="medium" {{ old('ai_impact_level') == 'medium' ? 'selected' : '' }}>Moyen (Assisté par IA)</option>
                            <option value="high" {{ old('ai_impact_level') == 'high' ? 'selected' : '' }}>Élevé (Secteur challengé)</option>
                        </select>
                    </div>
                </div>

                <div class="space-y-2">
                    <label for="ai_impact_explanation" class="text-sm font-bold text-gray-700 uppercase tracking-wider">Explication de l'impact IA</label>
                    <textarea
                        class="w-full rounded-2xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm transition-all p-4 text-sm"
                        id="ai_impact_explanation" name="ai_impact_explanation" rows="2"
                        placeholder="Expliquez pourquoi ce niveau d'impact a été choisi...">{{ old('ai_impact_explanation') }}</textarea>
                </div>

                <hr class="border-gray-100">

                <!-- MBTI & Secteurs Grids -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                    <!-- MBTI -->
                    <div class="space-y-4">
                        <label class="text-sm font-bold text-gray-700 uppercase tracking-wider flex items-center">
                            <i class="fas fa-brain mr-2 text-indigo-500"></i>
                            Types MBTI correspondants
                        </label>
                        <div class="grid grid-cols-4 gap-2">
                            @php
                                $mbtiList = ['INTJ', 'INTP', 'ENTJ', 'ENTP', 'INFJ', 'INFP', 'ENFJ', 'ENFP', 'ISTJ', 'ISFJ', 'ESTJ', 'ESFJ', 'ISTP', 'ISFP', 'ESTP', 'ESFP'];
                            @endphp
                            @foreach($mbtiList as $mbti)
                                <label
                                    class="relative flex items-center justify-center p-2 rounded-lg border border-gray-100 hover:bg-indigo-50 transition cursor-pointer group">
                                    <input type="checkbox" name="mbti_types[]" value="{{ $mbti }}" class="sr-only peer">
                                    <span
                                        class="text-[10px] font-bold text-gray-500 peer-checked:text-indigo-600 transition-colors uppercase">{{ $mbti }}</span>
                                    <div
                                        class="absolute inset-0 border-2 border-transparent peer-checked:border-indigo-500 rounded-lg transition-all">
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Secteurs -->
                    <div class="space-y-4">
                        <label class="text-sm font-bold text-gray-700 uppercase tracking-wider flex items-center">
                            <i class="fas fa-layer-group mr-2 text-indigo-500"></i>
                            Secteurs d'activité
                        </label>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 text-[10px]">
                            @php
                                $sectorsList = ['tech', 'business', 'creative', 'science', 'social', 'communication', 'leadership', 'artisanat', 'agronomie', 'droit', 'sante', 'finance'];
                            @endphp
                            @foreach($sectorsList as $sector)
                                <label
                                    class="flex items-center gap-2 p-2 hover:bg-gray-50 rounded-xl cursor-pointer transition border border-gray-50">
                                    <input type="checkbox" name="sectors[]" value="{{ $sector }}"
                                        class="rounded text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                    <span class="text-gray-600 font-medium">{{ ucfirst($sector) }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Submit -->
                <div class="pt-6">
                    <button type="submit"
                        class="w-full h-14 bg-indigo-600 text-white rounded-2xl hover:bg-indigo-700 transition shadow-xl shadow-indigo-100 font-bold text-lg flex items-center justify-center gap-3">
                        <i class="fas fa-save"></i>
                        Enregistrer le Métier
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script nonce="{{ request()->attributes->get('csp_nonce') }}">
            document.getElementById('btn-generate-ai').addEventListener('click', function () {
                const title = document.getElementById('title').value;
                if (!title) {
                    alert('Veuillez d\'abord saisir un titre de métier.');
                    return;
                }

                const btn = this;
                const originalContent = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

                fetch('{{ route("admin.ai.generate-career") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ title: title })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            alert('Erreur : ' + data.error);
                        } else {
                            if (data.description) document.getElementById('description').value = data.description;
                            if (data.african_context) document.getElementById('african_context').value = data.african_context;
                            if (data.future_prospects) document.getElementById('future_prospects').value = data.future_prospects;
                            if (data.demand_level) document.getElementById('demand_level').value = data.demand_level;
                            if (data.ai_impact_level) document.getElementById('ai_impact_level').value = data.ai_impact_level;
                            if (data.ai_impact_explanation) document.getElementById('ai_impact_explanation').value = data.ai_impact_explanation;

                            // Notification visuelle
                            const successMsg = document.createElement('div');
                            successMsg.className = 'fixed bottom-8 right-8 bg-indigo-600 text-white px-6 py-3 rounded-full shadow-2xl z-50 animate-bounce';
                            successMsg.innerText = '✨ Contenu généré avec succès !';
                            document.body.appendChild(successMsg);
                            setTimeout(() => successMsg.remove(), 3000);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Une erreur est survenue lors de la génération.');
                    })
                    .finally(() => {
                        btn.disabled = false;
                        btn.innerHTML = originalContent;
                    });
            });
        </script>
    @endpush
@endsection