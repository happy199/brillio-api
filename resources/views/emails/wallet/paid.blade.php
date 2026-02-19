@extends('emails.layouts.base')

@section('content')
<p style="margin: 0 0 20px;">Bonjour <strong>{{ $jeune->name }}</strong>,</p>

<p style="margin: 0 0 30px; font-size: 18px;">
    ✅ Votre paiement pour la séance de mentorat a été confirmé.
</p>

<div style="background-color: #f9fafb; border-radius: 8px; padding: 20px; margin: 20px 0; border: 1px solid #e5e7eb;">
    <p style="margin: 0 0 15px; font-weight: 600; color: #111827; font-size: 16px;">📋 Récapitulatif de la séance</p>
    <p style="margin: 0 0 8px; color: #374151;">
        <strong>Sujet :</strong> {{ $session->title }}<br>
        <strong>Mentor :</strong> {{ $session->mentor->name }}<br>
        <strong>Date :</strong> {{ $session->scheduled_at->translatedFormat('l j F Y à H:i') }}
    </p>
    <p style="margin: 15px 0 0; color: #059669; font-weight: 700;">
        💰 Montant déduit : {{ number_format($amount, 0, ',', ' ') }} crédits
    </p>
</div>

<p style="margin: 20px 0; color: #374151;">
    Le lien de la visioconférence sera disponible dans votre espace personnel 24h avant le début de la séance.
</p>

<table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
    <tr>
        <td align="center">
            <a href="{{ route('jeune.sessions.index') }}"
                style="display: inline-block; background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: #ffffff; padding: 14px 32px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 16px;">
                📅 Voir mes séances
            </a>
        </td>
    </tr>
</table>

<p style="margin: 30px 0 0; color: #374151;">
    Bon mentorat,<br>
    <strong>L'équipe Brillio</strong>
</p>
@endsection