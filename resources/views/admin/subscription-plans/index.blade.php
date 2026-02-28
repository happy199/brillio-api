@extends('layouts.admin')

@section('title', 'Gestion des Offres d\'Abonnement')

@section('header', 'Configuration des Abonnements (PRO/Entreprise)')

@section('content')
<div class="max-w-7xl mx-auto" x-data="{
    activeTab: 'pro',
    showModal: false,
    editMode: false,
    modalTitle: '',
    formAction: '',
    formData: {
        id: '',
        name: '',
        target_plan: 'pro',
        duration_days: 30,
        price: 0,
        promo_percent: 0,
        description: '',
        features: [],
        features_text: '',
        is_popular: false,
        is_active: true
    },
    openCreateModal(targetPlan) {
        this.editMode = false;
        this.modalTitle = 'Créer une Offre d\'Abonnement ' + (targetPlan === 'free' ? 'Standard (Gratuit)' : (targetPlan === 'pro' ? 'PRO' : 'Entreprise'));
        this.formAction = '{{ route('admin.subscription-plans.store') }}';
        this.formData = {
            id: '',
            name: '',
            target_plan: targetPlan,
            duration_days: targetPlan === 'free' ? 0 : 30,
            price: targetPlan === 'free' ? 0 : (targetPlan === 'pro' ? 20000 : 50000),
            promo_percent: 0,
            description: '',
            features: [],
            features_text: '',
            is_popular: false,
            is_active: true
        };
        this.showModal = true;
    },
    openEditModal(plan) {
        this.editMode = true;
        this.modalTitle = 'Modifier l\'Offre d\'Abonnement';
        this.formAction = '{{ url('brillioSecretTeamAdmin/subscription-plans') }}/' + plan.id;
        this.formData = { ...plan };
        this.formData.features_text = plan.features ? plan.features.join(', ') : '';
        this.showModal = true;
    }
}">
    <!-- Tabs -->
    <div class="mb-6 border-b border-gray-200">
        <nav class="-mb-px flex space-x-8">
            <button @click="activeTab = 'free'"
                :class="activeTab === 'free' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                Plans Gratuits
            </button>
            <button @click="activeTab = 'pro'"
                :class="activeTab === 'pro' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                Plans Pro
            </button>
            <button @click="activeTab = 'enterprise'"
                :class="activeTab === 'enterprise' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                Plans Entreprise
            </button>
        </nav>
    </div>

    <!-- Actions -->
    <div class="mb-4 flex justify-end">
        <button @click="openCreateModal(activeTab)"
            class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
            + Nouveau Plan
        </button>
    </div>

    <!-- Table -->
    <div class="bg-white shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durée
                        (jours)</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prix
                        (FCFA)</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($plans as $plan)
                <tr x-show="activeTab === '{{ $plan->target_plan }}'">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">{{ $plan->name }}</div>
                        <div class="text-xs text-gray-500 uppercase">{{ $plan->target_plan }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $plan->duration_days }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-bold">
                        {{ number_format($plan->price, 0, ',', ' ') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span
                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $plan->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $plan->is_active ? 'Actif' : 'Inactif' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <button @click="openEditModal({{ $plan }})"
                            class="text-indigo-600 hover:text-indigo-900 mr-3">Modifier</button>
                        <form action="{{ route('admin.subscription-plans.destroy', $plan) }}" method="POST"
                            class="inline" onsubmit="return confirm('Supprimer ce plan ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900">Supprimer</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Modal -->
    <div x-show="showModal" class="fixed z-10 inset-0 overflow-y-auto" style="display: none;" x-cloak>
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            <div
                class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900" x-text="modalTitle"></h3>
                    <button @click="showModal = false" class="text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form :action="formAction" method="POST">
                    @csrf
                    <input type="hidden" name="_method" :value="editMode ? 'PUT' : ''">

                    <input type="hidden" name="target_plan" x-model="formData.target_plan">

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nom du Plan</label>
                            <input type="text" name="name" x-model="formData.name" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Durée (en jours)</label>
                            <input type="number" name="duration_days" x-model="formData.duration_days" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Prix (FCFA)</label>
                                <input type="number" name="price" x-model="formData.price" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Promo (%)</label>
                                <input type="number" name="promo_percent" x-model="formData.promo_percent"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea name="description" x-model="formData.description" rows="2"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Fonctionnalités (séparées par des
                                virgules ou retours à la ligne)</label>
                            <textarea name="features_text" x-model="formData.features_text" rows="3"
                                placeholder="Ex: Stats détaillées, Export PDF, Support..."
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
                            <div class="mt-2 flex flex-wrap gap-2">
                                <template
                                    x-for="(feat, index) in formData.features_text.split(/[,\n]/).map(f => f.trim()).filter(f => f !== '')"
                                    :key="index">
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-800">
                                        <span x-text="feat"></span>
                                        <input type="hidden" name="features[]" :value="feat">
                                    </span>
                                </template>
                            </div>
                        </div>

                        <div class="flex items-center space-x-6">
                            <label class="flex items-center">
                                <input type="checkbox" name="is_popular" x-model="formData.is_popular" value="1"
                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                <span class="ml-2 text-sm text-gray-700">Populaire</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="is_active" x-model="formData.is_active" value="1"
                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                <span class="ml-2 text-sm text-gray-700">Actif</span>
                            </label>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" @click="showModal = false"
                            class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Annuler
                        </button>
                        <button type="submit"
                            class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                            Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection