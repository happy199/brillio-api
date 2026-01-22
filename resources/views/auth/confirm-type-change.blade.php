<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation - Brillio</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #d946ef 100%);
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-lg p-6">
        <!-- Icon -->
        <div class="text-center mb-6">
            <div class="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Compte existant trouvé</h1>
        </div>

        <!-- Message -->
        @if($isArchived)
            <p class="text-gray-600 mb-4 text-center">
                Nous avons trouvé un compte <strong class="text-gray-900">{{ ucfirst($oldType) }}</strong> archivé avec
                l'email :
                <br>
                <strong>{{ $user->email }}</strong>
            </p>
        @else
            <p class="text-gray-600 mb-4 text-center">
                Vous avez déjà un compte <strong class="text-gray-900">{{ ucfirst($oldType) }}</strong> actif avec cet
                email.
            </p>
        @endif

        <p class="text-gray-700 font-medium mb-6 text-center">
            Voulez-vous le réactiver en tant que <strong class="text-orange-600">{{ ucfirst($newType) }}</strong> ?
        </p>

        <!-- Actions -->
        <div class="space-y-3">
            <!-- Option 1 : Migrer (conserver toutes les données) -->
            <form action="{{ route('auth.confirm-type-change.post') }}" method="POST">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="action" value="migrate">
                <button type="submit"
                    class="w-full px-6 py-3 bg-gradient-to-r from-orange-500 to-red-500 text-white font-semibold rounded-xl hover:shadow-lg transition transform hover:scale-105">
                    ✅ Oui, devenir {{ ucfirst($newType) }}
                </button>
                <p class="text-xs text-gray-500 mt-2 text-center">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Toutes vos données seront conservées
                </p>
            </form>

            <!-- Option 2 : Garder le type actuel -->
            <form action="{{ route('auth.confirm-type-change.post') }}" method="POST">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="action" value="keep">
                <button type="submit"
                    class="w-full px-6 py-3 border-2 border-gray-300 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition">
                    ❌ Non, rester {{ ucfirst($oldType) }}
                </button>
            </form>
        </div>

        <!-- Info supplémentaire -->
        <div class="mt-6 p-4 bg-blue-50 rounded-xl border border-blue-200">
            <p class="text-sm text-blue-800">
                <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                        clip-rule="evenodd" />
                </svg>
                Ce lien expirera dans 24 heures
            </p>
        </div>
    </div>
</body>

</html>