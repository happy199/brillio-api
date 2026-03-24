<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Transcription de la Séance - {{ $session->title }}</title>
    <style>
        @page {
            margin: 100px 50px;
        }
        body { 
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; 
            color: #1f2937; 
            line-height: 1.6; 
            margin: 0;
            padding: 0;
        }
        .header { 
            position: fixed;
            top: -80px;
            left: 0;
            right: 0;
            height: 60px;
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 10px;
            text-align: center;
        }
        .logo {
            font-size: 24px;
            font-weight: 800;
            color: #4f46e5;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .logo span {
            color: #111827;
        }
        .footer { 
            position: fixed; 
            bottom: -60px; 
            left: 0; 
            right: 0; 
            height: 30px;
            font-size: 10px; 
            color: #9ca3af; 
            text-align: center; 
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }
        .content {
            margin-top: 20px;
        }
        .meeting-title { 
            font-size: 22px; 
            font-weight: 800; 
            color: #111827; 
            margin: 0 0 15px 0;
            text-align: center;
        }
        .metadata-box {
            background-color: #f9fafb;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
            border: 1px solid #e5e7eb;
        }
        .metadata-grid {
            width: 100%;
        }
        .metadata-item {
            width: 50%;
            vertical-align: top;
            padding-bottom: 10px;
        }
        .label {
            display: block;
            font-size: 11px;
            text-transform: uppercase;
            color: #6b7280;
            font-weight: 700;
            margin-bottom: 2px;
        }
        .value {
            display: block;
            font-size: 14px;
            color: #111827;
            font-weight: 600;
        }
        .participant-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 700;
            margin-left: 5px;
        }
        .badge-mentor { background-color: #e0e7ff; color: #4338ca; }
        .badge-jeune { background-color: #fef3c7; color: #92400e; }
        
        .transcription-section {
            margin-top: 30px;
        }
        .transcription-segment {
            margin-bottom: 15px;
            padding: 10px 0;
            border-bottom: 1px solid #f3f4f6;
        }
        .speaker-name {
            font-weight: 800;
            color: #4f46e5;
            font-size: 13px;
            margin-bottom: 4px;
        }
        .segment-text {
            font-size: 13px;
            color: #374151;
            text-align: justify;
        }
        .timestamp {
            font-size: 10px;
            color: #9ca3af;
            float: right;
        }
        .branding-tagline {
            text-align: center;
            margin-top: 50px;
            color: #4f46e5;
            font-style: italic;
            font-size: 14px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">Brillio</div>
    </div>

    <div class="footer">
        &copy; {{ date('Y') }} Brillio - Plateforme de Mentorat d'Excellence. Généré le {{ now()->format('d/m/Y à H:i') }}
    </div>

    <div class="content">
        <h1 class="meeting-title">Compte Rendu de Transcription</h1>

        <div class="metadata-box">
            <table class="metadata-grid">
                <tr>
                    <td class="metadata-item">
                        <span class="label">Titre de la séance</span>
                        <span class="value">{{ $session->title }}</span>
                    </td>
                    <td class="metadata-item">
                        <span class="label">Date & Heure</span>
                        <span class="value">{{ $session->scheduled_at->format('d/m/Y H:i') }}</span>
                    </td>
                </tr>
                <tr>
                    <td class="metadata-item">
                        <span class="label">Durée annoncée</span>
                        <span class="value">{{ $session->duration_minutes }} minutes</span>
                    </td>
                    <td class="metadata-item">
                        <span class="label">Statut</span>
                        <span class="value">{{ $session->translated_status }}</span>
                    </td>
                </tr>
                <tr>
                    <td class="metadata-item" colspan="2">
                        <span class="label">Participants</span>
                        <div style="margin-top: 5px;">
                            <span class="value" style="display:inline-block; margin-right: 15px;">
                                {{ $session->mentor->name }} <span class="participant-badge badge-mentor">MENTOR</span>
                            </span>
                            @foreach($session->mentees as $mentee)
                                <span class="value" style="display:inline-block; margin-right: 15px;">
                                    {{ $mentee->name }} <span class="participant-badge badge-jeune">JEUNE</span>
                                </span>
                            @endforeach
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="transcription-section">
            <h2 style="font-size: 18px; color: #111827; border-left: 4px solid #4f46e5; padding-left: 10px; margin-bottom: 20px;">Fil de la Discussion</h2>
            
            @php
                $transcription = $session->transcription_raw;
            @endphp

            @if(is_array($transcription))
                @foreach($transcription as $segment)
                    <div class="transcription-segment">
                        <div class="speaker-name">
                            {{ $segment['speaker'] ?? 'Anonyme' }}
                            @if(isset($segment['timestamp']))
                                <span class="timestamp">
                                    @if(is_numeric($segment['timestamp']))
                                        {{ date('H:i:s', $segment['timestamp']) }}
                                    @else
                                        {{ $segment['timestamp'] }}
                                    @endif
                                </span>
                            @endif
                        </div>
                        <div class="segment-text">
                            {{ $segment['text'] ?? ($segment['content'] ?? '') }}
                        </div>
                    </div>
                @endforeach
            @else
                <div class="segment-text" style="white-space: pre-wrap;">
                    {{ $transcription }}
                </div>
            @endif
        </div>

        <div class="branding-tagline">
            Propulsé par Brillio — Éveillez votre potentiel, facilitez votre réussite.
        </div>
    </div>
</body>
</html>
