@extends('emails.layouts.base')

@section('content')
<p style="margin: 0 0 20px;">Bonjour <strong>{{ $recipient->name }}</strong>,</p>

<p style="margin: 0 0 30px; font-size: 18px;">
    ✨ Merci d'avoir participé à la session de mentorat !
</p>

<div style="background-color: #f9fafb; border-radius: 8px; padding: 20px; margin: 20px 0;">
    <p style="margin: 0 0 8px; color: #374151;">
        <strong>📅 Session du :</strong> {{ $session->scheduled_at->translatedFormat('l j F Y à H:i') }}<br>
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

@if($recipient->user_type === 'jeune')
<table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
    <tr>
        <td align="center">
            <table cellpadding="0" cellspacing="0" style="width: 100%; max-width: 400px;">
                <tr>
                    <td align="center" style="padding-bottom: 12px;">
                        <a href="{{ $sessionUrl }}"
                            style="display: block; width: 100%; background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: #ffffff; padding: 14px 32px; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px; text-align: center; box-sizing: border-box;">
                            📝 Voir le compte rendu
                        </a>
                    </td>
                </tr>
                <tr>
                    <td align="center">
                        <a href="{{ $bookingUrl }}"
                            style="display: block; width: 100%; background-color: #10b981; color: #ffffff; padding: 14px 32px; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px; text-align: center; box-sizing: border-box;">
                            📅 Réserver une nouvelle session
                        </a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<p
    style="margin: 30px 0; padding: 16px; background-color: #eff6ff; border-left: 4px solid #3b82f6; border-radius: 4px; color: #1e40af;">
    💡 <strong>Continuez votre progression :</strong> N'hésitez pas à réserver une nouvelle session avec votre mentor
    pour aller plus loin !
</p>
@else
<table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
    <tr>
        <td align="center">
            <a href="{{ $sessionUrl }}"
                style="display: inline-block; max-width: 300px; width: 100%; background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: #ffffff; padding: 14px 32px; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px; text-align: center; box-sizing: border-box;">
                📝 Voir le compte rendu
            </a>
        </td>
    </tr>
</table>
@endif

<p style="margin: 30px 0 0; color: #374151;">
    Merci pour votre confiance,<br>
    <strong>L'équipe Brillio</strong>
</p>
@endsection