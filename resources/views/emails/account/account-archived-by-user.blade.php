@extends('emails.layouts.base')

@section('content')
<p style="margin: 0 0 20px;">Bonjour <strong>{{ $user->name }}</strong>,</p>

<p style="margin: 0 0 20px; color: #374151; line-height: 1.6;">
    Comme vous l'avez demandé, nous vous confirmons que votre compte <strong>Brillio</strong> a été archivé.
</p>

<div style="background-color: #fffbeb; border-radius: 8px; padding: 20px; margin: 25px 0; border: 1px solid #fde68a;">
    <p style="margin: 0 0 10px; font-weight: 600; color: #92400e;">⚠️ Informations importantes :</p>
    <ul style="margin: 0; padding-left: 20px; color: #b45309; line-height: 1.5;">
        <li>Votre compte restera archivé pendant <strong>30 jours</strong>.</li>
        <li>Passé ce délai sans reconnexion de votre part, vos données seront <strong>définitivement
                supprimées</strong>.</li>
        <li>Si vous avez archivé ce compte pour en utiliser un nouveau, une <strong>reconnexion avec votre nouveau
                compte</strong> est nécessaire pour valider le changement.</li>
    </ul>
</div>

<p style="margin: 20px 0; color: #374151; line-height: 1.6;">
    Si vous souhaitez annuler cet archivage et réactiver votre compte, il vous suffit de vous reconnecter à la
    plateforme Brillio dans les 30 prochains jours.
</p>

<table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
    <tr>
        <td align="center">
            <a href="{{ route('auth.login') }}"
                style="display: inline-block; background-color: #10b981; color: #ffffff; padding: 14px 32px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 16px;">
                🔓 Me reconnecter pour annuler
            </a>
        </td>
    </tr>
</table>

<p style="margin: 30px 0 0; color: #374151;">
    Cordialement,<br>
    <strong>L'équipe Brillio</strong>
</p>
@endsection