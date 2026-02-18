@extends('emails.layouts.base')

@section('content')
<p style="margin: 0 0 20px;">Bonjour <strong>{{ $user->name }}</strong>,</p>

<p style="margin: 0 0 30px; font-size: 18px;">
    🎉 Votre compte a été rechargé avec succès !
</p>

<div
    style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border-radius: 8px; padding: 20px; margin: 20px 0; border: 2px solid #10b981;">
    <p style="margin: 0 0 10px; font-weight: 600; color: #065f46; font-size: 16px;">💳 Détails de la transaction</p>
    <p style="margin: 0; color: #374151;">
        <strong>Montant ajouté :</strong> {{ number_format($amount, 0, ',', ' ') }} crédits<br>
        <strong>Nouveau solde :</strong> {{ number_format($newBalance, 0, ',', ' ') }} crédits
    </p>
</div>

<p style="margin: 20px 0; color: #374151;">
    Vous pouvez maintenant utiliser ces crédits pour réserver des séances avec vos mentors préférés.
</p>

<table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
    <tr>
        <td align="center">
            <a href="{{ route('jeune.mentors') }}"
                style="display: inline-block; background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: #ffffff; padding: 14px 32px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 16px;">
                🔍 Trouver un mentor
            </a>
        </td>
    </tr>
</table>

<p style="margin: 30px 0 0; color: #374151;">
    À très bientôt,<br>
    <strong>L'équipe Brillio</strong>
</p>
@endsection