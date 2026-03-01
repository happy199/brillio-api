@extends('emails.layouts.base')

@section('content')
<p style="margin: 0 0 20px;">Bonjour <strong>{{ $user->name }}</strong>,</p>

<p style="margin: 0 0 30px; font-size: 18px;">
    📚 Votre organisation <strong>{{ $organization->name }}</strong> vous a offert l'accès à une nouvelle ressource
    premium !
</p>

<div
    style="background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%); border-radius: 8px; padding: 20px; margin: 20px 0; border: 2px solid #ef4444;">
    <p style="margin: 0 0 10px; font-weight: 600; color: #991b1b; font-size: 16px;">📖 Ressource offerte</p>
    <p style="margin: 0; color: #374151;">
        <strong>Titre :</strong> {{ $resource->title }}<br>
        <strong>Type :</strong> {{ ucfirst($resource->type) }}
    </p>
</div>

<p style="margin: 20px 0; color: #374151;">
    Vous pouvez dès à présent consulter cette ressource dans votre bibliothèque personnelle sur Brillio.
</p>

<table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
    <tr>
        <td align="center">
            <a href="{{ route('jeune.resources.index') }}"
                style="display: inline-block; background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: #ffffff; padding: 14px 32px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 16px;">
                🚀 Voir mes ressources
            </a>
        </td>
    </tr>
</table>

<p style="margin: 30px 0 0; color: #374151;">
    Bonne lecture,<br>
    <strong>L'équipe Brillio</strong>
</p>
@endsection