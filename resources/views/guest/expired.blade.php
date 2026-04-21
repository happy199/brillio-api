<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lien expiré - Brillio</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
    </style>
</head>
<body class="h-full flex items-center justify-center p-6">
    <div class="max-w-md w-full text-center space-y-6">
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-red-100 text-red-600 mb-4">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Ce lien a expiré</h1>
        <p class="text-slate-500 mb-8">
            La séance <span class="font-bold text-slate-800">{{ $session->title }}</span> est déjà terminée ou le délai d'accès a été dépassé.
        </p>
        <div class="pt-6 border-t border-slate-200">
            <p class="text-sm text-slate-400">Veuillez contacter l'organisation <span class="font-semibold">{{ $session->organization->name }}</span> pour obtenir une nouvelle invitation.</p>
        </div>
    </div>
</body>
</html>
