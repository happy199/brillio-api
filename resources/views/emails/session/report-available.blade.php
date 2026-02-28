@extends('emails.layouts.base')

@section('content')
<p style="margin: 0 0 20px;">Bonjour <strong>{{ $recipient->name }}</strong>,</p>

<p style="margin: 0 0 30px; font-size: 18px;">
    📝 Le compte rendu de la séance de mentorat <strong>"{{ $session->title }}"</strong> avec le mentor <strong>{{
        $session->mentor->name }}</strong> a été soumis et est maintenant disponible.
</p>

<div style="background: #f8fafc; border-radius: 8px; padding: 20px; margin: 20px 0; border: 1px solid #e2e8f0;">
    <p style="margin: 0 0 10px; font-weight: 600; color: #1e293b; font-size: 16px;">💬 Résumé de la séance</p>
    <p style="margin: 0; color: #475569;">
        Vous pouvez maintenant consulter les progrès, les obstacles identifiés et les objectifs fixés lors de cette
        séance.
    </p>
</div>

<table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
    <tr>
        <td align="center">
            <a href="{{ $sessionUrl }}"
                style="display: inline-block; background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: #ffffff; padding: 14px 32px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 16px;">
                📑 Voir le compte rendu
            </a>
        </td>
    </tr>
</table>

<p style="margin: 30px 0 0; color: #374151;">
    Bonne progression,<br>
    <strong>L'équipe Brillio</strong>
</p>
@endsection