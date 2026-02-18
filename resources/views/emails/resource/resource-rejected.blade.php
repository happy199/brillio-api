@extends('emails.layouts.base')

@section('content')
<p style="margin: 0 0 20px;">Bonjour <strong>{{ $resource->user->name }}</strong>,</p>

<p style="margin: 0 0 20px; color: #374151; line-height: 1.6;">
    Nous vous informons que votre ressource "<strong>{{ $resource->title }}</strong>" a été retirée de la publication ou
    n'a pas été validée en l'état par notre équipe d'administration.
</p>

<div style="background-color: #fef2f2; border-radius: 8px; padding: 20px; margin: 25px 0; border: 1px solid #fecaca;">
    <p style="margin: 0; color: #991b1b; font-weight: 600;">⚠️ Statut : Non publiée</p>
    <p style="margin: 10px 0 0; color: #b91c1c; font-size: 14px; line-height: 1.5;">
        Cela peut être dû au non-respect de nos directives de contenu ou à un besoin de complétion.
    </p>
</div>

<p style="margin: 20px 0; color: #374151; line-height: 1.6;">
    Vous pouvez modifier votre ressource et la soumettre à nouveau pour validation depuis votre espace mentor.
</p>

<table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
    <tr>
        <td align="center">
            <a href="{{ route('mentor.resources.index') }}"
                style="display: inline-block; background-color: #6b7280; color: #ffffff; padding: 14px 32px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 16px;">
                ⚙️ Gérer mes ressources
            </a>
        </td>
    </tr>
</table>

<p style="margin: 30px 0 0; color: #374151;">
    Cordialement,<br>
    <strong>L'équipe Brillio</strong>
</p>
@endsection