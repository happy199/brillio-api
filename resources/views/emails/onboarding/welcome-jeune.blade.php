@extends('emails.layouts.base')

@section('content')
<p style="margin: 0 0 20px;">Bonjour <strong>{{ $user->name }}</strong>,</p>

<p style="margin: 0 0 30px; font-size: 18px;">
    🎉 Félicitations et bienvenue sur <strong>Brillio</strong> !
</p>

<p style="margin: 0 0 20px; color: #374151; line-height: 1.6;">
    Nous sommes ravis de vous accompagner dans votre parcours de découverte et de croissance. Voici vos premières étapes
    pour tirer le meilleur parti de la plateforme :
</p>

<div style="background-color: #f3f4f6; border-radius: 8px; padding: 20px; margin: 25px 0;">
    <h3 style="margin: 0 0 15px; color: #1e1b4b; font-size: 16px;">🚀 Vos premiers pas :</h3>
    <ul style="margin: 0; padding: 0 0 0 20px; color: #4b5563;">
        <li style="margin-bottom: 10px;"><strong>Découvrez votre personnalité</strong> : Passez notre test MBTI pour
            mieux comprendre vos forces.</li>
        <li style="margin-bottom: 10px;"><strong>Trouvez votre Mentor</strong> : Explorez notre communauté d'experts
            prêts à partager leur expérience.</li>
        <li style="margin-bottom: 10px;"><strong>Réservez une séance</strong> : Votre première discussion pourrait
            changer votre avenir !</li>
    </ul>
</div>

<table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
    <tr>
        <td align="center">
            <a href="{{ route('jeune.dashboard') }}"
                style="display: inline-block; background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); color: #ffffff; padding: 14px 32px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 16px;">
                🎯 Explorer mon espace Jeune
            </a>
        </td>
    </tr>
</table>

<p style="margin: 30px 0 0; color: #374151;">
    À très bientôt,<br>
    <strong>L'équipe Brillio</strong>
</p>
@endsection