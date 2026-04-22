@extends('emails.layouts.base')

@section('content')
<p style="margin: 0 0 20px;">Bonjour <strong>{{ $recipient->name }}</strong>,</p>

<p style="margin: 0 0 30px; font-size: 18px;">
    📝 Le compte rendu de la séance de mentorat <strong>"{{ $session->title }}"</strong> avec le mentor <strong>{{
        $session->mentor->name }}</strong> a été soumis et est maintenant disponible.
</p>

<div style="background: #f8fafc; border-radius: 8px; padding: 20px; margin: 20px 0; border: 1px solid #e2e8f0;">
    <p style="margin: 0 0 10px; font-weight: 600; color: #1e293b; font-size: 16px;">💬 Résumé de la séance</p>
    @if($showDetails && $session->report_content)
        <div style="margin-top: 15px; space-y: 15px;">
            @if(isset($session->report_content['progress']))
                <div style="margin-bottom: 15px;">
                    <p style="margin: 0 0 5px; font-weight: bold; font-size: 13px; color: #6366f1; text-transform: uppercase;">1. Progrès réalisés</p>
                    <p style="margin: 0; color: #475569; font-size: 14px;">{{ $session->report_content['progress'] }}</p>
                </div>
            @endif
            @if(isset($session->report_content['obstacles']))
                <div style="margin-bottom: 15px;">
                    <p style="margin: 0 0 5px; font-weight: bold; font-size: 13px; color: #6366f1; text-transform: uppercase;">2. Obstacles & Points Clés</p>
                    <p style="margin: 0; color: #475569; font-size: 14px;">{{ $session->report_content['obstacles'] }}</p>
                </div>
            @endif
            @if(isset($session->report_content['smart_goals']))
                <div>
                    <p style="margin: 0 0 5px; font-weight: bold; font-size: 13px; color: #6366f1; text-transform: uppercase;">3. Objectifs SMART</p>
                    <p style="margin: 0; color: #475569; font-size: 14px;">{{ $session->report_content['smart_goals'] }}</p>
                </div>
            @endif
        </div>
    @else
        <p style="margin: 0; color: #475569;">
            Vous pouvez maintenant consulter les progrès, les obstacles identifiés et les objectifs fixés lors de cette
            séance.
        </p>
    @endif
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