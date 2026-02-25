@extends('layouts.admin')

@section('title', 'Gestion des Coachs')

@section('header', 'Gestion des Coachs')

@section('content')
<div class="space-y-6">
    <!-- Statuts/Stats Rapides -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-indigo-500">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-indigo-100 text-indigo-500">
                    <i class="fas fa-user-tie fa-2x"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500 uppercase font-bold">Total Coachs</p>
                    <p class="text-2xl font-semibold text-gray-800">{{ $coaches->total() }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions Rapides -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex items-center space-x-4">
            <form action="{{ route('admin.coaches.index') }}" method="GET" class="flex">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher un coach..."
                    class="rounded-l-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border">
                <button type="submit"
                    class="bg-indigo-600 text-white px-4 py-2 rounded-r-lg hover:bg-indigo-700 transition">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>

        <div class="flex flex-wrap gap-2">
            <button onclick="document.getElementById('modal-promote').classList.remove('hidden')"
                class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition flex items-center">
                <i class="fas fa-plus mr-2"></i> Promouvoir un mentor
            </button>
            <button onclick="document.getElementById('modal-create').classList.remove('hidden')"
                class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition flex items-center">
                <i class="fas fa-user-plus mr-2"></i> Créer Nouveau Coach
            </button>
        </div>
    </div>

    @if(session('generated_password'))
    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-yellow-400"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm text-yellow-700 font-bold">
                    Compte créé ! Veuillez noter les identifiants :
                </p>
                <p class="text-sm text-yellow-700 mt-1">
                    Email : <strong>{{ session('generated_email') }}</strong><br>
                    Mot de passe : <code class="bg-yellow-100 px-1 rounded">{{ session('generated_password') }}</code>
                </p>
            </div>
        </div>
    </div>
    @endif

    <!-- Table des Coachs -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Coach
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type /
                        Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Inscrit
                        le</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($coaches as $coach)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="h-10 w-10 flex-shrink-0">
                                <img class="h-10 w-10 rounded-full object-cover" src="{{ $coach->avatar_url }}" alt="">
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">{{ $coach->name }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $coach->email }}</div>
                        <div class="text-sm text-gray-500">{{ $coach->phone ?? 'N/A' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span
                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                            {{ ucfirst($coach->user_type) }}
                        </span>
                        @if($coach->is_admin)
                        <span
                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800 ml-1">
                            Admin
                        </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $coach->created_at->format('d/m/Y') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                        <!-- Reset Password -->
                        <form action="{{ route('admin.coaches.reset-password', $coach) }}" method="POST" class="inline"
                            onsubmit="return confirm('Réinitialiser le mot de passe et envoyer les nouveaux accès à ce coach ?')">
                            @csrf
                            <button type="submit"
                                class="text-indigo-600 hover:text-indigo-900 px-3 py-1 rounded bg-indigo-50"
                                title="Réinitialiser le mot de passe">
                                <i class="fas fa-key mr-1"></i> Reset
                            </button>
                        </form>

                        <!-- Copy Credentials (JS) -->
                        <button type="button" onclick="copyToClipboard('{{ $coach->email }}', 'Email copié !')"
                            class="text-gray-600 hover:text-gray-900 px-3 py-1 rounded bg-gray-100"
                            title="Copier l'email">
                            <i class="fas fa-copy mr-1"></i> Email
                        </button>

                        <!-- Revoke -->
                        <form action="{{ route('admin.coaches.destroy', $coach) }}" method="POST" class="inline"
                            onsubmit="return confirm('Révoquer le statut de coach pour cet utilisateur ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900 px-3 py-1 rounded bg-red-50">
                                <i class="fas fa-user-minus mr-1"></i> Révoquer
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-10 text-center text-gray-500 italic">
                        Aucun coach trouvé.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if($coaches->hasPages())
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            {{ $coaches->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modal Promouvoir -->
<div id="modal-promote"
    class="hidden fixed inset-0 bg-gray-900 bg-opacity-75 overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4">
    <div
        class="relative mx-auto p-0 border-0 w-full max-w-lg shadow-2xl rounded-2xl bg-white overflow-hidden transition-all transform animate-in fade-in zoom-in duration-200">
        <!-- Header -->
        <div class="bg-indigo-600 px-6 py-4 flex items-center justify-between">
            <h3 class="text-xl font-bold text-white flex items-center">
                <i class="fas fa-user-plus mr-3 text-indigo-200"></i>
                Promouvoir des mentors
            </h3>
            <button onclick="document.getElementById('modal-promote').classList.add('hidden')"
                class="text-indigo-200 hover:text-white transition">
                <i class="fas fa-times fa-lg"></i>
            </button>
        </div>

        <form action="{{ route('admin.coaches.store') }}" method="POST" class="p-0">
            @csrf
            <div class="p-6">
                <p class="text-gray-600 mb-4 text-sm">
                    Sélectionnez un ou plusieurs mentors pour leur accorder le statut de Coach.
                </p>

                <!-- Search -->
                <div class="relative mb-4">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" id="mentor-search" onkeyup="filterMentors()"
                        placeholder="Rechercher un mentor par nom ou email..."
                        class="w-full pl-10 pr-4 py-2 border-gray-300 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 shadow-sm border transition">
                </div>

                <!-- List -->
                <div
                    class="border border-gray-200 rounded-xl overflow-hidden max-h-64 overflow-y-auto bg-gray-50 custom-scrollbar">
                    <div id="mentor-list" class="divide-y divide-gray-200">
                        @forelse($availableMentors as $mentor)
                        <label
                            class="mentor-item group flex items-center p-3 hover:bg-white cursor-pointer transition-colors"
                            data-search="{{ strtolower($mentor->name . ' ' . $mentor->email) }}">
                            <div class="relative flex items-center justify-center h-5 w-5 mr-3">
                                <input type="checkbox" name="user_ids[]" value="{{ $mentor->id }}"
                                    class="mentor-checkbox h-5 w-5 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded-md cursor-pointer">
                            </div>
                            <div class="flex items-center flex-1">
                                <img src="{{ $mentor->avatar_url }}"
                                    class="h-8 w-8 rounded-full object-cover border border-gray-200 mr-3 shadow-sm">
                                <div class="overflow-hidden">
                                    <div class="text-sm font-semibold text-gray-900 truncate mentor-name">{{
                                        $mentor->name }}</div>
                                    <div class="text-xs text-gray-500 truncate mentor-email">{{ $mentor->email }}</div>
                                </div>
                            </div>
                        </label>
                        @empty
                        <div class="p-6 text-center text-gray-500 italic text-sm">
                            <i class="fas fa-info-circle mb-2 block text-xl"></i>
                            Aucun mentor disponible pour la promotion.
                        </div>
                        @endforelse
                    </div>
                </div>

                <div id="no-results" class="hidden p-6 text-center text-gray-500 italic text-sm">
                    Aucun résultat pour cette recherche.
                </div>

                <div class="mt-4 flex items-center justify-between text-xs text-gray-500">
                    <span id="selected-count">0 mentor(s) sélectionné(s)</span>
                    <button type="button" onclick="selectAll(true)"
                        class="text-indigo-600 hover:text-indigo-800 font-bold uppercase tracking-wider">Tout
                        sélectionner</button>
                </div>
            </div>

            <!-- Footer -->
            <div class="bg-gray-50 px-6 py-4 flex flex-row-reverse gap-3 border-t border-gray-100">
                <button type="submit" id="btn-confirm" disabled
                    class="bg-indigo-600 text-white px-6 py-2.5 rounded-xl hover:bg-indigo-700 transition font-bold shadow-lg shadow-indigo-200 flex items-center opacity-50 cursor-not-allowed">
                    Confirmer la promotion
                </button>
                <button type="button" onclick="document.getElementById('modal-promote').classList.add('hidden')"
                    class="bg-white text-gray-700 px-6 py-2.5 rounded-xl border border-gray-300 hover:bg-gray-100 transition font-medium">
                    Annuler
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Créer -->
<div id="modal-create"
    class="hidden fixed inset-0 bg-gray-900 bg-opacity-75 overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4">
    <div
        class="relative mx-auto p-0 border-0 w-full max-w-md shadow-2xl rounded-2xl bg-white overflow-hidden animate-in fade-in zoom-in duration-200">
        <!-- Header -->
        <div class="bg-green-600 px-6 py-4 flex items-center justify-between">
            <h3 class="text-xl font-bold text-white flex items-center">
                <i class="fas fa-user-plus mr-3 text-green-200"></i>
                Nouveau Coach
            </h3>
            <button onclick="document.getElementById('modal-create').classList.add('hidden')"
                class="text-green-200 hover:text-white transition">
                <i class="fas fa-times fa-lg"></i>
            </button>
        </div>

        <form action="{{ route('admin.coaches.store') }}" method="POST" class="p-6">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-1.5">Nom complet</label>
                    <input type="text" name="name" placeholder="Ex: Jean Dupont"
                        class="w-full border-gray-300 rounded-xl focus:ring-green-500 focus:border-green-500 shadow-sm border py-2.5 px-4"
                        required>
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-1.5">Adresse Email</label>
                    <input type="email" name="email" placeholder="coach@brillio.com"
                        class="w-full border-gray-300 rounded-xl focus:ring-green-500 focus:border-green-500 shadow-sm border py-2.5 px-4"
                        required>
                </div>
                <div class="pt-2">
                    <p class="text-xs text-gray-500 italic">
                        <i class="fas fa-magic mr-1"></i> Le mot de passe sera généré automatiquement et affiché après
                        la création.
                    </p>
                </div>
            </div>

            <div class="mt-8 flex flex-row-reverse gap-3">
                <button type="submit"
                    class="bg-green-600 text-white px-6 py-2.5 rounded-xl hover:bg-green-700 transition font-bold shadow-lg shadow-green-200">
                    Créer le compte
                </button>
                <button type="button" onclick="document.getElementById('modal-create').classList.add('hidden')"
                    class="bg-white text-gray-700 px-6 py-2.5 rounded-xl border border-gray-300 hover:bg-gray-100 transition font-medium">
                    Annuler
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #d1d5db;
        border-radius: 10px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #9ca3af;
    }

    .mentor-item:has(input:checked) {
        background-color: #f5f3ff;
        border-color: #ddd6fe;
    }
</style>

<script>
    function copyToClipboard(text, message) {
        navigator.clipboard.writeText(text).then(() => {
            alert(message);
        });
    }

    function filterMentors() {
        const input = document.getElementById('mentor-search');
        const filter = input.value.toLowerCase();
        const items = document.getElementsByClassName('mentor-item');
        let visibleCount = 0;

        for (let i = 0; i < items.length; i++) {
            const text = items[i].getAttribute('data-search');
            if (text.includes(filter)) {
                items[i].style.display = "flex";
                visibleCount++;
            } else {
                items[i].style.display = "none";
            }
        }

        document.getElementById('no-results').classList.toggle('hidden', visibleCount > 0);
    }

    function selectAll(force = true) {
        const checkboxes = document.querySelectorAll('.mentor-checkbox');
        checkboxes.forEach(cb => {
            if (cb.closest('.mentor-item').style.display !== 'none') {
                cb.checked = force;
            }
        });
        updateSelectedCount();
    }

    function updateSelectedCount() {
        const checked = document.querySelectorAll('.mentor-checkbox:checked').length;
        document.getElementById('selected-count').textContent = checked + ' mentor(s) sélectionné(s)';

        const btn = document.getElementById('btn-confirm');
        if (checked > 0) {
            btn.disabled = false;
            btn.classList.remove('opacity-50', 'cursor-not-allowed');
            btn.classList.add('hover:bg-indigo-700');
        } else {
            btn.disabled = true;
            btn.classList.add('opacity-50', 'cursor-not-allowed');
            btn.classList.remove('hover:bg-indigo-700');
        }
    }

    // Attach listener to checkboxes
    document.addEventListener('change', function (e) {
        if (e.target.classList.contains('mentor-checkbox')) {
            updateSelectedCount();
        }
    });

    // Handle clicks on the label area for better UX
    document.addEventListener('click', function (e) {
        if (e.target.closest('.mentor-item') && !e.target.classList.contains('mentor-checkbox')) {
            const checkbox = e.target.closest('.mentor-item').querySelector('.mentor-checkbox');
            checkbox.checked = !checkbox.checked;
            updateSelectedCount();
        }
    });
</script>
@endsection