@extends('layouts.jeune')

@section('title', 'Mon Portefeuille')

@section('content')
    <div class="space-y-8">
        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-md">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd" />
                    </svg>
                    <p class="text-green-800 font-medium">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if(session('info'))
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-md" x-data="{ autoRefresh: true }" x-init="
                                        if (autoRefresh) {
                                            setTimeout(() => window.location.reload(), 3000);
                                        }
                                    ">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-blue-500 mr-3 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    <p class="text-blue-800 font-medium">{{ session('info') }} <span class="text-sm">(Actualisation
                            automatique...)</span></p>
                </div>
            </div>
        @endif

        @if(session('warning'))
            <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded-md">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-yellow-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                            clip-rule="evenodd" />
                    </svg>
                    <p class="text-yellow-800 font-medium">{{ session('warning') }}</p>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-md">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-red-500 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd" />
                    </svg>
                    <div class="flex-1">
                        @foreach($errors->all() as $error)
                            <p class="text-red-800 font-medium">{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- En-tête -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Mon Portefeuille</h1>
                <p class="text-gray-600">Gérez vos crédits Brillio pour accéder aux contenus premium.</p>
            </div>
            <div
                class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl p-6 text-white shadow-lg flex items-center gap-6 transform md:scale-105 transition">
                <div>
                    <p class="text-indigo-100 text-sm font-medium">Solde actuel</p>
                    <div class="flex items-baseline gap-1">
                        <span class="text-4xl font-extrabold">{{ number_format($user->credits_balance) }}</span>
                        <span class="text-sm font-bold opacity-80">Crédits</span>
                    </div>
                </div>
                <div class="bg-white/20 p-3 rounded-xl backdrop-blur-sm">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>


        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Recharge -->
            <div class="lg:col-span-2 space-y-6">
                <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Recharger mon compte
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach($packs as $pack)
                        <div
                            class="bg-white border rounded-xl p-5 hover:border-indigo-500 hover:shadow-md transition relative group overflow-hidden">
                            @if($pack['bonus'] > 0)
                                <div
                                    class="absolute top-0 right-0 bg-green-500 text-white text-[10px] font-bold px-2 py-1 rounded-bl-lg">
                                    -{{ $pack['bonus'] }}%
                                </div>
                            @endif

                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <span class="block text-2xl font-black text-gray-900">{{ $pack['credits'] }}</span>
                                    <span class="text-xs font-semibold text-gray-500 uppercase">Crédits</span>
                                </div>
                                <div class="text-right">
                                    <span
                                        class="block text-lg font-bold text-indigo-600">{{ number_format($pack['price'], 0, ',', ' ') }}
                                        F</span>
                                </div>
                            </div>

                            <form action="{{ route('jeune.wallet.purchase') }}" method="POST">
                                @csrf
                                <input type="hidden" name="amount" value="{{ $pack['credits'] }}">
                                <button type="submit"
                                    class="w-full py-2 px-3 bg-gray-50 hover:bg-indigo-600 text-gray-700 hover:text-white font-semibold rounded-lg transition text-sm">
                                    Acheter maintenant
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>

                <!-- Coupon -->
                <div class="bg-indigo-50 rounded-xl p-6 border border-indigo-100 mt-6">
                    <h3 class="font-bold text-indigo-900 mb-2">Vous avez un code promo ?</h3>
                    <form action="{{ route('jeune.wallet.redeem') }}" method="POST" class="flex gap-2">
                        @csrf
                        <input type="text" name="code" placeholder="Entrez votre code ici"
                            class="flex-1 bg-white border-0 text-sm rounded-lg focus:ring-2 focus:ring-indigo-500 p-3">
                        <button type="submit"
                            class="bg-indigo-600 text-white font-bold px-6 py-2 rounded-lg hover:bg-indigo-700 transition">
                            Valider
                        </button>
                    </form>
                    @error('code') <p class="text-red-600 text-sm mt-2 font-medium">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Historique -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden sticky top-24">
                    <div class="p-4 border-b border-gray-100 bg-gray-50">
                        <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide">Historique</h2>
                    </div>
                    <div class="divide-y divide-gray-100 max-h-[500px] overflow-y-auto">
                        @forelse($transactions as $transaction)
                            <div class="p-4 hover:bg-gray-50 transition">
                                <div class="flex justify-between items-center mb-1">
                                    <span
                                        class="text-xs font-medium text-gray-500">{{ $transaction->created_at->format('d M H:i') }}</span>
                                    <span
                                        class="font-bold text-sm {{ $transaction->amount > 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $transaction->amount > 0 ? '+' : '' }}{{ $transaction->amount }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-700 truncate" title="{{ $transaction->description }}">
                                    {{ $transaction->description }}
                                </p>
                            </div>
                        @empty
                            <div class="p-6 text-center text-sm text-gray-500 italic">
                                Aucune transaction récente.
                            </div>
                        @endforelse
                    </div>
                    <div class="p-2 bg-gray-50 border-t border-gray-100 text-center">
                        {{ $transactions->links('pagination.simple-indigo') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection