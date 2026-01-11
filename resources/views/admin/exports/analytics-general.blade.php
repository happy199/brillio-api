<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Brillio - Rapport Analytics Général</title>
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
        .header p {
            color: #666;
            margin: 5px 0 0;
        }
        .section {
            margin-bottom: 25px;
        }
        .section h2 {
            color: #4f46e5;
            font-size: 16px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }
        .stats-grid {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        .stat-item {
            display: table-cell;
            width: 25%;
            text-align: center;
            padding: 10px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #4f46e5;
        }
        .stat-label {
            color: #666;
            font-size: 10px;
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
            font-weight: bold;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            color: #999;
            font-size: 10px;
            border-top: 1px solid #e5e7eb;
            padding-top: 15px;
        }
        .period {
            background: #eef2ff;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Brillio - Rapport Analytics</h1>
        <p>Rapport général d'activité</p>
    </div>

    <div class="period">
        <strong>Période :</strong>
        {{ $dateRange['start']->format('d/m/Y') }} - {{ $dateRange['end']->format('d/m/Y') }}
    </div>

    <div class="section">
        <h2>Utilisateurs</h2>
        <table>
            <tr>
                <th>Métrique</th>
                <th>Total</th>
                <th>Période</th>
            </tr>
            <tr>
                <td>Utilisateurs inscrits</td>
                <td>{{ number_format($stats['users']['total']) }}</td>
                <td>{{ number_format($stats['users']['period']) }}</td>
            </tr>
            <tr>
                <td>Jeunes</td>
                <td>-</td>
                <td>{{ number_format($stats['users']['jeunes']) }}</td>
            </tr>
            <tr>
                <td>Mentors</td>
                <td>-</td>
                <td>{{ number_format($stats['users']['mentors']) }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h2>Tests de personnalité</h2>
        <table>
            <tr>
                <th>Métrique</th>
                <th>Valeur</th>
            </tr>
            <tr>
                <td>Tests complétés (total)</td>
                <td>{{ number_format($stats['tests']['total']) }}</td>
            </tr>
            <tr>
                <td>Tests complétés (période)</td>
                <td>{{ number_format($stats['tests']['period']) }}</td>
            </tr>
            <tr>
                <td>Taux de complétion</td>
                <td>{{ $stats['tests']['completion_rate'] }}%</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h2>Chatbot IA</h2>
        <table>
            <tr>
                <th>Métrique</th>
                <th>Valeur</th>
            </tr>
            <tr>
                <td>Conversations (période)</td>
                <td>{{ number_format($stats['chat']['conversations']) }}</td>
            </tr>
            <tr>
                <td>Messages (période)</td>
                <td>{{ number_format($stats['chat']['messages']) }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h2>Documents</h2>
        <table>
            <tr>
                <th>Métrique</th>
                <th>Valeur</th>
            </tr>
            <tr>
                <td>Documents (total)</td>
                <td>{{ number_format($stats['documents']['total']) }}</td>
            </tr>
            <tr>
                <td>Documents (période)</td>
                <td>{{ number_format($stats['documents']['period']) }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h2>Mentors</h2>
        <table>
            <tr>
                <th>Métrique</th>
                <th>Valeur</th>
            </tr>
            <tr>
                <td>Mentors actifs</td>
                <td>{{ number_format($stats['mentors']['active']) }}</td>
            </tr>
            <tr>
                <td>En attente de validation</td>
                <td>{{ number_format($stats['mentors']['pending']) }}</td>
            </tr>
        </table>
    </div>

    @if($stats['countries']->count() > 0)
    <div class="section">
        <h2>Répartition par pays</h2>
        <table>
            <tr>
                <th>Pays</th>
                <th>Nombre d'utilisateurs</th>
            </tr>
            @foreach($stats['countries'] as $country)
            <tr>
                <td>{{ $country->country }}</td>
                <td>{{ number_format($country->total) }}</td>
            </tr>
            @endforeach
        </table>
    </div>
    @endif

    @if($stats['personality_types']->count() > 0)
    <div class="section">
        <h2>Types de personnalité (période)</h2>
        <table>
            <tr>
                <th>Type MBTI</th>
                <th>Nombre</th>
            </tr>
            @foreach($stats['personality_types'] as $type)
            <tr>
                <td>{{ $type->personality_type }}</td>
                <td>{{ number_format($type->count) }}</td>
            </tr>
            @endforeach
        </table>
    </div>
    @endif

    <div class="footer">
        <p>Rapport généré le {{ $generatedAt->format('d/m/Y à H:i') }}</p>
        <p>Brillio - La plateforme d'orientation pour les jeunes africains</p>
    </div>
</body>
</html>
