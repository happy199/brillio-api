@extends('emails.layouts.base')

@section('content')
<p style="margin: 0 0 20px;">Bonjour <strong>{{ $mentor->name }}</strong>,</p>

<div style="background-color: #fffbeb; border-radius: 8px; padding: 20px; margin: 20px 0; border: 1px solid #fde68a;">
    <p style="margin: 0 0 15px; font-weight: 600; color: #92400e; font-size: 16px;">⏳ Revenus en attente</p>
    <p style="margin: 0 0 8px; color: #374151;">
        <strong>Séance :</strong> {{ $session->title }}<br>
        <strong>Date :</strong> {{ $session->scheduled_at->translatedFormat('l j F Y à H:i') }}
    </p>
    <p style="margin: 15px 0 0; color: #b45309; font-weight: 700; font-size: 18px;">
        Estimation : + {{ number_format($amount, 0, ',', ' ') }} FCFA (brut)
    </p>
</div>

<p style="margin: 20px 0; color: #374151; font-style: italic;">
    <strong>Note importante :</strong> Conformément à notre politique de qualité, vos revenus seront crédités sur votre
    portefeuille <strong>immédiatement après la soumission de votre compte rendu</strong> de séance.
</p>

<p style="margin: 20px 0; color: #374151;">
    Vos revenus cumulés peuvent être retirés à tout moment depuis votre portefeuille vers votre compte Mobile Money.
</p>

<table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
    <tr>
        <td align="center">
            <a href="{{ route('mentor.wallet.index') }}"
                style="display: inline-block; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #ffffff; padding: 14px 32px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 16px;">
                💼 Voir mon portefeuille
            </a>
        </td>
    </tr>
</table>

<p style="margin: 30px 0 0; color: #374151;">
    Merci de votre engagement,<br>
    <strong>L'équipe Brillio</strong>
</p>
@endsection