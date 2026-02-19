@extends('emails.layouts.base')

@section('content')
<p style="margin: 0 0 20px;">Bonjour <strong>{{ $mentee->name }}</strong>,</p>

<p style="margin: 0 0 30px; font-size: 18px;">
    Nous avons du nouveau concernant votre demande de mentorat avec <strong>{{ $mentor->name }}</strong>.
</p>

<div
    style="background-color: #fef2f2; border-left: 4px solid #ef4444; padding: 20px; margin: 20px 0; border-radius: 4px;">
    <p style="margin: 0 0 10px; font-weight: 600; color: #991b1b;">Statut : Demande non retenue</p>
    @if($reason)
    <p style="margin: 0; color: #b91c1c; font-style: italic;">
        "{{ $reason }}"
    </p>
    @else
    <p style="margin: 0; color: #b91c1c;">
        Le mentor n'est pas disponible pour de nouvelles relations de mentorat actuellement.
    </p>
    @endif
</div>

<p style="margin: 20px 0;">
    Ne vous découragez pas ! Brillio regorge d'autres mentors exceptionnels qui seraient ravis de vous accompagner.
</p>

<table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
    <tr>
        <td align="center">
            <a href="{{ route('jeune.mentors') }}"
                style="display: inline-block; background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: #ffffff; padding: 14px 32px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 16px;">
                🔍 Explorer d'autres mentors
            </a>
        </td>
    </tr>
</table>

<p style="margin: 20px 0; font-size: 14px; color: #6b7280;">
    💡 <strong>Conseil :</strong> N'hésitez pas à affiner vos critères de recherche pour trouver le mentor qui
    correspond le mieux à vos objectifs actuels.
</p>

<p style="margin: 30px 0 0; color: #374151;">
    À très bientôt sur Brillio,<br>
    <strong>L'équipe Brillio</strong>
</p>
@endsection