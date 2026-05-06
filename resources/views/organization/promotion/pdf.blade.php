<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des Prospects - {{ $organization->name }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; line-height: 1.5; font-size: 12px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #f43f5e; padding-bottom: 10px; }
        .logo { max-height: 60px; margin-bottom: 10px; }
        h1 { color: #f43f5e; margin: 0; font-size: 24px; }
        .stats { margin-bottom: 20px; background: #f8fafc; padding: 15px; border-radius: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background-color: #f1f5f9; text-align: left; padding: 10px; border-bottom: 1px solid #e2e8f0; font-weight: bold; }
        td { padding: 10px; border-bottom: 1px solid #f1f5f9; }
        .footer { margin-top: 30px; text-align: center; color: #94a3b8; font-size: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Liste des Prospects</h1>
        <p>{{ $organization->name }} - {{ now()->format('d/m/Y') }}</p>
    </div>

    <div class="stats">
        <p><strong>Total des clics :</strong> {{ number_format($clicks->count()) }}</p>
        <p><strong>Date d'extraction :</strong> {{ now()->translatedFormat('d F Y à H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Nom</th>
                <th>Email</th>
                <th>Ville / Pays</th>
                <th>Date du clic</th>
            </tr>
        </thead>
        <tbody>
            @foreach($clicks as $click)
            <tr>
                <td>{{ $click->user?->name ?? 'Anonyme' }}</td>
                <td>{{ $click->user?->email ?? '-' }}</td>
                <td>{{ $click->user?->city ?? '-' }} / {{ $click->user?->country ?? '-' }}</td>
                <td>{{ $click->created_at->format('d/m/Y H:i') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Document généré par Brillio Partner pour {{ $organization->name }}.
    </div>
</body>
</html>
