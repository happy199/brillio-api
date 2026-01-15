<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Newsletter Subscribers</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        h1 {
            color: #333;
        }

        .stats {
            margin: 20px 0;
        }

        .stat-box {
            display: inline-block;
            padding: 10px;
            margin: 5px;
            border: 1px solid #ddd;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f4f4f4;
        }

        .active {
            color: green;
        }

        .unsubscribed {
            color: red;
        }
    </style>
</head>

<body>
    <h1>Newsletter - Abonnés</h1>
    <p>Généré le {{ now()->format('d/m/Y à H:i') }}</p>

    <div class="stats">
        <div class="stat-box">
            <strong>Total:</strong> {{ $stats['total'] }}
        </div>
        <div class="stat-box">
            <strong>Actifs:</strong> {{ $stats['active'] }}
        </div>
        <div class="stat-box">
            <strong>Désabonnés:</strong> {{ $stats['unsubscribed'] }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Email</th>
                <th>Statut</th>
                <th>Inscrit le</th>
                <th>Désabonné le</th>
            </tr>
        </thead>
        <tbody>
            @foreach($subscribers as $subscriber)
                <tr>
                    <td>{{ $subscriber->email }}</td>
                    <td class="{{ $subscriber->status }}">{{ ucfirst($subscriber->status) }}</td>
                    <td>{{ $subscriber->subscribed_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $subscriber->unsubscribed_at?->format('d/m/Y H:i') ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>