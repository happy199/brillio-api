@extends('emails.layouts.base')

@section('content')
<p style="margin: 0 0 20px;">Bonjour <strong>{{ $mentor->name }}</strong>,</p>

<p style="margin: 0 0 30px; font-size: 18px;">
    🎉 Vos revenus sont désormais disponibles !
</p>

<div style="background-color: #f0fdf4; border-radius: 8px; padding: 20px; margin: 20px 0; border: 2px solid #10b981;">
    <p style="margin: 0 0 15px; font-weight: 600; color: #166534; font-size: 16px;">💸 Revenus libérés</p>
    <p style="margin: 0 0 8px; color: #374151;">
        <strong>Séance :</strong> {{ $session->title }}<br>
        <strong>L'étape finale :</strong> Compte rendu validé ✅
    </p>
    <p style="margin: 15px 0 0; color: #15803d; font-weight: 700; font-size: 20px;">
        + {{ number_format($amount, 0, ',', ' ') }} crédits
    </p>
</div>

<p style="margin: 20px 0; color: #374151;">
    Ces crédits ont été ajoutés à votre solde "disponibles pour retrait". Vous pouvez initier une demande de virement
    vers votre compte Mobile Money dès maintenant.
</p>

<table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
    <tr>
        <td align="center">
            <a href="{{ route('mentor.wallet.index') }}"
                style="display: inline-block; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #ffffff; padding: 14px 32px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 16px;">
                💼 Accéder à mon portefeuille
            </a>
        </td>
    </tr>
</table>

<p style="margin: 30px 0 0; color: #374151;">
    Merci pour la qualité de votre accompagnement !<br>
    <strong>L'équipe Brillio</strong>
</p>
@endsection