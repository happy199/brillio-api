<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture Brillio</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
            line-height: 1.5;
            font-size: 14px;
            margin: 0;
            padding: 20px;
        }
        .header {
            width: 100%;
            border-bottom: 2px solid #6B46C1;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .logo {
            color: #6B46C1;
            font-size: 32px;
            font-weight: bold;
            margin: 0;
        }
        .company-details, .client-details {
            width: 50%;
            display: inline-block;
            vertical-align: top;
        }
        .client-details {
            text-align: right;
        }
        .title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #1F2937;
        }
        .invoice-meta {
            margin-bottom: 40px;
            width: 100%;
        }
        .invoice-meta td {
            padding: 5px 0;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .items-table th {
            background-color: #F3F4F6;
            color: #4B5563;
            padding: 12px;
            text-align: left;
            border-bottom: 2px solid #E5E7EB;
        }
        .items-table td {
            padding: 15px 12px;
            border-bottom: 1px solid #E5E7EB;
        }
        .total-row td {
            font-weight: bold;
            font-size: 16px;
            background-color: #F9FAFB;
        }
        .text-right {
            text-align: right;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #6B7280;
            border-top: 1px solid #E5E7EB;
            padding-top: 20px;
        }
    </style>
</head>
<body>

    <table class="header">
        <tr>
            <td style="width: 50%;">
                <h1 class="logo">Brillio Africa</h1>
                <p style="margin: 5px 0 0 0; color: #6B7280;">Le carrefour de l'orientation</p>
                <br>
                <div style="color: #4B5563;">
                    <!-- Placeholders to be customized by user later -->
                    Brillio Africa<br>
                    Abidjan, Côte d'Ivoire<br>
                    Email: contact@brillio.africa
                </div>
            </td>
            <td style="width: 50%; text-align: right; vertical-align: top;">
                <h2 class="title">FACTURE</h2>
                <div style="color: #4B5563;">
                    <strong>Numéro :</strong> {{ $transaction->moneroo_transaction_id }}<br>
                    <strong>Date :</strong> {{ $transaction->completed_at ? $transaction->completed_at->format('d/m/Y') : now()->format('d/m/Y') }}<br>
                    <strong>Statut :</strong> <span style="color: #10B981; font-weight: bold;">PAYÉE</span>
                </div>
            </td>
        </tr>
    </table>

    <div style="margin-bottom: 40px;">
        <h3 style="color: #4B5563; margin-bottom: 10px; font-size: 16px;">FACTURE À :</h3>
        <p style="margin: 0; font-size: 18px; font-weight: bold; color: #111827;">{{ $entity->name ?? '' }}</p>
        <p style="margin: 5px 0 0 0; color: #4B5563;">
            {{ $entity->email ?? '' }}<br>
            @if(isset($entity->country))
                {{ $entity->city ? $entity->city.', ' : '' }}{{ $entity->country }}
            @endif
        </p>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th>Description</th>
                <th class="text-right">Montant</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <strong>{{ $transaction->metadata['description'] ?? 'Achat sur Brillio' }}</strong>
                    @if($transaction->credits_amount > 0)
                        <br><span style="color: #6B7280; font-size: 12px;">+ {{ $transaction->credits_amount }} crédits ajoutés au portefeuille</span>
                    @endif
                </td>
                <td class="text-right">
                    {{ number_format($transaction->amount, 0, ',', ' ') }} {{ $transaction->currency }}
                </td>
            </tr>
            <tr>
                <td class="text-right" style="padding-top: 30px;">Sous-total :</td>
                <td class="text-right" style="padding-top: 30px;">{{ number_format($transaction->amount, 0, ',', ' ') }} {{ $transaction->currency }}</td>
            </tr>
            <tr class="total-row">
                <td class="text-right">TOTAL PAYÉ :</td>
                <td class="text-right" style="color: #6B46C1;">{{ number_format($transaction->amount, 0, ',', ' ') }} {{ $transaction->currency }}</td>
            </tr>
        </tbody>
    </table>

    <div style="margin-top: 40px; background-color: #F9FAFB; padding: 15px; border-radius: 4px;">
        <strong>Mode de paiement :</strong> Moneroo Gateway<br>
        <strong>Moyen utilisé :</strong> {{ $transaction->metadata['payment_method'] ?? 'En ligne' }}
    </div>

    <div class="footer">
        Ceci est une facture générée électroniquement et est valide sans signature.<br>
        Merci de votre confiance. L'équipe Brillio Africa.
    </div>

</body>
</html>
