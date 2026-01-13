<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Historique des Tests de Personnalité</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #f97316;
            padding-bottom: 20px;
        }

        .header h1 {
            color: #f97316;
            margin: 0;
        }

        .test-item {
            margin: 20px 0;
            padding: 15px;
            background: #fff7ed;
            border-left: 4px solid #f97316;
            page-break-inside: avoid;
        }

        .test-item h3 {
            color: #ea580c;
            margin-top: 0;
        }

        .badge {
            display: inline-block;
            background: #fed7aa;
            color: #9a3412;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            margin: 5px 0;
        }

        .current {
            background: #22c55e;
            color: white;
        }

        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 12px;
            color: #666;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        th,
        td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background: #fed7aa;
            color: #9a3412;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Historique des Tests de Personnalité</h1>
        <p><strong>{{ $user->name }}</strong></p>
        <p>Nombre total de tests : {{ $tests->count() }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Type MBTI</th>
                <th>Label</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tests as $test)
                <tr>
                    <td>{{ $test->completed_at->format('d/m/Y') }}</td>
                    <td><strong>{{ $test->personality_type }}</strong></td>
                    <td>{{ $test->personality_label }}</td>
                    <td>
                        @if($test->is_current)
                            <span class="badge current">Actuel</span>
                        @else
                            <span class="badge">Historique</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @foreach($tests as $test)
        <div class="test-item">
            <h3>
                {{ $test->personality_type }} - {{ $test->personality_label }}
                @if($test->is_current)
                    <span class="badge current">Test Actuel</span>
                @endif
            </h3>
            <p><strong>Date :</strong> {{ $test->completed_at->format('d/m/Y à H:i') }}</p>
            <p><strong>Description :</strong> {{ $test->personality_description }}</p>
        </div>
    @endforeach

    <div class="footer">
        <p>Document généré par Brillio - Plateforme d'orientation professionnelle</p>
        <p>© {{ date('Y') }} Brillio. Tous droits réservés.</p>
    </div>
</body>

</html>