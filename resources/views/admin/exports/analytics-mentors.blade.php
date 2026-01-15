<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Mentors - Brillio Analytics</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        h1 {
            color: #F97316;
            font-size: 24px;
            margin-bottom: 10px;
        }

        h2 {
            color: #1F2937;
            font-size: 18px;
            margin-top: 20px;
            margin-bottom: 10px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #F97316;
            padding-bottom: 15px;
        }

        .stats {
            display: flex;
            justify-content: space-around;
            margin: 20px 0;
        }

        .stat-box {
            text-align: center;
            padding: 15px;
            background: #F3F4F6;
            border-radius: 8px;
        }

        .stat-value {
            font-size: 28px;
            font-weight: bold;
            color: #F97316;
        }

        .stat-label {
            font-size: 12px;
            color: #6B7280;
            margin-top: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th {
            background: #F97316;
            color: white;
            padding: 10px;
            text-align: left;
            font-size: 11px;
        }

        td {
            padding: 8px;
            border-bottom: 1px solid #E5E7EB;
            font-size: 10px;
        }

        tr:nth-child(even) {
            background: #F9FAFB;
        }

        .badge {
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
        }

        .badge-success {
            background: #D1FAE5;
            color: #065F46;
        }

        .badge-warning {
            background: #FEF3C7;
            color: #92400E;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #6B7280;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>📊 Rapport Mentors - Brillio</h1>
        <p>Période: {{ $dateRange['start']->format('d/m/Y') }} - {{ $dateRange['end']->format('d/m/Y') }}</p>
        <p>Généré le: {{ $generatedAt->format('d/m/Y à H:i') }}</p>
    </div>

    <div class="stats">
        <div class="stat-box">
            <div class="stat-value">{{ $stats['total'] }}</div>
            <div class="stat-label">Total mentors</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{{ $stats['published'] }}</div>
            <div class="stat-label">Publiés</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{{ $stats['pending'] }}</div>
            <div class="stat-label">En attente</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{{ $stats['with_roadmap'] }}</div>
            <div class="stat-label">Avec parcours</div>
        </div>
    </div>

    <h2>Liste des mentors</h2>
    <table>
        <thead>
            <tr>
                <th>Nom</th>
                <th>Email</th>
                <th>Domaine</th>
                <th>Expérience</th>
                <th>Statut</th>
                <th>Étapes</th>
                <th>Inscription</th>
            </tr>
        </thead>
        <tbody>
            @foreach($mentors as $mentor)
                <tr>
                    <td>{{ $mentor->user->name }}</td>
                    <td>{{ $mentor->user->email }}</td>
                    <td>{{ $mentor->specialization?->name ?? 'Non défini' }}</td>
                    <td>{{ $mentor->years_of_experience ?? 0 }} ans</td>
                    <td>
                        @if($mentor->is_published)
                            <span class="badge badge-success">Publié</span>
                        @else
                            <span class="badge badge-warning">En attente</span>
                        @endif
                    </td>
                    <td>{{ $mentor->roadmapSteps->count() }}</td>
                    <td>{{ $mentor->created_at->format('d/m/Y') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>© {{ date('Y') }} Brillio - Plateforme d'orientation professionnelle</p>
    </div>
</body>

</html>