@extends('layouts.organization')

@section('title', 'Mon Portefeuille')

@section('content')
<div class="space-y-12">
    <!-- Header -->
    <div class="text-center max-w-3xl mx-auto space-y-4">
        <h1 class="text-3xl font-bold text-gray-900 tracking-tight">
            Mon <span class="text-pink-600">Portefeuille</span>
        </h1>
        <p class="text-lg text-gray-500">
            Gérez vos crédits et consultez votre historique de transactions.
            Les crédits vous permettent de financer des besoins ponctuels.
        </p>
    </div>

    <!-- Current Balance (Placeholder for now) -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-200">
            <div class="px-4 py-5 sm:p-6 text-center">
                <dt class="text-sm font-medium text-gray-500 truncate">Solde actuel</dt>
                <dd class="mt-1 text-3xl font-semibold text-gray-900">{{
                    number_format(auth()->user()->organization->credits_balance ?? 0) }} Crédits</dd>
            </div>
        </div>
    </div>

    <!-- Credit Packs Section -->
    <div class="border-t border-gray-200 pt-12">
        <div class="text-center max-w-3xl mx-auto mb-10">
            <h2 class="text-2xl font-bold tracking-tight text-gray-900">Acheter des crédits</h2>
        </div>

        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @foreach($creditPacks as $pack)
            <div
                class="flex flex-col rounded-xl border border-gray-200 bg-white p-6 shadow-sm hover:border-pink-300 transition-colors">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ $pack->name }}</h3>
                    @if($pack->is_popular)
                    <span
                        class="inline-flex items-center rounded-full bg-pink-100 px-2.5 py-0.5 text-xs font-medium text-pink-800">
                        Populaire
                    </span>
                    @endif
                </div>
                <p class="text-3xl font-bold text-gray-900 mb-2">{{ number_format($pack->credits) }} <span
                        class="text-sm font-normal text-gray-500">Crédits</span></p>
                <p class="text-xl font-medium text-pink-600 mb-6">{{ number_format($pack->price) }} FCFA</p>

                <ul class="space-y-3 mb-6 flex-1">
                    @if(isset($pack->features) && is_array($pack->features))
                    @foreach($pack->features as $feature)
                    <li class="flex items-start">
                        <i class="fas fa-check text-organization-500 mt-1 mr-2 text-xs"></i>
                        <span class="text-sm text-gray-600">{{ $feature }}</span>
                    </li>
                    @endforeach
                    @endif
                </ul>

                <form action="{{ route('organization.wallet.purchase') }}" method="POST">
                    @csrf
                    <input type="hidden" name="pack_id" value="{{ $pack->id }}">
                    <button type="submit"
                        class="w-full rounded-md bg-white border border-pink-600 px-3 py-2 text-sm font-semibold text-pink-600 shadow-sm hover:bg-pink-50 transition-colors">
                        Acheter
                    </button>
                </form>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection