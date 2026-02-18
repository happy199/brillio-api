@extends('emails.layouts.base')

@section('content')
<p style="margin: 0 0 20px;">Bonjour <strong>{{ $mentee->name }}</strong>,</p>

<p style="margin: 0 0 30px; font-size: 18px;">
    <strong>{{ $mentor->name }}</strong> ne pourra pas honorer la session de mentorat proposée le <strong>{{
        $session->scheduled_at->translatedFormat('j F Y à H:i') }}</strong>.
</p>

<div
    style="background-color: #fff7ed; border-left: 4px solid #f97316; padding: 20px; margin: 20px 0; border-radius: 4px;">
    <p style="margin: 0 0 10px; font-weight: 600; color: #9a3412;">Motif du refus :</p>
    @if($reason)
    <p style="margin: 0; color: #c2410c; font-style: italic;">
        "{{ $reason }}"
    </p>
    @else
    <p style="margin: 0; color: #c2410c;">
        Le mentor a un empêchement de dernière minute ou le créneau ne lui convient plus.
    </p>
    @endif
</div>

<p style="margin: 20px 0;">
    Vous pouvez proposer un autre créneau en consultant les disponibilités de votre mentor sur son profil.
</p>

<table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
    <tr>
        <td align="center">
            <a href="{{ route('jeune.sessions.create', ['mentor' => $mentor->id]) }}"
                style="display: inline-block; background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: #ffffff; padding: 14px 32px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 16px;">
                📅 Proposer un autre créneau
            </a>
        </td>
    </tr>
</table>

<p style="margin: 30px 0 0; color: #374151;">
    Merci de votre compréhension,<br>
    <strong>L'équipe Brillio</strong>
</p>
@endsection