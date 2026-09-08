@extends('layouts.organization')

@section('title', 'Modifier la Ressource Interne')

@push('styles')
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
    .ql-editor {
        min-height: 200px;
        font-family: 'Inter', sans-serif;
        font-size: 0.875rem;
    }

    .ql-toolbar.ql-snow {
        border-top-left-radius: 0.5rem;
        border-top-right-radius: 0.5rem;
        border-color: #e5e7eb;
        background-color: #f9fafb;
    }

    .ql-container.ql-snow {
        border-bottom-left-radius: 0.5rem;
        border-bottom-right-radius: 0.5rem;
        border-color: #e5e7eb;
        background-color: #ffffff;
    }
</style>
@endpush

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('organization.resources.index', ['tab' => 'internal']) }}" class="text-gray-500 hover:text-gray-700">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Modifier : {{ $resource->title }}</h1>
            <p class="text-sm text-gray-500">Mettez à jour le contenu et les paramètres de cette ressource interne.</p>
        </div>
    </div>

    @if ($errors->any())
    <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">Des erreurs ont été détectées :</h3>
                <ul class="mt-2 text-xs text-red-700 list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif

    <form action="{{ route('organization.resources.update', $resource) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Colonne Principale (Informations + Contenu + Quiz) -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Informations de base -->
                <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-gray-900 border-b border-gray-100 pb-4">Informations Principales</h2>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Titre de la ressource <span class="text-red-500">*</span></label>
                            <input type="text" name="title" value="{{ old('title', $resource->title) }}" required
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-organization-500 focus:border-organization-500 block w-full p-3 transition">
                            @error('title') <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Description courte <span class="text-red-500">*</span></label>
                            <textarea name="description" rows="3" required
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-organization-500 focus:border-organization-500 block w-full p-3 transition resize-y">{{ old('description', $resource->description) }}</textarea>
                            @error('description') <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Contenu textuel / pédagogique</label>
                            <div class="relative" x-data x-init="
                                const quill = new Quill($refs.editor, {
                                    theme: 'snow',
                                    placeholder: 'Rédigez ou collez le contenu pédagogique ici...',
                                    modules: {
                                        toolbar: [
                                            [{ 'header': [1, 2, 3, false] }],
                                            ['bold', 'italic', 'underline', 'strike'],
                                            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                                            [{ 'color': [] }, { 'background': [] }],
                                            ['link'],
                                            ['clean']
                                        ]
                                    }
                                });

                                if ($refs.contentInput.value) {
                                    quill.clipboard.dangerouslyPasteHTML($refs.contentInput.value);
                                }

                                quill.on('text-change', function() {
                                    $refs.contentInput.value = quill.root.innerHTML;
                                });
                            ">
                                <div x-ref="editor" class="bg-white"></div>
                                <input type="hidden" name="content" x-ref="contentInput" value="{{ old('content', $resource->content) }}">
                            </div>
                            @error('content') <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- Quiz d'évaluation (Optionnel) -->
                <div x-data="quizManager()" class="bg-white rounded-xl border border-gray-200 p-6 space-y-6 relative overflow-hidden shadow-sm">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-gray-100 pb-4 gap-4">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">Quiz d'Évaluation <span class="text-sm font-normal text-gray-500 ml-2">(Optionnel)</span></h2>
                            <p class="text-xs text-gray-500 mt-1">Ajoutez ou modifiez les quiz pour tester la compréhension de vos jeunes.</p>
                        </div>
                        <button type="button" @click="addQuiz()" class="text-sm font-bold text-organization-600 bg-organization-50 hover:bg-organization-100 px-3 py-1.5 rounded-lg transition flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Ajouter un Quiz
                        </button>
                    </div>

                    <input type="hidden" name="quizzes_data" :value="JSON.stringify(quizzes)">

                    <div class="space-y-6">
                        <template x-for="(quiz, qIndex) in quizzes" :key="qIndex">
                            <div class="border border-organization-100 bg-organization-50/20 rounded-xl p-4 relative">
                                <button type="button" @click="removeQuiz(qIndex)" class="absolute top-4 right-4 text-gray-400 hover:text-red-500">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>

                                <div class="space-y-4 pr-8">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Nom du Quiz</label>
                                        <input type="text" x-model="quiz.title" placeholder="Ex: Quiz de validation des acquis" class="bg-white border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-organization-500 focus:border-organization-500 block w-full p-2.5">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Description courte (Optionnelle)</label>
                                        <input type="text" x-model="quiz.description" placeholder="Objectif de ce quiz..." class="bg-white border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-organization-500 focus:border-organization-500 block w-full p-2.5">
                                    </div>

                                    <!-- Questions -->
                                    <div class="mt-4 border-t border-organization-100 pt-4">
                                        <div class="flex justify-between items-center mb-3">
                                            <h4 class="text-sm font-bold text-gray-700">Questions</h4>
                                            <div class="flex gap-4">
                                                <button type="button" @click="addQuestion(qIndex, 'single')" class="text-xs text-organization-600 hover:underline flex items-center gap-1 font-semibold">
                                                    + Choix unique
                                                </button>
                                                <button type="button" @click="addQuestion(qIndex, 'multiple')" class="text-xs text-organization-600 hover:underline flex items-center gap-1 font-semibold">
                                                    + Choix multiple
                                                </button>
                                            </div>
                                        </div>

                                        <div class="space-y-4">
                                            <template x-for="(question, qsIndex) in quiz.questions" :key="qsIndex">
                                                <div class="bg-white border border-gray-200 rounded-lg p-3 relative shadow-sm">
                                                    <button type="button" @click="removeQuestion(qIndex, qsIndex)" class="absolute top-3 right-3 text-gray-400 hover:text-red-500">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                    </button>

                                                    <div class="mb-3 pr-8 flex gap-2 items-start">
                                                        <div class="flex-1">
                                                            <input type="text" x-model="question.question_text" placeholder="Posez votre question ici..." class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-organization-500 focus:border-organization-500 block w-full p-2">
                                                        </div>
                                                        <div class="w-20">
                                                            <input type="number" x-model="question.points" min="1" placeholder="Pts" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-organization-500 focus:border-organization-500 block w-full p-2">
                                                        </div>
                                                    </div>

                                                    <!-- Options -->
                                                    <div class="pl-4 space-y-3 border-l-2 border-gray-100">
                                                        <template x-for="(option, optIndex) in question.options" :key="optIndex">
                                                            <div class="flex flex-col gap-1">
                                                                <div class="flex items-center gap-2">
                                                                    <template x-if="question.type === 'multiple'">
                                                                        <input type="checkbox" x-model="option.is_correct" class="w-4 h-4 text-organization-600 bg-gray-100 border-gray-300 rounded focus:ring-organization-500 cursor-pointer">
                                                                    </template>
                                                                    <template x-if="question.type === 'single' || !question.type">
                                                                        <input type="radio" :name="'correct_'+qIndex+'_'+qsIndex" :checked="option.is_correct" @change="setCorrectOption(qIndex, qsIndex, optIndex)" class="w-4 h-4 text-organization-600 bg-gray-100 border-gray-300 focus:ring-organization-500 cursor-pointer">
                                                                    </template>
                                                                    <input type="text" x-model="option.option_text" placeholder="Option de réponse" class="flex-1 bg-transparent border-b border-gray-200 focus:border-organization-500 focus:ring-0 text-sm px-1 py-1">
                                                                    <button type="button" @click="removeOption(qIndex, qsIndex, optIndex)" class="text-gray-300 hover:text-red-500 p-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                                                                </div>
                                                            </div>
                                                        </template>
                                                        <button type="button" @click="addOption(qIndex, qsIndex)" class="text-[10px] text-gray-500 hover:text-organization-600 mt-2 uppercase font-bold tracking-wider inline-flex items-center gap-1">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                                            Ajouter option
                                                        </button>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <template x-if="quizzes.length === 0">
                            <div class="text-center py-6 text-gray-400 text-sm italic border-2 border-dashed border-gray-200 rounded-xl">
                                Aucun quiz ajouté. Cliquez sur "Ajouter un Quiz" pour commencer.
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Colonne Latérale (Paramètres, Média & Mise à jour) -->
            <div class="space-y-6">
                <!-- Paramètres de Type et Fichier -->
                <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4 shadow-sm">
                    <h3 class="text-md font-semibold text-gray-900 border-b border-gray-100 pb-3">Format & Média</h3>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Type de Ressource <span class="text-red-500">*</span></label>
                        <select name="type" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-organization-500 focus:border-organization-500 block w-full p-2.5">
                            <option value="article" {{ old('type', $resource->type) === 'article' ? 'selected' : '' }}>📄 Article</option>
                            <option value="video" {{ old('type', $resource->type) === 'video' ? 'selected' : '' }}>🎥 Vidéo</option>
                            <option value="tool" {{ old('type', $resource->type) === 'tool' ? 'selected' : '' }}>🔧 Outil</option>
                            <option value="exercise" {{ old('type', $resource->type) === 'exercise' ? 'selected' : '' }}>📝 Exercice</option>
                            <option value="template" {{ old('type', $resource->type) === 'template' ? 'selected' : '' }}>📋 Modèle</option>
                            <option value="script" {{ old('type', $resource->type) === 'script' ? 'selected' : '' }}>📜 Script</option>
                            <option value="book" {{ old('type', $resource->type) === 'book' ? 'selected' : '' }}>📚 Livre / Support PDF</option>
                            <option value="podcast" {{ old('type', $resource->type) === 'podcast' ? 'selected' : '' }}>🎧 Podcast</option>
                            <option value="webinar" {{ old('type', $resource->type) === 'webinar' ? 'selected' : '' }}>📺 Webinaire</option>
                            <option value="guide" {{ old('type', $resource->type) === 'guide' ? 'selected' : '' }}>🧭 Guide</option>
                            <option value="case_study" {{ old('type', $resource->type) === 'case_study' ? 'selected' : '' }}>📊 Étude de cas</option>
                            <option value="course" {{ old('type', $resource->type) === 'course' ? 'selected' : '' }}>🎓 Cours / Formation</option>
                        </select>
                        @error('type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Image de couverture</label>
                        @if($resource->preview_image_path)
                        <div class="mb-2 w-full aspect-video rounded-lg overflow-hidden bg-gray-100">
                            <img src="{{ Storage::url($resource->preview_image_path) }}" class="w-full h-full object-cover">
                        </div>
                        @endif
                        <input type="file" name="preview_image" accept="image/*"
                            class="block w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-organization-50 file:text-organization-700 hover:file:bg-organization-100 cursor-pointer">
                        <p class="text-[10px] text-gray-400 mt-1">PNG, JPG ou WebP (Max 5 Mo)</p>
                        @error('preview_image') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Fichier joint téléchargeable</label>
                        @if($resource->file_path)
                        <div class="mb-2 p-2 bg-gray-50 rounded border border-gray-200 text-xs text-gray-600 flex items-center justify-between">
                            <span class="truncate">Fichier actuel : {{ basename($resource->file_path) }}</span>
                        </div>
                        @endif
                        <input type="file" name="file"
                            class="block w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-organization-50 file:text-organization-700 hover:file:bg-organization-100 cursor-pointer">
                        <p class="text-[10px] text-gray-400 mt-1">PDF, ZIP, DOCX, etc. (Max 20 Mo)</p>
                        @error('file') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Mots-clés / Tags</label>
                        <input type="text" name="tags" value="{{ old('tags', is_array($resource->tags) ? implode(', ', $resource->tags) : $resource->tags) }}"
                            placeholder="Ex: orientation, cv, entretien, stage"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-organization-500 focus:border-organization-500 block w-full p-2.5">
                        <p class="text-[10px] text-gray-400 mt-1">Séparez par des virgules</p>
                    </div>
                </div>

                <!-- Tarification & Disponibilité -->
                <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4 shadow-sm" x-data="{ isPremium: '{{ old('is_premium', $resource->is_premium ? '1' : '0') }}' }">
                    <h3 class="text-md font-semibold text-gray-900 border-b border-gray-100 pb-3">Accès</h3>

                    <div class="space-y-3">
                        <label class="flex items-center gap-3 p-3 rounded-lg border cursor-pointer transition" :class="isPremium === '0' ? 'bg-green-50 border-green-300' : 'bg-gray-50 border-gray-200'">
                            <input type="radio" name="is_premium" value="0" x-model="isPremium" class="text-organization-600 focus:ring-organization-500">
                            <div>
                                <span class="text-sm font-bold text-gray-900">Gratuit</span>
                                <p class="text-xs text-gray-500">Disponible immédiatement pour tous vos jeunes</p>
                            </div>
                        </label>

                        <label class="flex items-center gap-3 p-3 rounded-lg border cursor-pointer transition" :class="isPremium === '1' ? 'bg-organization-50 border-organization-300' : 'bg-gray-50 border-gray-200'">
                            <input type="radio" name="is_premium" value="1" x-model="isPremium" class="text-organization-600 focus:ring-organization-500">
                            <div>
                                <span class="text-sm font-bold text-gray-900">Payant (Premium)</span>
                                <p class="text-xs text-gray-500">Déblocable via des crédits ou offert par votre structure</p>
                            </div>
                        </label>
                    </div>

                    <div x-show="isPremium === '1'" x-cloak class="pt-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Prix en FCFA <span class="text-red-500">*</span></label>
                        <input type="number" name="price" value="{{ old('price', $resource->price) }}" min="200" step="50"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-organization-500 focus:border-organization-500 block w-full p-2.5">
                        <p class="text-[10px] text-gray-400 mt-1">Minimum 200 FCFA</p>
                    </div>
                </div>

                <!-- Boutons d'action -->
                <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-3 shadow-sm">
                    <button type="submit" class="w-full bg-organization-600 hover:bg-organization-700 text-white font-bold py-3 px-4 rounded-xl shadow-sm transition flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Enregistrer les modifications
                    </button>
                    <a href="{{ route('organization.resources.index', ['tab' => 'internal']) }}"
                        class="w-full block text-center text-sm text-gray-500 hover:text-gray-700 py-2">
                        Annuler
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<script>
    function quizManager() {
        return {
            quizzes: initialQuizzes || [],
            addQuiz() {
                this.quizzes.push({
                    title: '',
                    description: '',
                    questions: [
                        {
                            question_text: '',
                            type: 'single',
                            points: 1,
                            options: [
                                { option_text: '', is_correct: true, explanation: '' },
                                { option_text: '', is_correct: false, explanation: '' }
                            ]
                        }
                    ]
                });
            },
            removeQuiz(qIndex) {
                if (confirm('Êtes-vous sûr de vouloir supprimer ce quiz ?')) {
                    this.quizzes.splice(qIndex, 1);
                }
            },
            addQuestion(qIndex, type = 'single') {
                this.quizzes[qIndex].questions.push({
                    question_text: '',
                    type: type,
                    points: 1,
                    options: [
                        { option_text: '', is_correct: false, explanation: '' },
                        { option_text: '', is_correct: false, explanation: '' }
                    ]
                });
            },
            removeQuestion(qIndex, qsIndex) {
                this.quizzes[qIndex].questions.splice(qsIndex, 1);
            },
            addOption(qIndex, qsIndex) {
                this.quizzes[qIndex].questions[qsIndex].options.push({ option_text: '', is_correct: false, explanation: '' });
            },
            removeOption(qIndex, qsIndex, optIndex) {
                this.quizzes[qIndex].questions[qsIndex].options.splice(optIndex, 1);
            },
            setCorrectOption(qIndex, qsIndex, optIndex) {
                this.quizzes[qIndex].questions[qsIndex].options.forEach((opt, idx) => {
                    opt.is_correct = (idx === optIndex);
                });
            }
        };
    }

    @php
        $existingQuizzes = $resource->quizzes()->with('questions.options')->get()->map(function($q) {
            return [
                'id' => $q->id,
                'title' => $q->title,
                'description' => $q->description,
                'questions' => $q->questions->map(function($quest) {
                    return [
                        'id' => $quest->id,
                        'question_text' => $quest->question_text,
                        'type' => $quest->type ?? 'single',
                        'points' => $quest->points,
                        'options' => $quest->options->map(function($opt) {
                            return [
                                'id' => $opt->id,
                                'option_text' => $opt->option_text,
                                'is_correct' => (bool)$opt->is_correct,
                                'explanation' => $opt->explanation ?? '',
                            ];
                        })
                    ];
                })
            ];
        });
    @endphp

    const initialQuizzes = {!! old('quizzes_data') ?: json_encode($existingQuizzes) !!};
</script>
@endpush
