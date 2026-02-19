@extends('emails.layouts.base')

@section('content')
<p style="margin: 0 0 20px;">Bonjour <strong>{{ $user->name }}</strong>,</p>

<p style="margin: 0 0 30px; font-size: 18px;">
    📩 Votre demande de retrait a été bien reçue.
</p>

<div style="background-color: #f9fafb; border-radius: 8px; padding: 20px; margin: 20px 0; border: 1px solid #e5e7eb;">
    <p style="margin: 0 0 15px; font-weight: 600; color: #111827; font-size: 16px;">🏦 Détails du retrait</p>
    <p style="margin: 0 0 8px; color: #374151;">
        <strong>Montant brut :</strong> {{ number_format($payout->amount, 0, ',', ' ') }} FCFA<br>
        <strong>Frais de service :</strong> {{ number_format($payout->fee, 0, ',', ' ') }} FCFA<br>
        <strong>Montant net à recevoir :</strong> <span style="font-weight: 700;">{{ number_format($payout->net_amount,
            0, ',', ' ') }} FCFA</span>
    </p>
    <p style="margin: 15px 0 0; color: #4b5563;">
        <strong>Méthode :</strong> {{ $payout->payment_method }} ({{ $payout->phone_number }})
    </p>
</div>

<p style="margin: 20px 0; color: #374151;">
    Notre équipe traite les demandes sous 24h à 48h ouvrées. Vous recevrez un e-mail dès que le virement sera effectué.
</p>

<table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
    <tr>
        <td align="center">
            <a href="{{ route('mentor.wallet.index') }}"
                style="display: inline-block; background-color: #4b5563; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 14px;">
                Historique des transactions
            </a>
        </td>
    </tr>
</table>

<p style="margin: 30px 0 0; color: #374151;">
    Cordialement,<br>
    <strong>L'équipe Brillio</strong>
</p>
@endsection