<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Historique des Transactions - Brillio</title>
    <style>
        body {
            font-family: 'Helvetica', sans-serif;
            color: #333;
            line-height: 1.5;
            font-size: 11px;
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
            table-layout: fixed;
        }

        th {
            background-color: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
            padding: 8px;
            text-align: left;
            color: #6b7280;
            font-size: 9px;
            text-transform: uppercase;
        }

        td {
            border-bottom: 1px solid #e5e7eb;
            padding: 8px;
            vertical-align: top;
            word-wrap: break-word;
        }

        .amount {
            text-align: right;
            font-weight: bold;
        }

        .positive {
            color: #059669;
        }

        .negative {
            color: #dc2626;
        }

        .footer {
            text-align: center;
            font-size: 9px;
            color: #9ca3af;
            margin-top: 50px;
        }

        .badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 9px;
            background: #f3f4f6;
            color: #374151;
        }

        .total-box {
            background: #fdf2f8;
            padding: 15px;
            border-radius: 8px;
            text-align: right;
            margin-top: 20px;
        }

        .total-label {
            font-size: 12px;
            color: #4b5563;
        }

        .total-amount {
            font-size: 18px;
            font-weight: bold;
            color: #db2777;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="logo">BRILLIO</div>
        <div class="title">Historique Complet des Transactions</div>
    </div>

    <div class="info">
        <table>
            <tr>
                <td>
                    <strong>Organisation :</strong> {{ $organization->name }}<br>
                    <strong>Email :</strong> {{ $organization->contact_email ?? auth()->user()->email }}
                </td>
                <td style="text-align: right;">
                    <strong>Document généré le :</strong> {{ now()->format('d/m/Y H:i') }}<br>
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
                <th width="15%">Date</th>
                <th width="15%">Type</th>
                <th width="40%">Description</th>
                <th width="15%" style="text-align: right;">Crédits</th>
                <th width="15%" style="text-align: right;">Valeur (FCFA)</th>
            </tr>
        </thead>
        <tbody>
            @php $balanceCredits = 0; $balanceFcfa = 0; @endphp
            @foreach($transactions as $t)
            @php
            $credits = $t->amount;
            $fcfa = $credits * $creditPrice;
            $balanceCredits += $credits;
            $balanceFcfa += $fcfa;
            @endphp
            <tr>
                <td style="white-space: nowrap;">{{ $t->created_at->format('d/m/Y') }}</td>
                <td><span class="badge">
                        {{ match(strtolower($t->type)) {
                        'purchase', 'recharge' => 'Achat',
                        'subscription' => 'Abonnement',
                        'expense' => 'Ressource',
                        'distribution' => 'Distribution',
                        default => ucfirst($t->type)
                        } }}
                    </span></td>
                <td>{{ $t->description }}</td>
                <td class="amount {{ $credits > 0 ? 'positive' : 'negative' }}">
                    {{ $credits > 0 ? '+' : '' }}{{ number_format($credits) }}
                </td>
                <td class="amount {{ $fcfa > 0 ? 'positive' : 'negative' }}">
                    {{ $fcfa > 0 ? '+' : '' }}{{ number_format($fcfa) }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-box">
        <span class="total-label">Bilan sur la période :</span><br>
        <span class="total-amount">{{ $balanceFcfa >= 0 ? '+' : '' }}{{ number_format($balanceFcfa) }} FCFA</span><br>
        <small style="color: #6b7280;">({{ number_format($balanceCredits) }} crédits au total)</small>
    </div>

    <div class="footer">
        Document généré automatiquement par Brillio.<br>
        Ce document récapitule l'ensemble des mouvements de crédits (rechargements et dépenses) de votre organisation.
    </div>
</body>

</html>