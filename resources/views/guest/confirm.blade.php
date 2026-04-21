<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accès Séance - Brillio</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .glass {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
    </style>
</head>
<body class="h-full selection:bg-indigo-100 flex items-center justify-center p-4 sm:p-6 lg:p-8">

    <div class="max-w-md w-full space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
        <!-- Logo -->
        <div class="text-center">
            <img class="mx-auto h-16 w-auto" src="{{ asset('assets/logo/logo-brillio.png') }}" alt="Brillio">
            <h2 class="mt-6 text-3xl font-extrabold text-slate-900 tracking-tight">Bienvenue, {{ $session->mentor->name }}</h2>
            <p class="mt-2 text-sm text-slate-500">
                Vous avez été invité par <span class="font-bold text-indigo-600">{{ $session->organization->name }}</span> pour animer une séance collective.
            </p>
        </div>

        <!-- Info Card -->
        <div class="glass rounded-3xl p-8 shadow-2xl shadow-indigo-100/50 space-y-6">
            <div class="space-y-4">
                <div class="flex items-center space-x-4 p-4 rounded-2xl bg-indigo-50 border border-indigo-100">
                    <div class="flex-shrink-0 w-12 h-12 rounded-full bg-indigo-600 flex items-center justify-center text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-indigo-700 uppercase tracking-widest">Séance Vidéo</p>
                        <h3 class="text-lg font-bold text-slate-900 leading-tight">{{ $session->title }}</h3>
                        <p class="text-xs text-slate-500">{{ $session->scheduled_at->translatedFormat('d F Y \à H:i') }} ({{ $session->duration_minutes }} min)</p>
                    </div>
                </div>

                <p class="text-xs text-center text-slate-400">
                    Pour garantir la sécurité de la réunion, veuillez confirmer votre identité en saisissant votre adresse email ci-dessous.
                </p>
            </div>

            <form class="space-y-6" action="{{ route('guest.sessions.handle-confirm', [$session, $token]) }}" method="POST">
                @csrf
                <div>
                    <label for="email" class="sr-only">Adresse email</label>
                    <input id="email" name="email" type="email" autocomplete="email" required 
                        placeholder="votre@email.com"
                        class="block w-full px-5 py-4 rounded-2xl border-none bg-slate-100 focus:bg-white focus:ring-2 focus:ring-indigo-600 transition-all text-slate-900 font-medium @error('email') ring-2 ring-red-500 @enderror">
                    @if(session('error'))
                        <p class="mt-2 text-xs text-red-500 font-medium px-2">{{ session('error') }}</p>
                    @endif
                </div>

                <button type="submit" class="w-full py-4 px-6 rounded-2xl bg-slate-900 hover:bg-black text-white font-bold text-lg shadow-lg hover:shadow-xl transition-all active:scale-95 flex items-center justify-center group">
                    <span>Rejoindre la séance</span>
                    <svg class="ml-3 w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </button>
            </form>
        </div>

        <p class="text-center text-xs text-slate-400">
            &copy; {{ date('Y') }} Brillio. Tous droits réservés.
        </p>
    </div>

</body>
</html>
