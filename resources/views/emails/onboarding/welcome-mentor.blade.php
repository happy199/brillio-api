@extends('emails.layouts.base')

@section('content')
<p style="margin: 0 0 20px;">Bonjour <strong>{{ $user->name }}</strong>,</p>

<p style="margin: 0 0 30px; font-size: 18px;">
    🌟 Bienvenue dans la communauté des <strong>Mentors Brillio</strong> !
</p>

<p style="margin: 0 0 20px; color: #374151; line-height: 1.6;">
    Votre expertise est une ressource précieuse pour la nouvelle génération. Merci de nous rejoindre pour partager votre
    savoir et inspirer les leaders de demain.
</p>

<p style="margin: 0 0 20px; color: #374151; line-height: 1.6;">
    Pour commencer à recevoir des demandes de mentorat, assurez-vous de :
</p>

<div style="background-color: #fdf2f8; border-radius: 8px; padding: 20px; margin: 25px 0;">
    <h3 style="margin: 0 0 15px; color: #831843; font-size: 16px;">🛠️ Optimisez votre impact :</h3>
    <ul style="margin: 0; padding: 0 0 0 20px; color: #9d174d;">
        <li style="margin-bottom: 10px;"><strong>Complétez votre profil</strong> : Ajoutez une photo et une biographie
            inspirante.</li>
        <li style="margin-bottom: 10px;"><strong>Définissez vos disponibilités</strong> : Ouvrez des créneaux pour vos
            futures séances.</li>
        <li style="margin-bottom: 10px;"><strong>Organisez votre Roadmap</strong> : Guidez les jeunes avec des étapes
            claires.</li>
    </ul>
</div>

<table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
    <tr>
        <td align="center">
            <a href="{{ route('mentor.dashboard') }}"
                style="display: inline-block; background: linear-gradient(135deg, #db2777 0%, #be185d 100%); color: #ffffff; padding: 14px 32px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 16px;">
                🚀 Accéder à mon Dashboard Mentor
            </a>
        </td>
    </tr>
</table>

<p style="margin: 30px 0 0; color: #374151;">
    Nous sommes impatients de voir l'impact que vous aurez !<br>
    <strong>L'équipe Brillio</strong>
</p>
@endsection