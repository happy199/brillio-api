<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Brillio - Rapport Analytics Chat</title>
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
    </style>
</head>
<body>
    <div class="header">
        <h1>Brillio - Analytics Chatbot</h1>
        <p>Rapport d'utilisation du chatbot IA</p>
    </div>

    <div class="period">
        <strong>Période :</strong>
        {{ $dateRange['start']->format('d/m/Y') }} - {{ $dateRange['end']->format('d/m/Y') }}
    </div>

    <div class="section">
        <h2>Statistiques globales</h2>
        <table>
            <tr>
                <th>Métrique</th>
                <th>Valeur</th>
            </tr>
            <tr>
                <td>Total des messages</td>
                <td>{{ number_format($stats['total']) }}</td>
            </tr>
            <tr>
                <td>Messages utilisateurs</td>
                <td>{{ number_format($stats['user']) }}</td>
            </tr>
            <tr>
                <td>Réponses IA</td>
                <td>{{ number_format($stats['assistant']) }}</td>
            </tr>
            <tr>
                <td>Conversations</td>
                <td>{{ number_format($stats['conversations']) }}</td>
            </tr>
            <tr>
                <td>Sessions avec conseiller humain</td>
                <td>{{ number_format($stats['human_sessions']) }}</td>
            </tr>
        </table>
    </div>

    @if($topUsers->count() > 0)
    <div class="section">
        <h2>Utilisateurs les plus actifs</h2>
        <table>
            <tr>
                <th>Nom</th>
                <th>Email</th>
                <th>Messages</th>
            </tr>
            @foreach($topUsers as $user)
            <tr>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ number_format($user->message_count) }}</td>
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
