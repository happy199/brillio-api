<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Justificatif de Dépenses - Brillio</title>
    <style>
        body {
            font-family: 'Helvetica', sans-serif;
            color: #333;
            line-height: 1.5;
            font-size: 12px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #db2777;
            pb: 10px;
        }

        .logo {
            font-size: 24px;
            font-bold: true;
            color: #db2777;
        }

        .title {
            font-size: 18px;
            margin-top: 10px;
        }

        .info {
            margin-bottom: 20px;
        }

        .info table {
            width: 100%;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th {
            background-color: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
            padding: 10px;
            text-align: left;
            color: #6b7280;
            font-size: 10px;
            text-transform: uppercase;
        }

        td {
            border-bottom: 1px solid #e5e7eb;
            padding: 10px;
            vertical-align: top;
        }

        .amount {
            text-align: right;
            font-weight: bold;
        }

        .footer {
            text-align: center;
            font-size: 10px;
            color: #9ca3af;
            margin-top: 50px;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            background: #f3f4f6;
        }

        .total-box {
            background: #fdf2f8;
            padding: 15px;
            border-radius: 8px;
            text-align: right;
            margin-top: 20px;
        }

        .total-label {
            font-size: 14px;
            color: #4b5563;
        }

        .total-amount {
            font-size: 20px;
            font-weight: bold;
            color: #db2777;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="logo">BRILLIO</div>
        <div class="title">Justificatif des Dépenses</div>
    </div>

    <div class="info">
        <table>
            <tr>
                <td>
                    <strong>Organisation :</strong> {{ $organization->name }}<br>
                    <strong>Email :</strong> {{ $organization->email }}
                </td>
                <td style="text-align: right;">
                    <strong>Date :</strong> {{ now()->format('d/m/Y') }}<br>
                    @if($date_from || $date_to)
                    <strong>Période :</strong>
                    {{ $date_from ? \Illuminate\Support\Carbon::parse($date_from)->format('d/m/Y') : 'Début' }}
                    -
                    {{ $date_to ? \Illuminate\Support\Carbon::parse($date_to)->format('d/m/Y') : 'Aujourd\'hui' }}
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Désignation / Description</th>
                <th style="text-align: right;">Crédits</th>
                <th style="text-align: right;">Valeur (FCFA)</th>
            </tr>
        </thead>
        <tbody>
            @php $totalCredits = 0; $totalFcfa = 0; @endphp
            @foreach($transactions as $t)
            @php
            $credits = abs($t->amount);
            $fcfa = $credits * $creditPrice;
            $totalCredits += $credits;
            $totalFcfa += $fcfa;
            @endphp
            <tr>
                <td style="white-space: nowrap;">{{ $t->created_at->format('d/m/Y') }}</td>
                <td><span class="badge">{{ ucfirst($t->type) }}</span></td>
                <td>{{ $t->description }}</td>
                <td class="amount">{{ number_format($credits) }}</td>
                <td class="amount">{{ number_format($fcfa) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-box">
        <span class="total-label">Total des dépenses :</span><br>
        <span class="total-amount">{{ number_format($totalFcfa) }} FCFA</span><br>
        <small style="color: #6b7280;">({{ number_format($totalCredits) }} crédits consommés)</small>
    </div>

    <div class="footer">
        Document généré automatiquement par Brillio - Plateforme d'accompagnement et de mentorat.<br>
        Une preuve de vos investissements dans le développement des jeunes.
    </div>
</body>

</html>