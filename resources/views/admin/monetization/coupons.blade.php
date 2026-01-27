@extends('layouts.admin')

@section('title', 'Gestion des Coupons')

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.monetization.index') }}" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <h1 class="text-2xl font-bold text-gray-900">Gestion des Coupons</h1>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Formulaire Création -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Créer un coupon</h2>
                    <form action="{{ route('admin.monetization.coupons.store') }}" method="POST" class="space-y-4">
                        @csrf

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Code</label>
                            <input type="text" name="code" required placeholder="EX: WELCOME100"
                                class="uppercase bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Montant (Crédits)</label>
                            <input type="number" name="credits_amount" required min="1" placeholder="Ex: 50"
                                class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Max Utilisations
                                (Optionnel)</label>
                            <input type="number" name="max_uses" min="1" placeholder="Vide = Illimité"
                                class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Expiration (Optionnel)</label>
                            <input type="date" name="expires_at"
                                class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5">
                        </div>

                        <button type="submit"
                            class="w-full bg-indigo-600 text-white font-bold py-2.5 rounded-lg hover:bg-indigo-700 transition shadow-lg shadow-indigo-100">
                            Créer le coupon
                        </button>
                    </form>
                </div>
            </div>

            <!-- Liste -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-gray-600">
                            <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                                <tr>
                                    <th class="px-6 py-3">Code</th>
                                    <th class="px-6 py-3">Crédits</th>
                                    <th class="px-6 py-3">Utilisations</th>
                                    <th class="px-6 py-3">Statut</th>
                                    <th class="px-6 py-3">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($coupons as $coupon)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-3 font-mono font-bold text-indigo-600">{{ $coupon->code }}</td>
                                        <td class="px-6 py-3 font-bold">{{ $coupon->credits_amount }}</td>
                                        <td class="px-6 py-3">
                                            {{ $coupon->uses_count }}
                                            <span class="text-gray-400">/ {{ $coupon->max_uses ?? '∞' }}</span>
                                        </td>
                                        <td class="px-6 py-3 text-xs">
                                            @if(!$coupon->isValid())
                                                <span class="px-2 py-1 bg-red-100 text-red-700 rounded-full">Expiré</span>
                                            @else
                                                <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full">Actif</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-3">
                                            <form action="{{ route('admin.monetization.coupons.destroy', $coupon) }}"
                                                method="POST" onsubmit="return confirm('Confirmer la suppression ?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="text-red-500 hover:text-red-700 font-medium text-xs">Supprimer</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">Aucun coupon.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="p-4">
                        {{ $coupons->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection