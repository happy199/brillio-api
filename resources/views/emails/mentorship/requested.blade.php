@extends('emails.layouts.base')

@section('content')
<p style="margin: 0 0 20px;">Bonjour <strong>{{ $mentor->name }}</strong>,</p>

<p style="margin: 0 0 20px;">
    Bonne nouvelle ! <strong>{{ $mentee->name }}</strong> souhaite bénéficier de votre mentorat.
</p>

<div
    style="background-color: #f9fafb; border-left: 4px solid #6366f1; padding: 16px; margin: 20px 0; border-radius: 4px;">
    <p style="margin: 0 0 10px; font-weight: 600; color: #374151;">Message de {{ $mentee->name }} :</p>
    <p style="margin: 0; color: #6b7280; font-style: italic;">"{{ $mentorship->request_message }}"</p>
</div>

@if($mentee->personalityTest)
<p style="margin: 20px 0; font-size: 14px;">
    <strong>Profil du jeune :</strong><br>
    📊 Personnalité MBTI : <span
        style="background-color: #e0e7ff; color: #4338ca; padding: 2px 8px; border-radius: 4px; font-weight: 600;">{{
        $mentee->personalityTest->mbti_type }}</span><br>
    🎓 Intérêts : {{ $mentee->personalityTest->career_sector ?? 'Non défini' }}
</p>
@endif

<table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
    <tr>
        <td align="center">
            <a href="{{ $acceptUrl }}"
                style="display: inline-block; background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: #ffffff; padding: 14px 32px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 16px;">
                🔍 Consulter la demande
            </a>
        </td>
    </tr>
</table>

<p style="margin: 20px 0; font-size: 14px; color: #6b7280;">
    💡 Vous pouvez consulter le profil complet de {{ $mentee->name }} avant de prendre votre décision.
</p>

<p style="margin: 30px 0 0; color: #374151;">
    Merci de partager votre expérience,<br>
    <strong>L'équipe Brillio</strong>
</p>
@endsection