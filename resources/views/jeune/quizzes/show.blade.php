@extends('layouts.jeune')

@section('title', 'Quiz : ' . $quiz->title)

@section('content')
<div class="max-w-3xl mx-auto space-y-8">
    <!-- Navigation -->
    <a href="{{ route('jeune.resources.show', $resource) }}"
        class="inline-flex items-center text-gray-500 hover:text-indigo-600 transition">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Retour à la ressource
    </a>

    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm">
        <div class="p-8 md:p-12">
            <header class="mb-8 text-center">
                <div class="w-16 h-16 bg-indigo-100 text-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-6">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                    </svg>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">{{ $quiz->title }}</h1>
                @if($quiz->description)
                    <p class="text-lg text-gray-600">{{ $quiz->description }}</p>
                @endif
                <div class="mt-4 flex items-center justify-center gap-4 text-sm text-gray-500 font-medium">
                    <span class="bg-gray-100 px-3 py-1 rounded-full">{{ $quiz->questions->count() }} questions</span>
                    <span>•</span>
                    <span class="bg-gray-100 px-3 py-1 rounded-full">Total : {{ $quiz->questions->sum('points') }} points</span>
                </div>
            </header>

            <form action="{{ route('jeune.quizzes.submit', $quiz) }}" method="POST" class="space-y-10">
                @csrf
                
                @foreach($quiz->questions as $index => $question)
                <div class="bg-gray-50 border border-gray-200 rounded-xl p-6 md:p-8">
                    <div class="flex items-start justify-between gap-4 mb-6">
                        <div>
                            <h3 class="text-lg md:text-xl font-bold text-gray-900">
                                <span class="text-indigo-600 mr-2">{{ $index + 1 }}.</span> {{ $question->question_text }}
                            </h3>
                            @if($question->type === 'multiple')
                            <p class="text-xs text-gray-500 mt-1 uppercase font-semibold tracking-wider">Choix multiple (plusieurs réponses possibles)</p>
                            @else
                            <p class="text-xs text-gray-500 mt-1 uppercase font-semibold tracking-wider">Choix unique (une seule réponse possible)</p>
                            @endif
                        </div>
                        <span class="shrink-0 bg-white border border-gray-200 text-gray-500 text-xs font-bold px-3 py-1 rounded-full shadow-sm">
                            {{ $question->points }} {{ $question->points > 1 ? 'pts' : 'pt' }}
                        </span>
                    </div>
                    
                    <div class="space-y-3">
                        @foreach($question->options as $option)
                        <label class="flex items-center p-4 border border-gray-200 rounded-lg cursor-pointer bg-white hover:bg-indigo-50 hover:border-indigo-200 transition group has-[:checked]:bg-indigo-50 has-[:checked]:border-indigo-500 has-[:checked]:ring-1 has-[:checked]:ring-indigo-500">
                            @if($question->type === 'multiple')
                            <input type="checkbox" name="answers[{{ $question->id }}][]" value="{{ $option->id }}" class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                            @else
                            <input type="radio" name="answers[{{ $question->id }}]" value="{{ $option->id }}" class="w-5 h-5 text-indigo-600 border-gray-300 focus:ring-indigo-500">
                            @endif
                            <span class="ml-4 text-gray-700 font-medium">{{ $option->option_text }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endforeach
                
                <div class="pt-6 border-t border-gray-100 text-center">
                    <button type="submit" class="bg-indigo-600 text-white font-bold py-4 px-10 rounded-xl hover:bg-indigo-700 transition shadow-lg hover:shadow-indigo-200/50 hover:-translate-y-1 transform text-lg">
                        Soumettre mes réponses
                    </button>
                    <p class="text-xs text-gray-400 mt-4">Vous ne pourrez plus modifier vos réponses après la soumission.</p>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
