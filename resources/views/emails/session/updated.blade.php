@extends('emails.layouts.base')

@section('content')
<p style="margin: 0 0 20px;">Bonjour <strong>{{ $recipient->name }}</strong>,</p>

<p style="margin: 0 0 30px; font-size: 18px;">
    La session de mentorat <strong>"{{ $session->title }}"</strong> a été modifiée par <strong>{{ $updatedBy->name }}</strong>.
</p>

<div style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border-radius: 8px; padding: 20px; margin: 20px 0; border: 2px solid #38bdf8;">
    <p style="margin: 0 0 15px; font-weight: 600; color: #0369a1; font-size: 16px;">📅 Nouveaux détails de la session</p>

    <p style="margin: 0 0 8px; color: #374151;">
        <strong>👥 Participants :</strong><br>
        🎓 Mentor : {{ $session->mentor->name }}<br>
        @foreach($participants as $participant)
        💼 {{ $participant->name }}<br>
        @endforeach
    </p>

    <p style="margin: 15px 0 8px; color: #374151;">
        <strong>📅 Date :</strong> {{ $session->scheduled_at->translatedFormat('l j F Y') }}<br>
        <strong>🕐 Heure :</strong> {{ $session->scheduled_at->format('H:i') }}<br>
        <strong>⏱️ Durée :</strong> {{ $session->duration_minutes }} minutes
    </p>
</div>

<p style="margin: 20px 0; color: #374151;">
    Veuillez mettre à jour votre agenda si nécessaire. Vous recevrez un rappel 24 heures avant la session.
</p>

<table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
    <tr>
        <td align="center">
            <a href="{{ $recipient->isMentor() ? route('mentor.mentorship.calendar') : route('jeune.sessions.calendar') }}"
                style="display: inline-block; background-color: #38bdf8; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 14px;">
                Voir mon calendrier
            </a>
        </td>
    </tr>
</table>

<p style="margin: 30px 0 0; color: #374151;">
    À très bientôt,<br>
    <strong>L'équipe Brillio</strong>
</p>
@endsection
