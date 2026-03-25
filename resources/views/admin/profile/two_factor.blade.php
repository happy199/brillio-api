@extends('layouts.admin')

@section('content')
<div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
    <div class="md:grid md:grid-cols-3 md:gap-6">
        <div class="md:col-span-1">
            <div class="px-4 sm:px-0">
                <h3 class="text-lg font-medium leading-6 text-gray-900">Double Authentification</h3>
                <p class="mt-1 text-sm text-gray-600">
                    Ajoutez une sécurité supplémentaire à votre compte en utilisant la double authentification (TOTP).
                </p>
            </div>
        </div>

        <div class="mt-5 md:mt-0 md:col-span-2">
            <div class="bg-white shadow sm:rounded-lg overflow-hidden">
                <div class="px-4 py-5 sm:p-6">
                    @if(auth()->user()->two_factor_confirmed_at)
                        <div class="flex items-center mb-6 text-green-600">
                            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                            <span class="font-bold">La double authentification est activée.</span>
                        </div>
                        
                        <p class="text-sm text-gray-600 mb-6">
                            Votre compte est protégé par un code de sécurité généré par votre application d'authentification.
                        </p>

                        <form action="{{ route('admin.two_factor.deactivate') }}" method="POST">
                            @csrf
                            <button type="submit" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded text-sm transition duration-150 ease-in-out">
                                Désactiver la double authentification
                            </button>
                        </form>
                    @else
                        <div class="text-gray-800 font-bold mb-4">
                            Configurez votre application d'authentification
                        </div>
                        
                        <div class="flex flex-col md:flex-row items-center gap-8">
                            <div class="bg-white p-4 border rounded-lg shadow-inner">
                                {!! $qrCodeSvg !!}
                            </div>
                            
                            <div class="flex-1">
                                <p class="text-sm text-gray-600 mb-4">
                                    1. Scannez ce QR Code avec une application comme <strong>Google Authenticator</strong> ou <strong>Microsoft Authenticator</strong>.
                                </p>
                                <p class="text-sm text-gray-600 mb-6">
                                    2. Saisissez le code à 6 chiffres généré par l'application pour confirmer l'activation.
                                </p>

                                <form action="{{ route('admin.two_factor.activate') }}" method="POST" class="max-w-xs">
                                    @csrf
                                    <div>
                                        <label for="code" class="block text-xs font-semibold uppercase text-gray-500 mb-1">Code de confirmation</label>
                                        <input type="text" name="code" id="code" maxlength="6" class="w-full border-gray-300 rounded shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-lg tracking-widest text-center" placeholder="000000" required>
                                        @error('code')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <button type="submit" class="mt-4 w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded text-sm transition duration-150 ease-in-out">
                                        Activer maintenant
                                    </button>
                                </form>

                                <div class="mt-6 pt-4 border-t border-gray-100">
                                    <form action="{{ route('admin.two_factor.deactivate') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-sm text-red-600 hover:text-red-500 font-medium">
                                            Désactiver la double authentification
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 pt-6 border-t border-gray-100">
                            <p class="text-xs text-gray-500">
                                Clé secrète (si vous ne pouvez pas scanner le code) : <code class="bg-gray-100 px-2 py-1 rounded font-mono">{{ $secret }}</code>
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
