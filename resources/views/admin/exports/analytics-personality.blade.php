<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Brillio - Rapport Analytics Personnalité</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.5;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #4f46e5;
            margin: 0;
            font-size: 24px;
        }
        .section {
            margin-bottom: 25px;
        }
        .section h2 {
            color: #4f46e5;
            font-size: 16px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #e5e7eb;
            padding: 8px;
            text-align: left;
        }
        th {
            background: #f9fafb;
        }
        .period {
            background: #eef2ff;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            color: #999;
            font-size: 10px;
            border-top: 1px solid #e5e7eb;
            padding-top: 15px;
        }
        .highlight {
            background: #eef2ff;
            padding: 15px;
            text-align: center;
            margin-bottom: 20px;
        }
        .highlight .number {
            font-size: 36px;
            font-weight: bold;
            color: #4f46e5;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Brillio - Analytics Tests de Personnalité</h1>
        <p>Rapport de distribution des types MBTI</p>
    </div>

    <div class="period">
        <strong>Période :</strong>
        {{ $dateRange['start']->format('d/m/Y') }} - {{ $dateRange['end']->format('d/m/Y') }}
    </div>

    <div class="highlight">
        <div class="number">{{ number_format($total) }}</div>
        <div>Tests complétés sur la période</div>
    </div>

    @if($distribution->count() > 0)
    <div class="section">
        <h2>Distribution des types de personnalité</h2>
        <table>
            <tr>
                <th>Type MBTI</th>
                <th>Label</th>
                <th>Nombre</th>
                <th>Pourcentage</th>
            </tr>
            @foreach($distribution as $item)
            <tr>
                <td><strong>{{ $item->personality_type }}</strong></td>
                <td>{{ $item->personality_label ?? '-' }}</td>
                <td>{{ number_format($item->count) }}</td>
                <td>{{ $total > 0 ? round(($item->count / $total) * 100, 1) : 0 }}%</td>
            </tr>
            @endforeach
        </table>
    </div>
    @else
    <p style="text-align: center; color: #666;">Aucun test complété sur cette période.</p>
    @endif

    <div class="footer">
        <p>Rapport généré le {{ $generatedAt->format('d/m/Y à H:i') }}</p>
        <p>Brillio - La plateforme d'orientation pour les jeunes africains</p>
    </div>
</body>
</html>
