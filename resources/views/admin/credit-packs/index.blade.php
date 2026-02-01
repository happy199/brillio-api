@extends('layouts.admin')

@section('title', 'Gestion des Packs de Crédits')
@section('header', 'Packs de Crédits')

@section('content')
    <div x-data="{ 
        activeTab: 'jeune',
        showModal: false,
        editMode: false,
        modalTitle: '',
        formAction: '',
        jeuneBasePrice: {{ $jeuneCreditPrice }},
        mentorBasePrice: {{ $mentorCreditPrice }},
        formData: {
            id: '',
            user_type: '',
            name: '',
            credits: 10,
            price: 0,
            promo_percent: 0,
            description: '',
            display_order: '',
            is_popular: false,
            is_active: true
        },
        get theoreticalPrice() {
            const base = this.formData.user_type === 'jeune' ? this.jeuneBasePrice : this.mentorBasePrice;
            return (this.formData.credits || 0) * base;
        },
        calculateFinalPrice() {
            // Simple reactivity: updates price based on inputs.
            // Wait 1 tick for theoreticalPrice to update or just calc directly
            const base = this.formData.user_type === 'jeune' ? this.jeuneBasePrice : this.mentorBasePrice;
            const theo = (this.formData.credits || 0) * base;

            if (this.formData.promo_percent > 0) {
                // Apply discount
                this.formData.price = Math.round(theo * (1 - (this.formData.promo_percent / 100)));
            } else {
                this.formData.price = theo;
            }
        },
        openCreateModal(type) {
            this.editMode = false;
            this.modalTitle = 'Créer un Pack ' + (type === 'jeune' ? 'Jeune' : 'Mentor');
            this.formAction = '{{ route('admin.credit-packs.store') }}';

            // Default init
            const base = type === 'jeune' ? this.jeuneBasePrice : this.mentorBasePrice;

            this.formData = {
                id: '',
                user_type: type,
                name: '',
                credits: 10,
                price: 10 * base, // Initial calc
                promo_percent: 0,
                description: '',
                display_order: 0,
                is_popular: false,
                is_active: true
            };
            this.showModal = true;
        },
        openEditModal(pack) {
            this.editMode = true;
            this.modalTitle = 'Modifier le Pack';
            this.formAction = '/brillioSecretTeamAdmin/credit-packs/' + pack.id;

            this.formData = {
                id: pack.id,
                user_type: pack.user_type,
                name: pack.name,
                credits: pack.credits,
                price: pack.price,
                promo_percent: pack.promo_percent || 0,
                description: pack.description,
                display_order: pack.display_order,
                is_popular: Boolean(pack.is_popular),
                is_active: Boolean(pack.is_active)
            };
            // Recalculate to verify (or could just trust DB)
            // Let's not auto-recalc on open to avoid changing DB-saved prices if base price changed.
            // But the user WANTS dynamic calculation. Let's trigger it if they edit.
            this.showModal = true;
        }
    }">

        <!-- Tabs -->
        <div class="border-b border-gray-200 mb-6">
            <nav class="-mb-px flex space-x-8">
                <button @click="activeTab = 'jeune'"
                    :class="activeTab === 'jeune' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Packs Jeunes
                </button>
                <button @click="activeTab = 'mentor'"
                    :class="activeTab === 'mentor' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Packs Mentors
                </button>
            </nav>
        </div>

        <!-- Tab Content: JEUNE -->
        <div x-show="activeTab === 'jeune'" class="space-y-4">
            <div class="flex justify-end">
                <button @click="openCreateModal('jeune')"
                    class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                    + Nouveau Pack Jeune
                </button>
            </div>

            <div class="bg-white shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ordre
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Crédits</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prix
                                (FCFA)</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Promo
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Statut</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($jeunePacks as $pack)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $pack->display_order }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $pack->name ?? '-' }}</div>
                                    @if($pack->is_popular)
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Populaire
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-bold">{{ $pack->credits }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ number_format($pack->price, 0, ',', ' ') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600">
                                    {{ $pack->promo_percent > 0 ? '-' . $pack->promo_percent . '%' : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $pack->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $pack->is_active ? 'Actif' : 'Inactif' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button @click="openEditModal({{ $pack }})"
                                        class="text-indigo-600 hover:text-indigo-900 mr-3">Modifier</button>
                                    <form action="{{ route('admin.credit-packs.destroy', $pack) }}" method="POST" class="inline"
                                        onsubmit="return confirm('Êtes-vous sûr ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">Supprimer</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-gray-500">Aucun pack Jeune trouvé.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab Content: MENTOR -->
        <div x-show="activeTab === 'mentor'" class="space-y-4" style="display: none;">
            <div class="flex justify-end">
                <button @click="openCreateModal('mentor')"
                    class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                    + Nouveau Pack Mentor
                </button>
            </div>

            <div class="bg-white shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ordre
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Crédits</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prix
                                (FCFA)</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Promo
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Statut</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($mentorPacks as $pack)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $pack->display_order }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $pack->name ?? '-' }}</div>
                                    @if($pack->is_popular)
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Populaire
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-bold">{{ $pack->credits }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ number_format($pack->price, 0, ',', ' ') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600">
                                    {{ $pack->promo_percent > 0 ? '-' . $pack->promo_percent . '%' : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $pack->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $pack->is_active ? 'Actif' : 'Inactif' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button @click="openEditModal({{ $pack }})"
                                        class="text-indigo-600 hover:text-indigo-900 mr-3">Modifier</button>
                                    <form action="{{ route('admin.credit-packs.destroy', $pack) }}" method="POST" class="inline"
                                        onsubmit="return confirm('Êtes-vous sûr ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">Supprimer</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-gray-500">Aucun pack Mentor trouvé.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal Form -->
        <div x-show="showModal" class="fixed z-10 inset-0 overflow-y-auto" style="display: none;">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true" @click="showModal = false">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div
                    class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                    <form :action="formAction" method="POST">
                        @csrf
                        <template x-if="editMode">
                            <input type="hidden" name="_method" value="PUT">
                        </template>
                        <input type="hidden" name="user_type" x-model="formData.user_type">

                        <!-- Hidden input for the calculated final price -->
                        <input type="hidden" name="price" x-model="formData.price">

                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title" x-text="modalTitle">
                            </h3>
                            <div class="mt-4 space-y-4">
                                <!-- Name -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Nom du Pack</label>
                                    <input type="text" name="name" x-model="formData.name"
                                        class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>

                                <!-- Credits & Theoretical -->
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Crédits</label>
                                        <input type="number" name="credits" x-model="formData.credits"
                                            @input="calculateFinalPrice()" required
                                            class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500">Prix Théorique (FCFA) - <span
                                                x-text="theoreticalPrice"></span></label>
                                        <input type="text" :value="theoreticalPrice" disabled
                                            class="mt-1 bg-gray-100 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md cursor-not-allowed">
                                    </div>
                                </div>

                                <!-- Promo & Final -->
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Promotion (%)</label>
                                        <input type="number" name="promo_percent" x-model="formData.promo_percent"
                                            @input="calculateFinalPrice()" min="0" max="100"
                                            class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold text-indigo-700">Prix Final (FCFA)</label>
                                        <input type="text" :value="formData.price" readonly
                                            class="mt-1 font-bold text-indigo-700 bg-indigo-50 block w-full shadow-sm sm:text-sm border-indigo-300 rounded-md">
                                        <p class="text-xs text-blue-500 mt-1">Montant facturé à l'utilisateur</p>
                                    </div>
                                </div>

                                <!-- Display Order & Checkboxes -->
                                <div class="flex gap-4">
                                    <div class="w-1/2">
                                        <label class="block text-sm font-medium text-gray-700">Ordre d'affichage</label>
                                        <input type="number" name="display_order" x-model="formData.display_order"
                                            class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                    <div class="w-1/2 flex flex-col justify-end space-y-2">
                                        <div class="flex items-center">
                                            <input type="checkbox" name="is_popular" x-model="formData.is_popular"
                                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                            <label class="ml-2 block text-sm text-gray-900">Populaire</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input type="checkbox" name="is_active" x-model="formData.is_active"
                                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                            <label class="ml-2 block text-sm text-gray-900">Actif</label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Description -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Description (Optionnel)</label>
                                    <textarea name="description" x-model="formData.description" rows="2"
                                        class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                                Enregistrer
                            </button>
                            <button type="button" @click="showModal = false"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Annuler
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
@endsection