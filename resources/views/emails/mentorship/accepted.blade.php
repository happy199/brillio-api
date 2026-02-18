@extends('emails.layouts.base')

@section('content')
<p style="margin: 0 0 20px;">Bonjour <strong>{{ $mentee->name }}</strong>,</p>

<p style="margin: 0 0 30px; font-size: 18px;">
    🎉 Excellente nouvelle ! <strong>{{ $mentor->name }}</strong> a accepté de devenir votre mentor.
</p>

<div
    style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border-radius: 8px; padding: 20px; margin: 20px 0;">
    <p style="margin: 0 0 15px; font-weight: 600; color: #0369a1; font-size: 16px;">👤 Votre nouveau mentor</p>
    <p style="margin: 0 0 8px; color: #374151;">
        <strong>{{ $mentor->name }}</strong><br>
        {{ $mentor->mentorProfile->current_position }} @ {{ $mentor->mentorProfile->current_company }}
    </p>
    <p style="margin: 0; color: #6b7280; font-size: 14px;">
        🎯 Spécialisation : {{ $mentor->mentorProfile->specializationModel->name ??
        $mentor->mentorProfile->specialization }}<br>
        💼 {{ $mentor->mentorProfile->years_of_experience }} ans d'expérience
    </p>
</div>

<p style="margin: 20px 0;">
    Vous pouvez maintenant échanger avec {{ $mentor->name }}, planifier des sessions et bénéficier de son expérience
    pour construire votre avenir professionnel.
</p>

<table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
    <tr>
        <td align="center">
            <a href="{{ $bookingUrl }}"
                style="display: inline-block; background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: #ffffff; padding: 14px 32px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 16px;">
                📅 Réserver un meeting
            </a>
        </td>
    </tr>
</table>

<p style="margin: 20px 0; font-size: 14px; color: #6b7280;">
    💡 <strong>Conseil :</strong> Réservez votre première session pour mieux connaître votre mentor et définir ensemble
    vos objectifs professionnels !
</p>

<p style="margin: 30px 0 0; color: #374151;">
    Bonne chance dans votre parcours,<br>
    <strong>L'équipe Brillio</strong>
</p>
@endsection