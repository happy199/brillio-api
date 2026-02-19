@extends('emails.layouts.base')

@section('content')
<p style="margin: 0 0 20px;">Bonjour <strong>{{ $user->name }}</strong>,</p>

@if($payout->status === \App\Models\PayoutRequest::STATUS_COMPLETED)
<p style="margin: 0 0 30px; font-size: 18px; color: #059669;">
    ✨ Votre retrait de <strong>{{ number_format($payout->net_amount, 0, ',', ' ') }} FCFA</strong> a été effectué !
</p>

<div style="background-color: #f0fdf4; border-radius: 8px; padding: 20px; margin: 20px 0; border: 1px solid #bbf7d0;">
    <p style="margin: 0; color: #166534;">
        Le virement a été envoyé vers votre compte <strong>{{ $payout->payment_method }}</strong> ({{
        $payout->phone_number }}).
        Les fonds devraient apparaître sur votre solde sous peu.
    </p>
</div>
@else
<p style="margin: 0 0 30px; font-size: 18px; color: #dc2626;">
    ⚠️ Échec de votre demande de retrait.
</p>

<div style="background-color: #fef2f2; border-radius: 8px; padding: 20px; margin: 20px 0; border: 1px solid #fecaca;">
    <p style="margin: 0 0 10px; font-weight: 600; color: #991b1b;">Raison de l'échec :</p>
    <p style="margin: 0; color: #b91c1c; font-style: italic;">
        "{{ $payout->error_message ?? 'Une erreur technique est survenue lors du virement.' }}"
    </p>
</div>

<p style="margin: 20px 0; color: #374151;">
    Bonne nouvelle : vos crédits ont été <strong>automatiquement restitués</strong> sur votre solde de mentor. Vous
    pouvez retenter une demande ou contacter notre support si le problème persiste.
</p>
@endif

<table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
    <tr>
        <td align="center">
            <a href="{{ route('mentor.wallet.index') }}"
                style="display: inline-block; background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: #ffffff; padding: 14px 32px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 16px;">
                💼 Gérer mon portefeuille
            </a>
        </td>
    </tr>
</table>

<p style="margin: 30px 0 0; color: #374151;">
    Cordialement,<br>
    <strong>L'équipe Brillio</strong>
</p>
@endsection