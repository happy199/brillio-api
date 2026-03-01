@extends('emails.layouts.base')

@section('content')
<p style="margin: 0 0 20px;">Bonjour,</p>

<p style="margin: 0 0 30px; font-size: 18px;">
    ⚠️ Votre abonnement Brillio <strong>{{ $organization->subscription_plan }}</strong> arrive à échéance.
</p>

<div style="background: #fffbeb; border-radius: 8px; padding: 20px; margin: 20px 0; border: 2px solid #f59e0b;">
    <p style="margin: 0 0 10px; font-weight: 600; color: #92400e; font-size: 16px;">⏳ Expiration imminente</p>
    <p style="margin: 0; color: #374151;">
        Il ne vous reste que <strong>{{ $timeLeft }}</strong> avant la fin de vos avantages actuels.<br>
        Date d'expiration : {{ $organization->subscription_expires_at->format('d/m/Y H:i') }}
    </p>
</div>

<p style="margin: 20px 0; color: #374151;">
    Pour éviter toute interruption de service et conserver vos accès privilégiés (gestion des membres, mentors, etc.),
    nous vous invitons à renouveler votre abonnement dès maintenant.
</p>

<table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
    <tr>
        <td align="center">
            <a href="{{ $renewUrl }}"
                style="display: inline-block; background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: #ffffff; padding: 14px 32px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 16px;">
                🔄 Renouveler mon abonnement
            </a>
        </td>
    </tr>
</table>

<p style="margin: 30px 0 0; color: #374151;">
    À très bientôt,<br>
    <strong>L'équipe Brillio</strong>
</p>
@endsection