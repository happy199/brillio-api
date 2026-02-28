@extends('emails.layouts.base')

@section('content')
<p style="margin: 0 0 20px;">Bonjour,</p>

<p style="margin: 0 0 30px; font-size: 18px;">
    Une relation de mentorat au sein de votre organisation a été rompue par <strong>{{ $actorName }}</strong>.
</p>

<div style="background: #f8fafc; border-radius: 8px; padding: 20px; margin: 20px 0; border: 1px solid #e2e8f0;">
    <p style="margin: 0 0 10px; color: #475569;">
        <strong>Jeune :</strong> {{ $jeuneName }}<br>
        <strong>Mentor :</strong> {{ $mentorName }}
    </p>
    <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 10px 0;">
    <p style="margin: 0 0 8px; font-weight: 600; color: #475569;">Raison invoquée :</p>
    <p style="margin: 0; color: #64748b; font-style: italic;">
        "{{ $reason }}"
    </p>
</div>

<p style="margin: 20px 0;">
    Cette information a été enregistrée dans vos historiques de relations.
</p>

<p style="margin: 30px 0 0; color: #374151;">
    Cordialement,<br>
    <strong>L'équipe Brillio</strong>
</p>
@endsection