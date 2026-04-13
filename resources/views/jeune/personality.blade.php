@extends('layouts.jeune')

@section('title', 'Test de personnalité MBTI')

@section('content')
    @include('shared.personality-test', ['theme' => 'jeune'])
@endsection