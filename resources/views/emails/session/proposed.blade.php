@extends('emails.layouts.base')

@section('content')
<p style="margin: 0 0 20px;">Bonjour <strong>{{ $mentee->name }}</strong>,</p>

<p style="margin: 0 0 30px; font-size: 18px;">
    📅 Votre mentor <strong>{{ $mentor->name }}</strong> vous propose une session de mentorat !
</p>

<div
    style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border-radius: 8px; padding: 20px; margin: 20px 0;">
    <p style="margin: 0 0 15px; font-weight: 600; color: #0369a1; font-size: 16px;">📋 Détails de la session</p>

    <p style="margin: 0 0 8px; color: #374151;">
        <strong>📅 Date :</strong> {{ $session->scheduled_at->translatedFormat('l j F Y') }}<br>
        <strong>🕐 Heure :</strong> {{ $session->scheduled_at->format('H:i') }}<br>
        <strong>⏱️ Durée :</strong> {{ $session->duration }} minutes
    </p>

    @if($session->notes)
    <p
        style="margin: 15px 0 0; padding: 12px; background-color: #fff; border-radius: 4px; color: #6b7280; font-size: 14px; font-style: italic;">
        "{{ $session->notes }}"
    </p>
    @endif
</div>

@if($session->price > 0)
<div
    style="background-color: #fef3c7; border-left: 4px solid #f59e0b; padding: 16px; margin: 20px 0; border-radius: 4px;">
    <p style="margin: 0; color: #92400e;">
        💰 <strong>Coût :</strong> {{ number_format($session->price, 0, ',', ' ') }} XOF
        @if($menteeCredits >= $session->price)
        <span style="color: #16a34a;">✓ Vous avez assez de crédits</span>
        @else
        <span style="color: #dc2626;">⚠️ Crédits insuffisants ({{ $menteeCredits }} disponibles)</span>
        @endif
    </p>
</div>
@endif

<table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
    <tr>
        <td align="center">
            <a href="{{ $acceptUrl }}"
                style="display: inline-block; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #ffffff; padding: 14px 32px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 16px; margin-right: 10px;">
                ✓ Accepter
            </a>
            <a href="{{ $refuseUrl }}"
                style="display: inline-block; background-color: #f3f4f6; color: #6b7280; padding: 14px 32px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 16px;">
                Refuser
            </a>
        </td>
    </tr>
</table>

<p style="margin: 20px 0; font-size: 14px; color: #6b7280;">
    💡 Vous avez <strong>24 heures</strong> pour répondre à cette proposition.
</p>

<p style="margin: 30px 0 0; color: #374151;">
    À bientôt,<br>
    <strong>L'équipe Brillio</strong>
</p>
@endsection