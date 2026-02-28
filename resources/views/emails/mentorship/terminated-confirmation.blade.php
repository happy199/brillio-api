@extends('emails.layouts.base')

@section('content')
<p style="margin: 0 0 20px;">Bonjour <strong>{{ $actor->name }}</strong>,</p>

<p style="margin: 0 0 30px; font-size: 18px;">
    Cette email confirme que vous avez mis fin à votre relation de mentorat avec <strong>{{ $otherPartyName }}</strong>.
</p>

<div style="background: #f8fafc; border-radius: 8px; padding: 20px; margin: 20px 0; border: 1px solid #e2e8f0;">
    <p style="margin: 0 0 8px; font-weight: 600; color: #475569;">Raison invoquée :</p>
    <p style="margin: 0; color: #64748b; font-style: italic;">
        "{{ $reason }}"
    </p>
</div>

<p style="margin: 20px 0;">
    Cette action a été enregistrée et l'organisation parrainante ainsi que l'autre partie ont été informées.
</p>

<p style="margin: 30px 0 0; color: #374151;">
    Cordialement,<br>
    <strong>L'équipe Brillio</strong>
</p>
@endsection