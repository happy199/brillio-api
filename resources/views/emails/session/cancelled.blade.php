@extends('emails.layouts.base')

@section('content')
<p style="margin: 0 0 20px;">Bonjour <strong>{{ $recipient->name }}</strong>,</p>

<p style="margin: 0 0 30px; font-size: 18px;">
    La session de mentorat prévue le <strong>{{ $session->scheduled_at->translatedFormat('j F Y à H:i') }}</strong> a
    été annulée par <strong>{{ $cancelledBy->name }}</strong>.
</p>

<div style="background-color: #f3f4f6; border-radius: 8px; padding: 20px; margin: 20px 0;">
    <p style="margin: 0 0 15px; font-weight: 600; color: #374151; font-size: 16px;">📅 Détails de la session annulée</p>
    <p style="margin: 0 0 8px; color: #4b5563;">
        <strong>Date :</strong> {{ $session->scheduled_at->translatedFormat('l j F Y') }}<br>
        <strong>Heure :</strong> {{ $session->scheduled_at->format('H:i') }}
    </p>

    @if($session->cancel_reason)
    <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e5e7eb;">
        <p style="margin: 0 0 5px; font-weight: 600; color: #374151;">Raison de l'annulation :</p>
        <p style="margin: 0; color: #4b5563; font-style: italic;">"{{ $session->cancel_reason }}"</p>
    </div>
    @endif
</div>

@if($recipient->id !== $cancelledBy->id)
<p style="margin: 20px 0;">
    @if($recipient->isMentor())
    Le jeune a annulé cette séance. Vos disponibilités ont été libérées.
    @else
    Le mentor a annulé cette séance. Si vous aviez payé avec des crédits, ceux-ci ont été recrédités sur votre compte.
    @endif
</p>
@endif

<table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
    <tr>
        <td align="center">
            <a href="{{ $recipient->isMentor() ? route('mentor.mentorship.calendar') : route('jeune.sessions.calendar') }}"
                style="display: inline-block; background-color: #4b5563; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 14px;">
                Voir mon calendrier
            </a>
        </td>
    </tr>
</table>

<p style="margin: 30px 0 0; color: #374151;">
    Cordialement,<br>
    <strong>L'équipe Brillio</strong>
</p>
@endsection