@extends('emails.layouts.base')

@section('content')
<p style="margin: 0 0 20px;">Bonjour <strong>{{ $recipient->name }}</strong>,</p>

<p style="margin: 0 0 30px; font-size: 18px;">
    🎉 Votre session de mentorat est confirmée !
</p>

<div
    style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border-radius: 8px; padding: 20px; margin: 20px 0; border: 2px solid #10b981;">
    <p style="margin: 0 0 15px; font-weight: 600; color: #065f46; font-size: 16px;">📋 Détails de votre session</p>

    <p style="margin: 0 0 8px; color: #374151;">
        <strong>👥 Participants :</strong><br>
        🎓 Mentor : {{ $session->mentor->name }}<br>
        @foreach($participants as $participant)
        💼 {{ $participant->name }}<br>
        @endforeach
    </p>

    <p style="margin: 15px 0 8px; color: #374151;">
        <strong>📅 Date :</strong> {{ $session->scheduled_at->translatedFormat('l j F Y') }}<br>
        <strong>🕐 Heure :</strong> {{ $session->scheduled_at->format('H:i') }} ({{ $session->gmt_offset }})<br>
        <strong>⏱️ Durée :</strong> {{ $session->duration_minutes }} minutes
    </p>
</div>

<p style="margin: 20px 0; color: #374151;">
    Un rappel vous sera envoyé <strong>24 heures avant</strong> la session avec le lien de connexion.
</p>

<table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
    <tr>
        <td align="center">
            <a href="{{ $calendarUrl }}"
                style="display: inline-block; background: linear-gradient(135deg, #4285f4 0%, #34a853 100%); color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 14px; margin-bottom: 10px;">
                📅 Ajouter à mon agenda Google
            </a>
            <br>
            <span style="font-size: 12px; color: #6b7280;">
                📎 Un fichier <strong>invitation.ics</strong> est également joint pour les autres calendriers.
            </span>
        </td>
    </tr>
</table>

<p style="margin: 20px 0; font-size: 14px; color: #6b7280;">
    💡 <strong>Conseil :</strong> Préparez vos questions et objectifs pour profiter au maximum de cette session !
</p>

<p style="margin: 30px 0 0; color: #374151;">
    À très bientôt,<br>
    <strong>L'équipe Brillio</strong>
</p>
@endsection