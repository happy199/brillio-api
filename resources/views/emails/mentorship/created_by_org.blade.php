@extends('emails.layouts.base')

@section('content')
<p style="margin: 0 0 20px;">Bonjour <strong>{{ $recipient->name }}</strong>,</p>

<p style="margin: 0 0 30px; font-size: 18px;">
    🎉 <strong>{{ $organizationName }}</strong> a créé une nouvelle relation de mentorat pour vous avec <strong>{{
        $otherPartyName }}</strong>.
</p>

<div style="background: #f8fafc; border-radius: 8px; padding: 20px; margin: 20px 0; border: 1px solid #e2e8f0;">
    <p style="margin: 0; color: #475569; line-height: 1.6;">
        Cette initiative s'inscrit dans le cadre de l'accompagnement proposé par votre organisation pour favoriser les
        échanges et le partage d'expérience.
    </p>
</div>

<p style="margin: 20px 0;">
    Vous pouvez dès à présent échanger avec {{ $otherPartyName }} depuis votre espace personnel.
</p>

<table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
    <tr>
        <td align="center">
            <a href="{{ $actionUrl }}"
                style="display: inline-block; background: #6366f1; color: #ffffff; padding: 14px 32px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 16px;">
                🚀 Accéder à mon espace
            </a>
        </td>
    </tr>
</table>

<p style="margin: 30px 0 0; color: #374151;">
    Bonne continuation,<br>
    <strong>L'équipe Brillio</strong>
</p>
@endsection