@extends('emails.layouts.base')

@section('content')
<p style="margin: 0 0 20px;">Bonjour,</p>

<p style="margin: 0 0 30px; font-size: 18px;">
    ❌ Votre abonnement payant a expiré et votre compte a été rétrogradé au plan <strong>{{ $targetPlan === 'pro' ? 'Professionnel' : ($targetPlan === 'enterprise' ? 'Entreprise' : 'Gratuit') }}</strong>.
</p>

<div style="background: #fef2f2; border-radius: 8px; padding: 20px; margin: 20px 0; border: 2px solid #ef4444;">
    <p style="margin: 0 0 10px; font-weight: 600; color: #991b1b; font-size: 16px;">📉 Changement de statut</p>
    <p style="margin: 0; color: #374151;">
        @if($targetPlan === 'free')
            Certaines fonctionnalités premium ne sont plus accessibles. Vos données sont conservées, mais vos capacités de gestion sont désormais limitées.
        @else
            Les fonctionnalités de votre ancien plan ne sont plus accessibles. Vos capacités de gestion ont été ajustées selon votre nouveau plan.
        @endif
    </p>
</div>

<p style="margin: 20px 0; color: #374151;">
    Vous pouvez retrouver l'intégralité de vos services à tout moment en souscrivant à un nouveau plan Pro ou
    Entreprise.
</p>

<table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
    <tr>
        <td align="center">
            <a href="{{ $renewUrl }}"
                style="display: inline-block; background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: #ffffff; padding: 14px 32px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 16px;">
                🚀 Repasser au plan Premium
            </a>
        </td>
    </tr>
</table>

<p style="margin: 30px 0 0; color: #374151;">
    À très bientôt,<br>
    <strong>L'équipe Brillio</strong>
</p>
@endsection