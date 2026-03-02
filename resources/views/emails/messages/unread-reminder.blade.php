@extends('emails.layouts.base')

@section('content')
<p style="margin: 0 0 20px; font-size: 18px; font-weight: 600; color: #111827;">
    Bonjour {{ $recipient->name }} 👋
</p>

<p style="margin: 0 0 16px; color: #374151;">
    Vous avez
    <strong style="color: #6366f1;">{{ $messageCount }} message(s) non lu(s)</strong>
    en attente de la part de <strong>{{ $senderName }}</strong>.
</p>

<p style="margin: 0 0 24px; color: #6b7280; font-size: 14px;">
    Connectez-vous à Brillio pour lire et répondre à {{ $messageCount > 1 ? 'ces messages' : 'ce message' }}.
</p>

<!-- CTA -->
<table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td align="center" style="padding: 8px 0 32px;">
            <a href="{{ $conversationUrl }}"
                style="display: inline-block; background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: #ffffff; font-weight: 600; font-size: 15px; padding: 14px 32px; border-radius: 8px; text-decoration: none;">
                Voir mes messages →
            </a>
        </td>
    </tr>
</table>

<p style="margin: 0; font-size: 13px; color: #9ca3af; text-align: center;">
    Cet email a été envoyé car vous avez des messages non lus sur Brillio.<br>
    Vous ne recevrez plus de rappel une fois vos messages consultés.
</p>
@endsection