@php
if (old('quizzes_data')) {
    $initialQuizzesJson = json_encode(json_decode(old('quizzes_data')));
} elseif (isset($resource) && $resource) {
    $initialQuizzesJson = json_encode($resource->quizzes->load('questions.options')->map(function($q) {
        return [
            'id' => $q->id,
            'title' => $q->title,
            'description' => $q->description,
            'questions' => $q->questions->map(function($question) {
                return [
                    'id' => $question->id,
                    'question_text' => $question->question_text,
                    'type' => $question->type ?? 'single',
                    'points' => $question->points ?? 1,
                    'options' => $question->options->map(function($opt) {
                        return [
                            'id' => $opt->id,
                            'option_text' => $opt->option_text,
                            'is_correct' => (bool)$opt->is_correct,
                            'explanation' => $opt->explanation
                        ];
                    })
                ];
            })
        ];
    }));
} else {
    $initialQuizzesJson = 'null';
}
@endphp

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/quill@1.3.6/dist/quill.snow.css" rel="stylesheet" nonce="{{ request()->attributes->get('csp_nonce') }}" integrity="sha256-iS4plDGVXprjiK4lf3ICTudq8tUqepeoaPcPvlDxYUQ=" crossorigin="anonymous">
<style nonce="{{ request()->attributes->get('csp_nonce') }}">
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

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Colonne Principale (Informations + Contenu + Quiz) -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Informations de base -->
        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-900 border-b border-gray-100 pb-4">Informations Principales</h2>

            <div class="space-y-4">
                <div>
                    <label for="resource_title" class="block text-sm font-semibold text-gray-700 mb-2">Titre de la ressource <span class="text-red-500">*</span></label>
                    <input type="text" id="resource_title" name="title" value="{{ old('title', $resource?->title) }}" required
                        placeholder="Ex: Guide d'intégration et méthodologie de travail"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-organization-500 focus:border-organization-500 block w-full p-3 transition">
                    @error('title') <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="resource_description" class="block text-sm font-semibold text-gray-700 mb-2">Description courte <span class="text-red-500">*</span></label>
                    <textarea id="resource_description" name="description" rows="3" required
                        placeholder="Un résumé clair et synthétique de la ressource..."
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-organization-500 focus:border-organization-500 block w-full p-3 transition resize-y">{{ old('description', $resource?->description) }}</textarea>
                    @error('description') <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="editor-container" class="block text-sm font-semibold text-gray-700 mb-2">Contenu textuel / pédagogique</label>
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
                        <div id="editor-container" x-ref="editor" class="bg-white"></div>
                        <input type="hidden" name="content" x-ref="contentInput" value="{{ old('content', $resource?->content) }}">
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
                    <p class="text-xs text-gray-500 mt-1">Ajoutez un ou plusieurs quiz pour tester la compréhension de vos jeunes.</p>
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
                        <button type="button" @click="removeQuiz(qIndex)" aria-label="Supprimer ce quiz" class="absolute top-4 right-4 text-gray-400 hover:text-red-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>

                        <div class="space-y-4 pr-8">
                            <div>
                                <label :for="'quiz_title_' + qIndex" class="block text-xs font-semibold text-gray-700 mb-1">Nom du Quiz</label>
                                <input type="text" :id="'quiz_title_' + qIndex" x-model="quiz.title" placeholder="Ex: Quiz de validation des acquis" aria-label="Nom du Quiz" class="bg-white border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-organization-500 focus:border-organization-500 block w-full p-2.5">
                            </div>
                            <div>
                                <label :for="'quiz_description_' + qIndex" class="block text-xs font-semibold text-gray-700 mb-1">Description courte (Optionnelle)</label>
                                <input type="text" :id="'quiz_description_' + qIndex" x-model="quiz.description" placeholder="Objectif de ce quiz..." aria-label="Description courte du quiz" class="bg-white border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-organization-500 focus:border-organization-500 block w-full p-2.5">
                            </div>

                            <!-- Questions -->
                            <div class="mt-4 border-t border-organization-100 pt-4">
                                <div class="flex justify-between items-center mb-3">
                                    <h3 class="text-sm font-bold text-gray-700">Questions</h3>
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
                                            <button type="button" @click="removeQuestion(qIndex, qsIndex)" aria-label="Supprimer cette question" class="absolute top-3 right-3 text-gray-400 hover:text-red-500">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            </button>

                                            <div class="mb-3 pr-8 flex gap-2 items-start">
                                                <div class="flex-1">
                                                    <label :for="'q_text_' + qIndex + '_' + qsIndex" class="sr-only">Intitulé de la question</label>
                                                    <input type="text" :id="'q_text_' + qIndex + '_' + qsIndex" x-model="question.question_text" placeholder="Posez votre question ici..." aria-label="Posez votre question ici" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-organization-500 focus:border-organization-500 block w-full p-2">
                                                </div>
                                                <div class="w-20">
                                                    <label :for="'q_points_' + qIndex + '_' + qsIndex" class="sr-only">Points</label>
                                                    <input type="number" :id="'q_points_' + qIndex + '_' + qsIndex" x-model="question.points" min="1" placeholder="Pts" aria-label="Points de la question" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-organization-500 focus:border-organization-500 block w-full p-2">
                                                </div>
                                            </div>

                                            <!-- Options -->
                                            <div class="pl-4 space-y-3 border-l-2 border-gray-100">
                                                <template x-for="(option, optIndex) in question.options" :key="optIndex">
                                                    <div class="flex flex-col gap-1">
                                                        <div class="flex items-center gap-2">
                                                            <template x-if="question.type === 'multiple'">
                                                                <div>
                                                                    <label :for="'opt_cb_' + qIndex + '_' + qsIndex + '_' + optIndex" class="sr-only">Option correcte</label>
                                                                    <input type="checkbox" :id="'opt_cb_' + qIndex + '_' + qsIndex + '_' + optIndex" x-model="option.is_correct" aria-label="Option correcte" class="w-4 h-4 text-organization-600 bg-gray-100 border-gray-300 rounded focus:ring-organization-500 cursor-pointer">
                                                                </div>
                                                            </template>
                                                            <template x-if="question.type === 'single' || !question.type">
                                                                <div>
                                                                    <label :for="'opt_rad_' + qIndex + '_' + qsIndex + '_' + optIndex" class="sr-only">Option correcte</label>
                                                                    <input type="radio" :id="'opt_rad_' + qIndex + '_' + qsIndex + '_' + optIndex" :name="'correct_'+qIndex+'_'+qsIndex" :checked="option.is_correct" @change="setCorrectOption(qIndex, qsIndex, optIndex)" aria-label="Option correcte" class="w-4 h-4 text-organization-600 bg-gray-100 border-gray-300 focus:ring-organization-500 cursor-pointer">
                                                                </div>
                                                            </template>
                                                            <div class="flex-1">
                                                                <label :for="'opt_txt_' + qIndex + '_' + qsIndex + '_' + optIndex" class="sr-only">Option de réponse</label>
                                                                <input type="text" :id="'opt_txt_' + qIndex + '_' + qsIndex + '_' + optIndex" x-model="option.option_text" placeholder="Option de réponse" aria-label="Option de réponse" class="w-full bg-transparent border-b border-gray-200 focus:border-organization-500 focus:ring-0 text-sm px-1 py-1">
                                                            </div>
                                                            <button type="button" @click="removeOption(qIndex, qsIndex, optIndex)" aria-label="Supprimer option" class="text-gray-300 hover:text-red-500 p-1">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                            </button>
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
            </div>
        </div>
    </div>

    <!-- Colonne Latérale (Paramètres, Média & Publication) -->
    <div class="space-y-6">
        <!-- Paramètres de Type et Fichier -->
        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4 shadow-sm">
            <h2 class="text-md font-semibold text-gray-900 border-b border-gray-100 pb-3">Format & Média</h2>

            <div>
                <label for="resource_type" class="block text-sm font-semibold text-gray-700 mb-2">Type de Ressource <span class="text-red-500">*</span></label>
                <select id="resource_type" name="type" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-organization-500 focus:border-organization-500 block w-full p-2.5">
                    <option value="article" {{ old('type', $resource?->type) === 'article' ? 'selected' : '' }}>📄 Article</option>
                    <option value="video" {{ old('type', $resource?->type) === 'video' ? 'selected' : '' }}>🎥 Vidéo</option>
                    <option value="tool" {{ old('type', $resource?->type) === 'tool' ? 'selected' : '' }}>🔧 Outil</option>
                    <option value="exercise" {{ old('type', $resource?->type) === 'exercise' ? 'selected' : '' }}>📝 Exercice</option>
                    <option value="template" {{ old('type', $resource?->type) === 'template' ? 'selected' : '' }}>📋 Modèle</option>
                    <option value="script" {{ old('type', $resource?->type) === 'script' ? 'selected' : '' }}>📜 Script</option>
                    <option value="book" {{ old('type', $resource?->type) === 'book' ? 'selected' : '' }}>📚 Livre / Support PDF</option>
                    <option value="podcast" {{ old('type', $resource?->type) === 'podcast' ? 'selected' : '' }}>🎧 Podcast</option>
                    <option value="webinar" {{ old('type', $resource?->type) === 'webinar' ? 'selected' : '' }}>📺 Webinaire</option>
                    <option value="guide" {{ old('type', $resource?->type) === 'guide' ? 'selected' : '' }}>🧭 Guide</option>
                    <option value="case_study" {{ old('type', $resource?->type) === 'case_study' ? 'selected' : '' }}>📊 Étude de cas</option>
                    <option value="course" {{ old('type', $resource?->type) === 'course' ? 'selected' : '' }}>🎓 Cours / Formation</option>
                </select>
                @error('type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="resource_preview_image" class="block text-sm font-semibold text-gray-700 mb-2">Image de couverture</label>
                @if(isset($resource) && $resource && $resource->preview_image_path)
                <div class="mb-2 w-full aspect-video rounded-lg overflow-hidden bg-gray-100">
                    <img src="{{ Storage::url($resource->preview_image_path) }}" class="w-full h-full object-cover" alt="Aperçu actuel de la couverture">
                </div>
                @endif
                <input type="file" id="resource_preview_image" name="preview_image" accept="image/*"
                    class="block w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-organization-50 file:text-organization-700 hover:file:bg-organization-100 cursor-pointer">
                <p class="text-[10px] text-gray-400 mt-1">PNG, JPG ou WebP (Max 5 Mo)</p>
                @error('preview_image') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="resource_file" class="block text-sm font-semibold text-gray-700 mb-2">Fichier joint téléchargeable</label>
                @if(isset($resource) && $resource && $resource->file_path)
                <div class="mb-2 p-2 bg-gray-50 rounded border border-gray-200 text-xs text-gray-600 flex items-center justify-between">
                    <span class="truncate">Fichier actuel : {{ basename($resource->file_path) }}</span>
                </div>
                @endif
                <input type="file" id="resource_file" name="file"
                    class="block w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-organization-50 file:text-organization-700 hover:file:bg-organization-100 cursor-pointer">
                <p class="text-[10px] text-gray-400 mt-1">PDF, ZIP, DOCX, etc. (Max 20 Mo)</p>
                @error('file') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="resource_tags" class="block text-sm font-semibold text-gray-700 mb-2">Mots-clés / Tags</label>
                <input type="text" id="resource_tags" name="tags" value="{{ old('tags', (isset($resource) && $resource && $resource->tags) ? implode(', ', $resource->tags) : '') }}"
                    placeholder="Ex: orientation, cv, entretien, stage"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-organization-500 focus:border-organization-500 block w-full p-2.5">
                <p class="text-[10px] text-gray-400 mt-1">Séparez par des virgules</p>
            </div>
        </div>

        <!-- Tarification & Disponibilité -->
        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4 shadow-sm" x-data="{ isPremium: '{{ old('is_premium', isset($resource) && $resource ? ($resource->is_premium ? '1' : '0') : '0') }}' }">
            <h2 class="text-md font-semibold text-gray-900 border-b border-gray-100 pb-3">Accès</h2>

            <div class="space-y-3">
                <div class="flex items-center gap-3 p-3 rounded-lg border transition" :class="isPremium === '0' ? 'bg-green-50 border-green-300' : 'bg-gray-50 border-gray-200'">
                    <input type="radio" id="is_premium_free" name="is_premium" value="0" x-model="isPremium" class="text-organization-600 focus:ring-organization-500 cursor-pointer">
                    <label for="is_premium_free" class="cursor-pointer flex-1">
                        <span class="text-sm font-bold text-gray-900">Gratuit</span>
                        <p class="text-xs text-gray-500">Disponible immédiatement pour tous vos jeunes</p>
                    </label>
                </div>

                <div class="flex items-center gap-3 p-3 rounded-lg border transition" :class="isPremium === '1' ? 'bg-organization-50 border-organization-300' : 'bg-gray-50 border-gray-200'">
                    <input type="radio" id="is_premium_paid" name="is_premium" value="1" x-model="isPremium" class="text-organization-600 focus:ring-organization-500 cursor-pointer">
                    <label for="is_premium_paid" class="cursor-pointer flex-1">
                        <span class="text-sm font-bold text-gray-900">Payant (Premium)</span>
                        <p class="text-xs text-gray-500">Déblocable via des crédits ou offert par votre structure</p>
                    </label>
                </div>
            </div>

            <div x-show="isPremium === '1'" x-cloak class="pt-2">
                <label for="resource_price" class="block text-sm font-semibold text-gray-700 mb-1">Prix en FCFA <span class="text-red-500">*</span></label>
                <input type="number" id="resource_price" name="price" value="{{ old('price', isset($resource) && $resource ? $resource->price : 500) }}" min="200" step="50"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-organization-500 focus:border-organization-500 block w-full p-2.5">
                <p class="text-[10px] text-gray-400 mt-1">Minimum 200 FCFA</p>
            </div>
        </div>

        <!-- Soumission -->
        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-3 shadow-sm">
            <button type="submit"
                class="w-full bg-organization-600 hover:bg-organization-700 text-white font-bold py-3 px-4 rounded-xl shadow-lg shadow-organization-600/20 transition flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                {{ isset($resource) && $resource ? 'Mettre à jour la Ressource' : 'Publier la Ressource' }}
            </button>
            <a href="{{ route('organization.resources.index', ['tab' => 'internal']) }}"
                class="w-full block text-center text-sm text-gray-500 hover:text-gray-700 py-2">
                Annuler
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script nonce="{{ request()->attributes->get('csp_nonce') }}" src="https://cdn.jsdelivr.net/npm/quill@1.3.6/dist/quill.js" integrity="sha256-pNpwzXG1oOIk6VhlgpqDVqk5B8fUfrtrI8uAFMb/nEg=" crossorigin="anonymous"></script>
<script nonce="{{ request()->attributes->get('csp_nonce') }}">
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
                if (confirm('Voulez-vous vraiment supprimer ce quiz ?')) {
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

    const initialQuizzes = {!! $initialQuizzesJson !!};
</script>
@endpush
