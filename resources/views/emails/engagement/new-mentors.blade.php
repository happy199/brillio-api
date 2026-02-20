@extends('emails.layouts.base')

@section('content')
<p style="margin: 0 0 20px;">Bonjour <strong>{{ $jeune->name }}</strong>,</p>

<p style="margin: 0 0 30px; font-size: 18px;">
    Cette semaine, de nouveaux professionnels passionnés ont rejoint la communauté Brillio pour vous accompagner dans
    votre réussite.
</p>

<div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin: 25px 0;">
    <h3
        style="margin: 0 0 20px; color: #1e293b; font-size: 18px; border-bottom: 2px solid #6366f1; display: inline-block; padding-bottom: 5px;">
        🌟 Les nouveaux visages</h3>

    @foreach($mentors as $mentor)
    <table width="100%" cellpadding="0" cellspacing="0"
        style="margin-bottom: 25px; border-bottom: 1px solid #f1f5f9; padding-bottom: 20px;">
        <tr>
            <td style="vertical-align: middle;">
                <p style="margin: 0 0 5px; font-weight: 700; color: #334155; font-size: 17px;">{{ $mentor->name }}</p>
                <p style="margin: 0 0 5px; color: #64748b; font-size: 14px;">
                    <strong>{{ $mentor->mentorProfile->current_position }}</strong> @ {{
                    $mentor->mentorProfile->current_company }}
                </p>
                <p style="margin: 0; color: #6366f1; font-size: 13px; font-weight: 600;">
                    🎯 Expert en {{ $mentor->mentorProfile->specializationModel->name ??
                    $mentor->mentorProfile->specialization }}
                </p>
            </td>
            <td style="vertical-align: middle; text-align: right; width: 120px;">
                <a href="{{ route('jeune.mentors.show', ['mentor' => $mentor->mentorProfile]) }}"
                    style="display: inline-block; background-color: #ffffff; color: #6366f1; border: 1px solid #6366f1; padding: 10px 16px; text-decoration: none; border-radius: 6px; font-size: 14px; font-weight: 600; white-space: nowrap;">
                    Voir profil
                </a>
            </td>
        </tr>
    </table>
    @endforeach
</div>

<p style="margin: 20px 0; text-align: center;">
    Ne manquez pas l'opportunité d'échanger avec ces experts ! Les premiers arrivés sont les premiers servis.
</p>

<table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
    <tr>
        <td align="center">
            <a href="{{ route('jeune.mentors') }}"
                style="display: inline-block; background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: #ffffff; padding: 14px 32px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 16px;">
                🔍 Découvrir tous les mentors
            </a>
        </td>
    </tr>
</table>

<p style="margin: 30px 0 0; color: #374151;">
    Bonne exploration,<br>
    <strong>L'équipe Brillio</strong>
</p>
@endsection