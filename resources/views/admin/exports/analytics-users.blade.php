<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Brillio - Rapport Utilisateurs</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #333;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 15px;
        }
        .header h1 {
            color: #4f46e5;
            margin: 0;
            font-size: 20px;
        }
        .section {
            margin-bottom: 20px;
        }
        .section h2 {
            color: #4f46e5;
            font-size: 14px;
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
            padding: 5px;
            text-align: left;
        }
        th {
            background: #f9fafb;
            font-size: 9px;
        }
        td {
            font-size: 9px;
        }
        .period {
            background: #eef2ff;
            padding: 8px;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 11px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            color: #999;
            font-size: 9px;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }
        .stats-summary {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        .stat-box {
            display: table-cell;
            width: 25%;
            text-align: center;
            padding: 8px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
        }
        .stat-number {
            font-size: 18px;
            font-weight: bold;
            color: #4f46e5;
        }
        .stat-label {
            font-size: 9px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Brillio - Liste des Utilisateurs</h1>
    </div>

    <div class="period">
        <strong>Période :</strong>
        {{ $dateRange['start']->format('d/m/Y') }} - {{ $dateRange['end']->format('d/m/Y') }}
    </div>

    <div class="stats-summary">
        <div class="stat-box">
            <div class="stat-number">{{ $stats['total'] }}</div>
            <div class="stat-label">Total</div>
        </div>
        <div class="stat-box">
            <div class="stat-number">{{ $stats['jeunes'] }}</div>
            <div class="stat-label">Jeunes</div>
        </div>
        <div class="stat-box">
            <div class="stat-number">{{ $stats['mentors'] }}</div>
            <div class="stat-label">Mentors</div>
        </div>
        <div class="stat-box">
            <div class="stat-number">{{ $stats['with_test'] }}</div>
            <div class="stat-label">Avec test MBTI</div>
        </div>
    </div>

    @if($users->count() > 0)
    <div class="section">
        <h2>Liste des utilisateurs inscrits</h2>
        <table>
            <tr>
                <th>Nom</th>
                <th>Email</th>
                <th>Type</th>
                <th>Pays</th>
                <th>MBTI</th>
                <th>Inscription</th>
            </tr>
            @foreach($users as $user)
            <tr>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ ucfirst($user->user_type) }}</td>
                <td>{{ $user->country ?? '-' }}</td>
                <td>{{ $user->personalityTest && $user->personalityTest->completed_at ? $user->personalityTest->personality_type : '-' }}</td>
                <td>{{ $user->created_at->format('d/m/Y') }}</td>
            </tr>
            @endforeach
        </table>
    </div>
    @else
    <p style="text-align: center; color: #666;">Aucun utilisateur inscrit sur cette période.</p>
    @endif

    <div class="footer">
        <p>Rapport généré le {{ $generatedAt->format('d/m/Y à H:i') }}</p>
        <p>Brillio - La plateforme d'orientation pour les jeunes africains</p>
    </div>
</body>
</html>
