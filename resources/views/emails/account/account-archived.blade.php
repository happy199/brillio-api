@extends('emails.layouts.base')

@section('content')
<p style="margin: 0 0 20px;">Bonjour <strong>{{ $user->name }}</strong>,</p>

<p style="margin: 0 0 20px; color: #374151; line-height: 1.6;">
    Nous vous informons que votre compte sur la plateforme <strong>Brillio</strong> a été archivé.
</p>

@if($reason)
<div style="background-color: #f3f4f6; border-radius: 8px; padding: 20px; margin: 25px 0;">
    <p style="margin: 0 0 5px; font-weight: 600; color: #1f2937;">Motif :</p>
    <p style="margin: 0; color: #4b5563;">{{ $reason }}</p>
</div>
@endif

<p style="margin: 20px 0; color: #374151; line-height: 1.6;">
    L'archivage signifie que votre profil n'est plus visible et que vos accès sont restreints. Vos données fondamentales
    sont conservées conformément à notre politique de confidentialité, mais votre activité est suspendue.
</p>

<p style="margin: 20px 0; color: #374151; line-height: 1.6;">
    Si vous pensez qu'il s'agit d'une erreur ou si vous souhaitez réactiver votre compte, n'hésitez pas à nous contacter
    en répondant directement à cet email.
</p>

<p style="margin: 30px 0 0; color: #374151;">
    Cordialement,<br>
    <strong>L'équipe Brillio</strong>
</p>
@endsection