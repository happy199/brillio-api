@extends('emails.layouts.base')

@section('content')
<p style="margin: 0 0 20px;">Bonjour <strong>{{ $recipient->name }}</strong>,</p>

<p style="margin: 0 0 30px; font-size: 18px;">
    @if($type === '1h')
    ⏰ Votre session de mentorat commence dans <strong>1 heure</strong> !
    @else
    ⏰ Votre session de mentorat a lieu <strong>demain</strong> !
    @endif
</p>

<div
    style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-radius: 8px; padding: 20px; margin: 20px 0; border: 2px solid #f59e0b;">
    <p style="margin: 0 0 15px; font-weight: 600; color: #92400e; font-size: 16px;">📅 Rappel de session</p>

    <p style="margin: 0 0 8px; color: #374151; font-size: 18px; font-weight: 600;">
        {{ $session->scheduled_at->translatedFormat('l j F Y') }} à {{ $session->scheduled_at->format('H:i') }} ({{ $session->gmt_offset }})
    </p>

    <p style="margin: 15px 0 8px; color: #374151;">
        <strong>⏱️ Durée :</strong> {{ $session->duration_minutes }} minutes<br>
        <strong>👥 Avec :</strong>
        @if($recipient->id === $session->mentor_id)
        @foreach($participants as $participant)
        {{ $participant->name }}{{ !$loop->last ? ', ' : '' }}
        @endforeach
        @else
        {{ $session->mentor->name }}
        @endif
    </p>
</div>

<table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
    <tr>
        <td align="center">
            <a href="{{ $session->meeting_link }}"
                style="display: inline-block; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #ffffff; padding: 14px 32px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 16px; margin-bottom: 15px;">
                🎥 Rejoindre la session
            </a>
            <br>
            <a href="{{ $calendarUrl }}"
                style="display: inline-block; background: #ffffff; color: #4285f4; padding: 10px 20px; text-decoration: none; border-radius: 6px; border: 1px solid #4285f4; font-weight: 600; font-size: 14px;">
                📅 Ajouter à mon agenda
            </a>
        </td>
    </tr>
</table>

<p style="margin: 20px 0; font-size: 14px; color: #6b7280;">
    💡 <strong>Conseils techniques :</strong><br>
    • Testez votre micro et webcam avant la session<br>
    • Prévoyez une connexion internet stable<br>
    • Préparez vos questions en avance
</p>

<p style="margin: 30px 0 0; color: #374151;">
    Bonne session !<br>
    <strong>L'équipe Brillio</strong>
</p>
@endsection