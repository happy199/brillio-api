@extends('emails.layouts.base')

@section('content')
<p style="margin: 0 0 20px;">Bonjour <strong>{{ $resource->user->name }}</strong>,</p>

<p style="margin: 0 0 20px; color: #374151; line-height: 1.6;">
    Félicitations ! Un utilisateur vient de débloquer votre ressource premium : "<strong>{{ $resource->title
        }}</strong>".
</p>

<div
    style="background-color: #eff6ff; border-radius: 8px; padding: 25px; margin: 25px 0; border: 1px solid #dbeafe; text-align: center;">
    <p style="margin: 0; color: #1e40af; font-size: 14px; text-transform: uppercase; letter-spacing: 0.05em;">Revenu
        généré</p>
    <p style="margin: 10px 0; color: #1d4ed8; font-size: 32px; font-weight: 800;">+{{ $creditsEarned }} Crédits</p>
    <p style="margin: 0; color: #60a5fa; font-size: 14px;">Acheté par {{ $buyer->name }}</p>
</div>

<p style="margin: 20px 0; color: #374151; line-height: 1.6;">
    Vos crédits ont été automatiquement ajoutés à votre portefeuille mentor. Vous pouvez consulter l'historique de vos
    revenus à tout moment.
</p>

<table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
    <tr>
        <td align="center">
            <a href="{{ route('mentor.wallet.index') }}"
                style="display: inline-block; background-color: #2563eb; color: #ffffff; padding: 14px 32px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 16px;">
                💰 Voir mon portefeuille
            </a>
        </td>
    </tr>
</table>

<p style="margin: 30px 0 0; color: #374151;">
    Continuez à partager votre expertise !<br>
    <strong>L'équipe Brillio</strong>
</p>
@endsection