@extends('emails.layouts.base')

@section('content')
    <div style="background-color: #ffffff;">
        {!! $content !!}
    </div>
    
    <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #f3f4f6; color: #9ca3af; font-size: 11px; text-align: center; font-style: italic;">
        Vous recevez cet email car vous êtes inscrit à la newsletter Brillio.
        <br>
        <a href="{{ route('newsletter.unsubscribe', ['token' => 'default']) }}" style="color: #6366f1; text-decoration: underline;">Se désabonner</a>
    </div>
@endsection
