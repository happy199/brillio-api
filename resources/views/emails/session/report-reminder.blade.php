@extends('emails.layouts.base')

@section('content')
<p style="margin: 0 0 20px;">Bonjour <strong>{{ $mentor->name }}</strong>,</p>

<p style="margin: 0 0 20px; color: #374151; line-height: 1.6;">
    Sauf erreur de notre part, vous n'avez pas encore soumis le compte rendu pour votre séance avec <strong>{{
        $session->mentees->first()?->name ?? 'votre jeune' }}</strong>.
</p>

<div style="background-color: #fffbeb; border-radius: 8px; padding: 20px; margin: 25px 0; border: 1px solid #fde68a;">
    <p style="margin: 0 0 10px; font-weight: 600; color: #92400e;">💡 Rappel de la séance :</p>
    <p style="margin: 0; color: #b45309;">
        <strong>Titre :</strong> {{ $session->title }}<br>
        <strong>Date :</strong> {{ $session->scheduled_at->translatedFormat('l j F Y à H:i') }}
    </p>
</div>

<p style="margin: 20px 0; color: #374151; font-weight: 600;">
    Pourquoi est-ce important ?
</p>
<p style="margin: 0 0 20px; color: #374151; line-height: 1.6;">
    La soumission de votre compte rendu est l'étape finale qui nous permet de <strong>débloquer vos revenus</strong> sur
    votre portefeuille Brillio. C'est également un outil précieux pour le suivi du jeune.
</p>

<table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
    <tr>
        <td align="center">
            <a href="{{ route('mentor.mentorship.sessions.show', $session) }}"
                style="display: inline-block; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #ffffff; padding: 14px 32px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 16px;">
                📝 Soumettre mon compte rendu
            </a>
        </td>
    </tr>
</table>

<p style="margin: 30px 0 0; color: #374151;">
    Merci de votre réactivité,<br>
    <strong>L'équipe Brillio</strong>
</p>
@endsection