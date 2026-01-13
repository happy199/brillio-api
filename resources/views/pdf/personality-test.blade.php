<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Test de Personnalité - {{ $test->personality_type }}</title>
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

        .section {
            margin: 20px 0;
        }

        .section h2 {
            color: #ea580c;
            border-bottom: 2px solid #fed7aa;
            padding-bottom: 5px;
        }

        .badge {
            display: inline-block;
            background: #fed7aa;
            color: #9a3412;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
        }

        .career-item {
            margin: 10px 0;
            padding: 10px;
            background: #fff7ed;
            border-left: 3px solid #f97316;
        }

        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Test de Personnalité MBTI</h1>
        <p><strong>{{ $user->name }}</strong></p>
        <p>Date : {{ $test->completed_at->format('d/m/Y') }}</p>
    </div>

    <div class="section">
        <h2>Votre Type de Personnalité</h2>
        <p><span class="badge">{{ $test->personality_type }} - {{ $test->personality_label }}</span></p>
        <p>{{ $test->personality_description }}</p>
    </div>

    @if($test->recommended_careers && count($test->recommended_careers) > 0)
        <div class="section">
            <h2>Métiers Recommandés</h2>
            @foreach($test->recommended_careers as $career)
                <div class="career-item">
                    <strong>{{ $career['title'] }}</strong><br>
                    {{ $career['description'] }}<br>
                    <em>Pourquoi : {{ $career['match_reason'] }}</em>
                </div>
            @endforeach
        </div>
    @endif

    <div class="footer">
        <p>Document généré par Brillio - Plateforme d'orientation professionnelle</p>
        <p>© {{ date('Y') }} Brillio. Tous droits réservés.</p>
    </div>
</body>

</html>