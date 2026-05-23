@extends('layouts.jeune')

@section('title', 'Résultat : ' . $quiz->title)

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
            
            <!-- Score Header -->
            <div class="text-center mb-12">
                @php
                    $percentage = $attempt->max_score > 0 ? round(($attempt->score / $attempt->max_score) * 100) : 0;
                    $isSuccess = $percentage >= 50;
                    $colorClass = $isSuccess ? 'text-green-600' : 'text-red-500';
                    $bgClass = $isSuccess ? 'bg-green-50' : 'bg-red-50';
                    $icon = $isSuccess 
                        ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>'
                        : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
                @endphp
                
                <div class="w-24 h-24 {{ $bgClass }} {{ $colorClass }} rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        {!! $icon !!}
                    </svg>
                </div>
                
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-2">Résultat du Quiz</h1>
                <p class="text-lg text-gray-600 mb-6">{{ $quiz->title }}</p>
                
                <div class="inline-flex flex-col items-center justify-center p-6 bg-gray-50 rounded-2xl border border-gray-100 min-w-[200px]">
                    <span class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-1">Votre Score</span>
                    <div class="flex items-baseline gap-2">
                        <span class="text-5xl font-extrabold {{ $colorClass }}">{{ $attempt->score }}</span>
                        <span class="text-2xl font-bold text-gray-400">/ {{ $attempt->max_score }}</span>
                    </div>
                    <span class="mt-2 text-sm font-medium {{ $colorClass }}">{{ $percentage }}% de réussite</span>
                </div>
            </div>

            <!-- Detailed Answers -->
            <div class="space-y-6">
                <h3 class="text-xl font-bold text-gray-900 border-b border-gray-100 pb-4">Détails des réponses</h3>
                
                @foreach($quiz->questions as $index => $question)
                @php
                    $questionAnswers = $attempt->answers->where('quiz_question_id', $question->id);
                    $selectedOptionIds = $questionAnswers->pluck('quiz_option_id')->toArray();
                    
                    $isQuestionCorrect = false;
                    if ($question->type === 'multiple') {
                        $correctOptionIds = $question->options->where('is_correct', true)->pluck('id')->toArray();
                        sort($correctOptionIds);
                        $submittedSorted = $selectedOptionIds;
                        sort($submittedSorted);
                        $isQuestionCorrect = (array_map('strval', $correctOptionIds) === array_map('strval', $submittedSorted));
                    } else {
                        $submittedOptionId = $selectedOptionIds[0] ?? null;
                        $option = $question->options->firstWhere('id', $submittedOptionId);
                        $isQuestionCorrect = $option ? $option->is_correct : false;
                    }
                @endphp
                <div class="bg-white border {{ $isQuestionCorrect ? 'border-green-200' : 'border-red-200' }} rounded-xl p-6 relative overflow-hidden">
                    <!-- Status indicator -->
                    <div class="absolute top-0 right-0 w-2 h-full {{ $isQuestionCorrect ? 'bg-green-500' : 'bg-red-500' }}"></div>
                    
                    <div class="pr-6">
                        <div class="flex items-start justify-between gap-4 mb-4">
                            <h4 class="text-lg font-bold text-gray-900">
                                {{ $index + 1 }}. {{ $question->question_text }}
                            </h4>
                            <span class="shrink-0 text-sm font-bold {{ $isQuestionCorrect ? 'text-green-600' : 'text-red-500' }}">
                                {{ $isQuestionCorrect ? '+' . $question->points : '0' }} / {{ $question->points }} pts
                            </span>
                        </div>
                        
                        <div class="space-y-3">
                            @foreach($question->options as $option)
                            @php
                                $isSelected = in_array($option->id, $selectedOptionIds);
                                $isCorrect = $option->is_correct;
                                
                                $classes = 'flex items-center p-3 rounded-lg border ';
                                $icon = '';
                                
                                if ($question->type === 'multiple') {
                                    if ($isSelected && $isCorrect) {
                                        $classes .= 'bg-green-50 border-green-200 text-green-800 font-medium';
                                        $icon = '<svg class="w-5 h-5 text-green-500 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
                                    } elseif ($isSelected && !$isCorrect) {
                                        $classes .= 'bg-red-50 border-red-200 text-red-800 font-medium';
                                        $icon = '<svg class="w-5 h-5 text-red-500 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';
                                    } elseif (!$isSelected && $isCorrect) {
                                        $classes .= 'bg-green-50/50 border-green-200 text-green-700 border-dashed';
                                        $icon = '<svg class="w-5 h-5 text-green-500 mr-3 shrink-0 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
                                    } else {
                                        $classes .= 'bg-gray-50 border-gray-100 text-gray-500';
                                        $icon = '<div class="w-5 h-5 rounded border border-gray-300 mr-3 shrink-0"></div>';
                                    }
                                } else {
                                    if ($isSelected && $isCorrect) {
                                        $classes .= 'bg-green-50 border-green-200 text-green-800 font-medium';
                                        $icon = '<svg class="w-5 h-5 text-green-500 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
                                    } elseif ($isSelected && !$isCorrect) {
                                        $classes .= 'bg-red-50 border-red-200 text-red-800 font-medium';
                                        $icon = '<svg class="w-5 h-5 text-red-500 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';
                                    } elseif (!$isSelected && $isCorrect) {
                                        $classes .= 'bg-green-50/50 border-green-200 text-green-700 border-dashed';
                                        $icon = '<svg class="w-5 h-5 text-green-500 mr-3 shrink-0 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
                                    } else {
                                        $classes .= 'bg-gray-50 border-gray-100 text-gray-500';
                                        $icon = '<div class="w-5 h-5 rounded-full border border-gray-300 mr-3 shrink-0"></div>';
                                    }
                                }
                            @endphp
                            <div class="{{ $classes }}">
                                {!! $icon !!}
                                <span>{{ $option->option_text }}</span>
                                @if(!$isSelected && $isCorrect)
                                    <span class="ml-auto text-xs font-bold text-green-600 bg-green-100 px-2 py-1 rounded">Bonne réponse</span>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="mt-12 text-center">
                <a href="{{ route('jeune.resources.show', $resource) }}" class="inline-flex items-center justify-center px-8 py-3 text-base font-bold text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 transition shadow-lg">
                    Terminer et retourner à la ressource
                </a>
            </div>
            
        </div>
    </div>
</div>
@endsection
