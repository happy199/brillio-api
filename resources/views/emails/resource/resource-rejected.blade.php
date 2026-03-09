@extends('emails.layouts.base')

@section('content')
<p style="margin: 0 0 20px;">Bonjour <strong>{{ $resource->user->name }}</strong>,</p>

<p style="margin: 0 0 20px; color: #374151; line-height: 1.6;">
    Votre ressource "<strong>{{ $resource->title }}</strong>" a été <strong>dépubliée</strong> par un administrateur
    Brillio.
    Elle n'est plus visible dans le catalogue des jeunes.
</p>

<div style="background-color: #fef2f2; border-radius: 8px; padding: 20px; margin: 25px 0; border: 1px solid #fecaca;">
    <p style="margin: 0; color: #991b1b; font-weight: 600;">⚠️ Message de l'administrateur :</p>
    <p style="margin: 10px 0 0; color: #b91c1c; font-size: 14px; line-height: 1.5;">
        {{ $resource->admin_feedback }}
    </p>
</div>

<p style="margin: 20px 0; color: #374151; line-height: 1.6;">
    Veuillez apporter les corrections nécessaires à votre ressource, puis la <strong>sauvegarder</strong> pour qu'elle
    soit automatiquement republiée et à nouveau visible.
</p>

<table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
    <tr>
        <td align="center">
            <a href="{{ route('mentor.resources.index') }}"
                style="display: inline-block; background-color: #4f46e5; color: #ffffff; padding: 14px 32px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 16px;">
                ✏️ Modifier ma ressource
            </a>
        </td>
    </tr>
</table>

<p style="margin: 30px 0 0; color: #374151;">
    Cordialement,<br>
    <strong>L'équipe Brillio</strong>
</p>
@endsection