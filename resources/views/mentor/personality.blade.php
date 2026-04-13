@extends('layouts.mentor')

@section('title', 'Test de personnalité MBTI')

@section('content')
    @include('shared.personality-test', ['theme' => 'mentor'])
@endsection