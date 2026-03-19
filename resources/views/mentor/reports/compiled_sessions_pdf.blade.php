<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport Compilé - Séances de Mentorat</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #333; line-height: 1.6; }
        .header { text-align: center; border-bottom: 2px solid #4F46E5; padding-bottom: 20px; margin-bottom: 30px; }
        .title { font-size: 24px; font-weight: bold; color: #111827; margin: 0; }
        .meta { font-size: 14px; color: #6B7280; margin-top: 5px; }
        .section { margin-bottom: 25px; }
        .section-title { font-size: 18px; font-weight: bold; color: #4F46E5; border-bottom: 1px solid #E5E7EB; padding-bottom: 5px; margin-bottom: 15px; }
        .content { font-size: 14px; background: #F9FAFB; padding: 15px; border-radius: 8px; }
        .footer { position: fixed; bottom: -30px; left: 0; right: 0; font-size: 10px; color: #9CA3AF; text-align: center; border-top: 1px solid #E5E7EB; padding-top: 5px; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    @foreach($sessions as $index => $session)
        <div class="header">
            <h1 class="title">Compte Rendu de Séance (#{{ $index + 1 }})</h1>
            <p class="meta">
                <strong>Séance :</strong> {{ $session->title }}<br>
                <strong>Date :</strong> {{ $session->scheduled_at->format('d/m/Y à H:i') }}<br>
                <strong>Jeune(s) :</strong> {{ $session->mentees ? $session->mentees->pluck('name')->join(', ') : 'ND' }}<br>
                <strong>Mentor :</strong> {{ $session->mentor ? $session->mentor->name : 'ND' }}
            </p>
        </div>

        @php
            $report = is_array($session->report_content) ? $session->report_content : json_decode($session->report_content, true);
        @endphp

        <div class="section">
            <h2 class="section-title">Ce qui a été fait / Ce qui a progressé</h2>
            <div class="content">
                {!! nl2br(e(data_get($report, 'progress', 'Non renseigné'))) !!}
            </div>
        </div>

        <div class="section">
            <h2 class="section-title">Points de blocage / Obstacles</h2>
            <div class="content">
                {!! nl2br(e(data_get($report, 'obstacles', 'Aucun'))) !!}
            </div>
        </div>

        <div class="section">
            <h2 class="section-title">Prochains objectifs (SMART)</h2>
            <div class="content">
                {!! nl2br(e(data_get($report, 'smart_goals', 'Non renseigné'))) !!}
            </div>
        </div>

        <div class="footer">
            Généré par Brillio le {{ now()->format('d/m/Y à H:i') }} - Séance {{ $index + 1 }} sur {{ $sessions->count() }}
        </div>
        
        @if(!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach
</body>
</html>
