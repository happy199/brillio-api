@extends('emails.layouts.base')

@section('content')
<p style="margin: 0 0 20px;">Bonjour <strong>{{ $user->name }}</strong>,</p>

<p style="margin: 0 0 20px; color: #374151; line-height: 1.6;">
    Nous avons bien reçu votre message envoyé via notre formulaire de contact. Notre équipe va l'étudier avec attention
    et vous répondra dans les plus brefs délais.
</p>

<div style="background-color: #f0f9ff; border-radius: 8px; padding: 20px; margin: 25px 0; border: 1px solid #bae6fd;">
    <p style="margin: 0 0 10px; font-weight: 600; color: #0369a1;">Détails de votre demande :</p>
    <p style="margin: 0; color: #0c4a6e;">
        <strong>Sujet :</strong> {{ $data['subject'] ?? 'Sans objet' }}<br>
        <strong>Date :</strong> {{ now()->translatedFormat('l j F Y à H:i') }}
    </p>
</div>

<p style="margin: 20px 0; color: #374151; line-height: 1.6;">
    En attendant, vous pouvez consulter notre FAQ ou votre espace personnel pour trouver des réponses à vos questions.
</p>

<table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
    <tr>
        <td align="center">
            <a href="{{ route('home') }}"
                style="display: inline-block; background-color: #0ea5e9; color: #ffffff; padding: 14px 32px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 16px;">
                🏠 Retour au site
            </a>
        </td>
    </tr>
</table>

<p style="margin: 30px 0 0; color: #374151;">
    À bientôt sur Brillio,<br>
    <strong>L'équipe Support</strong>
</p>
@endsection