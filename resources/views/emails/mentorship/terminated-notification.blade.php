@extends('emails.layouts.base')

@section('content')
<p style="margin: 0 0 20px;">Bonjour <strong>{{ $recipient->name }}</strong>,</p>

<p style="margin: 0 0 30px; font-size: 18px;">
    Nous vous informons que <strong>{{ $actorName }}</strong> a décidé de mettre fin à votre relation de mentorat.
</p>

<div style="background: #fff1f2; border-radius: 8px; padding: 20px; margin: 20px 0; border: 1px solid #fecdd3;">
    <p style="margin: 0 0 8px; font-weight: 600; color: #991b1b;">Raison de la rupture :</p>
    <p style="margin: 0; color: #b91c1c; font-style: italic;">
        "{{ $reason }}"
    </p>
</div>

<p style="margin: 20px 0;">
    Cette décision prend effet immédiatement. Votre organisation parrainante a également été informée de cette clôture.
</p>

<p style="margin: 30px 0 0; color: #374151;">
    Nous vous souhaitons une bonne continuation dans votre parcours,<br>
    <strong>L'équipe Brillio</strong>
</p>
@endsection