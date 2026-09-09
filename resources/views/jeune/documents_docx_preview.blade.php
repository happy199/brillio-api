<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $document->file_name }} - Aperçu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
    </style>
</head>
<body class="bg-gray-100 p-4 sm:p-6 text-gray-800 antialiased">
    <div class="max-w-3xl mx-auto space-y-4">
        <!-- Barre d'actions du document Word -->
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-200 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center flex-shrink-0 font-bold text-xs border border-blue-200">
                    DOCX
                </div>
                <div class="min-w-0">
                    <h1 class="text-sm font-bold text-gray-900 truncate">{{ $document->file_name }}</h1>
                    <p class="text-xs text-gray-500">
                        {{ $document->formatted_file_size }} • Ajouté le {{ $document->created_at ? $document->created_at->format('d/m/Y') : date('d/m/Y') }}
                    </p>
                </div>
            </div>
            <a href="{{ route('jeune.documents.download', $document->id) }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span>Télécharger le fichier (.docx)</span>
            </a>
        </div>

        <!-- Feuille de lecture du document Word -->
        <div class="bg-white rounded-2xl p-6 sm:p-10 shadow-sm border border-gray-200 min-h-[500px]">
            <div class="pb-4 mb-6 border-b border-gray-200 flex items-center justify-between text-xs text-gray-500 uppercase tracking-wider font-semibold">
                <span>Contenu du document Word</span>
                <span class="text-blue-600 lowercase font-normal">aperçu textuel sécurisé</span>
            </div>
            @if(trim($content) !== '')
                <div class="whitespace-pre-wrap font-sans text-xs sm:text-sm text-gray-800 leading-relaxed space-y-2">
                    {{ $content }}
                </div>
            @else
                <div class="py-12 text-center text-gray-400 space-y-2">
                    <p class="text-sm font-medium">Ce document Word ne contient pas de texte brut directement extractible.</p>
                    <p class="text-xs">Vous pouvez télécharger le fichier original pour l'ouvrir dans Microsoft Word.</p>
                </div>
            @endif
        </div>
    </div>
</body>
</html>
