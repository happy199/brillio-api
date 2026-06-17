@extends('emails.layouts.base')

@section('content')
<div style="font-family: 'Inter', system-ui, -apple-system, sans-serif; color: #374151;">
    <p style="margin: 0 0 20px; font-size: 16px; line-height: 1.6;">Bonjour <strong>{{ $user->name }}</strong>,</p>

    <!-- Urgency Message Card -->
    <div style="background: linear-gradient(135deg, #f5f3ff 0%, #fae8ff 100%); border: 1px solid #e9d5ff; border-radius: 12px; padding: 24px; margin: 24px 0; box-shadow: 0 2px 8px rgba(124, 58, 237, 0.05);">
        <p style="margin: 0; font-size: 16px; line-height: 1.7; color: #5b21b6; font-weight: 500;">
            Pour t'éviter de rater les opportunités de nos universités partenaires, Brillio envoie désormais les offres de bourses et les invitations aux tests d'entrée directement sur <strong>WhatsApp</strong>. Renseigne ton numéro en 1 clic pour activer tes alertes.
        </p>
    </div>

    <!-- CTA Button -->
    <table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
        <tr>
            <td align="center">
                <a href="{{ route('jeune.dashboard') }}"
                   style="display: inline-block; background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: #ffffff !important; padding: 16px 36px; text-decoration: none; border-radius: 10px; font-weight: 700; font-size: 16px; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3); transition: all 0.3s ease;">
                   📲 Activer mes alertes WhatsApp
                </a>
            </td>
        </tr>
    </table>

    <hr style="border: 0; border-top: 1px solid #e5e7eb; margin: 32px 0;">

    <!-- Motivated Section -->
    <div style="margin-top: 24px;">
        <h3 style="color: #1f2937; font-size: 18px; font-weight: 700; margin: 0 0 12px 0;">🚀 Envie d'aller encore plus vite ?</h3>
        <p style="margin: 0 0 20px 0; font-size: 15px; color: #4b5563; line-height: 1.5;">
            Si tu es motivé et pressé, tu peux rejoindre nos différents canaux pour ne rien rater :
        </p>

        <!-- Channel Link Card -->
        <div style="background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 10px; padding: 20px; margin-bottom: 16px;">
            <div style="display: flex; align-items: flex-start;">
                <span style="font-size: 24px; margin-right: 12px; line-height: 1;">📢</span>
                <div>
                    <h4 style="margin: 0 0 6px 0; color: #111827; font-size: 15px; font-weight: 600;">Canal WhatsApp officiel</h4>
                    <p style="margin: 0 0 12px 0; color: #6b7280; font-size: 13px;">Suis toutes nos actualités et annonces en temps réel.</p>
                    <a href="{{ $whatsappChannelUrl }}" target="_blank" style="color: #6366f1; text-decoration: none; font-weight: 600; font-size: 14px;">Rejoindre la chaîne &rarr;</a>
                </div>
            </div>
        </div>

        <!-- Group Link Card -->
        <div style="background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 10px; padding: 20px; margin-bottom: 24px;">
            <div style="display: flex; align-items: flex-start;">
                <span style="font-size: 24px; margin-right: 12px; line-height: 1;">🔒</span>
                <div>
                    <h4 style="margin: 0 0 6px 0; color: #111827; font-size: 15px; font-weight: 600;">Groupe restreint VIP</h4>
                    <p style="margin: 0 0 12px 0; color: #6b7280; font-size: 13px;">Sois le premier informé des offres d'emploi, de bourses exclusives, de formations et de webinaires.</p>
                    <a href="{{ $whatsappGroupUrl }}" target="_blank" style="color: #6366f1; text-decoration: none; font-weight: 600; font-size: 14px;">Rejoindre le groupe restreint &rarr;</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Sign-off -->
    <p style="margin: 24px 0 0 0; font-size: 16px; line-height: 1.6; color: #374151;">
        À très vite,<br>
        <strong>L'équipe Brillio</strong>
    </p>
</div>
@endsection
