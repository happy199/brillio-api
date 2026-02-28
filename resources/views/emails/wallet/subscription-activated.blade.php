@extends('emails.layouts.base')

@section('content')
<p style="margin: 0 0 20px;">Bonjour,</p>

<p style="margin: 0 0 30px; font-size: 18px;">
    ✅ Votre abonnement <strong>{{ $plan->name }}</strong> pour l'organisation <strong>{{ $organization->name }}</strong>
    a été activé avec succès !
</p>

<div
    style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border-radius: 8px; padding: 20px; margin: 20px 0; border: 2px solid #0ea5e9;">
    <p style="margin: 0 0 10px; font-weight: 600; color: #0369a1; font-size: 16px;">📅 Détails de l'abonnement</p>
    <p style="margin: 0; color: #374151;">
        <strong>Plan :</strong> {{ $plan->name }}<br>
        <strong>Statut :</strong> Actif<br>
        <strong>Date d'expiration :</strong> {{ $organization->subscription_expires_at->format('d/m/Y') }}
    </p>
</div>

<p style="margin: 20px 0; color: #374151;">
    Vous bénéficiez maintenant de toutes les fonctionnalités incluses dans votre plan. Vous pouvez gérer vos membres,
    vos mentors et suivre vos dépenses depuis votre tableau de bord.
</p>

<table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
    <tr>
        <td align="center">
            <a href="{{ route('organization.dashboard') }}"
                style="display: inline-block; background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: #ffffff; padding: 14px 32px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 16px;">
                ⚙️ Gérer mon organisation
            </a>
        </td>
    </tr>
</table>

<p style="margin: 30px 0 0; color: #374151;">
    Merci de votre confiance,<br>
    <strong>L'équipe Brillio</strong>
</p>
@endsection