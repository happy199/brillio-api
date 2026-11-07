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
    @php
        $mentorProfile = $mentor->mentorProfile ?? null;
        $profileRoute = $mentorProfile ? route('jeune.mentors.show', ['mentor' => $mentorProfile]) : route('jeune.mentors');
        $position = $mentorProfile->current_position ?? null;
        $company = $mentorProfile->current_company ?? null;
        $specializationName = $mentorProfile->specializationModel->name ?? $mentorProfile->specialization ?? null;
        $avatarUrl = $mentor->avatar_url ?? ('https://ui-avatars.com/api/?name='.urlencode($mentor->name).'&color=5844E3&background=EEF2FF');
    @endphp
    <table width="100%" cellpadding="0" cellspacing="0"
        style="margin-bottom: 25px; border-bottom: 1px solid #f1f5f9; padding-bottom: 20px;">
        <tr>
            <td style="vertical-align: middle; width: 68px; padding-right: 16px;">
                <a href="{{ $profileRoute }}" style="text-decoration: none; display: block;">
                    <img src="{{ $avatarUrl }}"
                         alt="{{ $mentor->name }}"
                         width="56"
                         height="56"
                         style="width: 56px; height: 56px; border-radius: 50%; object-fit: cover; display: block; border: 2px solid #e0e7ff; background-color: #eef2ff;">
                </a>
            </td>
            <td style="vertical-align: middle;">
                <p style="margin: 0 0 4px; font-weight: 700; color: #1e293b; font-size: 16px; line-height: 1.3;">
                    <a href="{{ $profileRoute }}" style="color: #1e293b; text-decoration: none;">
                        {{ $mentor->name }}
                    </a>
                </p>
                @if($position || $company)
                <p style="margin: 0 0 4px; color: #64748b; font-size: 13px; line-height: 1.4;">
                    @if($position)<strong>{{ $position }}</strong>@endif
                    @if($position && $company) @ @endif
                    @if($company){{ $company }}@endif
                </p>
                @endif
                @if($specializationName)
                <p style="margin: 0; color: #5844E3; font-size: 12px; font-weight: 600; line-height: 1.4;">
                    🎯 Expert en {{ $specializationName }}
                </p>
                @endif
            </td>
            <td style="vertical-align: middle; text-align: right; width: 110px; padding-left: 10px;">
                <a href="{{ $profileRoute }}"
                    style="display: inline-block; background-color: #ffffff; color: #5844E3; border: 1px solid #5844E3; padding: 9px 14px; text-decoration: none; border-radius: 8px; font-size: 13px; font-weight: 600; white-space: nowrap;">
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