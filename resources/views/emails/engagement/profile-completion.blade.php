@extends('emails.layouts.base')

@section('content')
<p style="margin: 0 0 20px;">Bonjour <strong>{{ $user->name }}</strong>,</p>

<p style="margin: 0 0 30px; font-size: 18px;">
    Saviez-vous qu'un profil complet sur Brillio multiplie par 3 vos chances d'être contacté et d'avancer dans vos
    projets ?
</p>

<div style="background-color: #f5f3ff; border-radius: 8px; padding: 25px; margin: 20px 0; border: 1px solid #ddd6fe;">
    <p style="margin: 0 0 15px; font-weight: 600; color: #5b21b6; font-size: 16px;">🎯 Ce qu'il vous reste à faire
        (moins de 5 minutes) :</p>

    <ul style="margin: 0; padding: 0; list-style-type: none;">
        @foreach($missingSections as $section)
        <li style="margin-bottom: 12px; display: flex; align-items: center;">
            <span style="color: #8b5cf6; margin-right: 10px;">⚡</span>
            <span style="color: #374151;">{{ $section }}</span>
        </li>
        @endforeach
    </ul>
</div>

<p style="margin: 20px 0;">
    Un profil bien renseigné permet aux @if($user->isMentor()) jeunes @else mentors @endif de mieux comprendre votre
    parcours et vos attentes.
</p>

<table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
    <tr>
        <td align="center">
            <a href="{{ $user->isMentor() ? route('mentor.profile') : route('jeune.profile') }}"
                style="display: inline-block; background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: #ffffff; padding: 14px 32px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 16px;">
                🚀 Compléter mon profil
            </a>
        </td>
    </tr>
</table>

<p style="margin: 20px 0; font-size: 14px; color: #6b7280; text-align: center;">
    <em>Besoin d'aide ? Répondez simplement à cet email, notre équipe est là pour vous !</em>
</p>

<p style="margin: 30px 0 0; color: #374151;">
    À vous de jouer,<br>
    <strong>L'équipe Brillio</strong>
</p>
@endsection