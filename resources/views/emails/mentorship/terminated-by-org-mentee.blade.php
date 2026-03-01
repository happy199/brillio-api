@extends('emails.layouts.base')

@section('content')
<p style="margin: 0 0 20px;">Bonjour <strong>{{ $mentee->name }}</strong>,</p>

<p style="margin: 0 0 30px; font-size: 18px;">
    Nous vous informons que votre organisation parrainante, <strong>{{ $organizationName }}</strong>, a décidé de mettre
    fin à votre relation de mentorat avec <strong>{{ $mentor->name }}</strong>.
</p>

<div style="background: #f8fafc; border-radius: 8px; padding: 20px; margin: 20px 0; border: 1px solid #e2e8f0;">
    <p style="margin: 0; color: #475569;">
        Cette décision prend effet immédiatement. Vous ne pourrez plus planifier de nouvelles sessions avec ce mentor
        dans le cadre de ce parrainage.
    </p>
</div>

<p style="margin: 20px 0;">
    N'hésitez pas à explorer notre catalogue de mentors pour trouver un nouvel accompagnement ou à contacter votre
    organisation pour plus d'informations.
</p>

<table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
    <tr>
        <td align="center">
            <a href="{{ route('jeune.mentors') }}"
                style="display: inline-block; background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: #ffffff; padding: 14px 32px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 16px;">
                🔍 Trouver un autre mentor
            </a>
        </td>
    </tr>
</table>

<p style="margin: 30px 0 0; color: #374151;">
    Bonne continuation dans votre parcours,<br>
    <strong>L'équipe Brillio</strong>
</p>
@endsection