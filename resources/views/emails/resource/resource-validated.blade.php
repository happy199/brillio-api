@extends('emails.layouts.base')

@section('content')
<p style="margin: 0 0 20px;">Bonjour <strong>{{ $resource->user->name }}</strong>,</p>

<p style="margin: 0 0 20px; color: #374151; line-height: 1.6;">
    Bonne nouvelle ! Votre ressource "<strong>{{ $resource->title }}</strong>" a été validée par notre équipe et est
    désormais publiée sur la plateforme Brillio.
</p>

<div
    style="background-color: #f0fdf4; border-radius: 8px; padding: 20px; margin: 25px 0; border: 1px solid #bbf7d0; text-align: center;">
    <p style="margin: 0; color: #166534; font-weight: 600; font-size: 18px;">🎉 Votre contenu est en ligne !</p>
</div>

<p style="margin: 20px 0; color: #374151; line-height: 1.6;">
    Elle est maintenant accessible aux jeunes de la communauté qui correspondent à vos critères de ciblage.
</p>

<table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
    <tr>
        <td align="center">
            <a href="{{ route('public.mentor.profile', ['mentor' => $resource->user->id ?? 0]) }}"
                style="display: inline-block; background-color: #10b981; color: #ffffff; padding: 14px 32px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 16px;">
                👀 Voir mon profil
            </a>
        </td>
    </tr>
</table>

<p style="margin: 30px 0 0; color: #374151;">
    Merci pour votre contribution à l'écosystème Brillio !<br>
    <strong>L'équipe Brillio</strong>
</p>
@endsection