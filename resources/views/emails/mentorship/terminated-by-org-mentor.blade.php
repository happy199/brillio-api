@extends('emails.layouts.base')

@section('content')
<p style="margin: 0 0 20px;">Bonjour <strong>{{ $mentor->name }}</strong>,</p>

<p style="margin: 0 0 30px; font-size: 18px;">
    L'organisation <strong>{{ $organizationName }}</strong> nous a informés qu'elle souhaite mettre fin à la relation de
    mentorat entre vous et <strong>{{ $mentee->name }}</strong>.
</p>

<div style="background: #f8fafc; border-radius: 8px; padding: 20px; margin: 20px 0; border: 1px solid #e2e8f0;">
    <p style="margin: 0; color: #475569;">
        En conséquence, votre relation de mentorat avec ce jeune est désormais clôturée sur la plateforme.
    </p>
</div>

<p style="margin: 20px 0;">
    Nous vous remercions pour l'accompagnement et le temps que vous avez consacrés à ce jeune. Votre profil reste bien
    entendu disponible pour d'autres opportunités de mentorat.
</p>

<table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
    <tr>
        <td align="center">
            <a href="{{ route('mentor.dashboard') }}"
                style="display: inline-block; background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: #ffffff; padding: 14px 32px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 16px;">
                🏠 Accéder à mon tableau de bord
            </a>
        </td>
    </tr>
</table>

<p style="margin: 30px 0 0; color: #374151;">
    Cordialement,<br>
    <strong>L'équipe Brillio</strong>
</p>
@endsection