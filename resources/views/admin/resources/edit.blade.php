@extends('layouts.admin')

@section('title', 'Éditer Ressource')

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
        <a href="{{ route('admin.resources.index') }}" class="text-gray-500 hover:text-gray-700">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Éditer : {{ $resource->title }}</h1>
            <p class="text-gray-600">Modifier le contenu pédagogique</p>
        </div>
    </div>

    <form action="{{ route('admin.resources.update', $resource) }}" method="POST" enctype="multipart/form-data"
        class="space-y-8">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Colonne Principale -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Informations de base -->
                <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-6">
                    <h2 class="text-lg font-semibold text-gray-900 border-b border-gray-100 pb-4">Informations
                        Principales</h2>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Titre de la ressource</label>
                            <input type="text" name="title" value="{{ old('title', $resource->title) }}" required
                                class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-3 transition duration-150 ease-in-out">
                            @error('title') <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Description courte</label>
                            <textarea name="description" rows="3" required
                                class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-3 transition duration-150 ease-in-out resize-y">{{ old('description', $resource->description) }}</textarea>
                            @error('description') <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Contenu complet
                            </label>
                            <div class="relative" x-data x-init="
                                const quill = new Quill($refs.editor, {
                                    theme: 'snow',
                                    placeholder: 'Écrivez votre contenu pédagogique ici...',
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
                                
                                // Set initial content (Important pour l'édition)
                                if ($refs.contentInput.value) {
                                    quill.root.innerHTML = $refs.contentInput.value;
                                }

                                // Sync content
                                quill.on('text-change', function() {
                                    $refs.contentInput.value = quill.root.innerHTML;
                                });
                            ">
                                <div x-ref="editor" class="bg-white"></div>
                                <input type="hidden" name="content" x-ref="contentInput"
                                    value="{{ old('content', $resource->content) }}">
                            </div>
                            @error('content') <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Critères Psychométriques (MBTI) - Remonté AVANT le ciblage -->
                <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-6">
                    <h2 class="text-lg font-semibold text-gray-900 border-b border-gray-100 pb-4">Critères
                        Psychométriques</h2>

                    @php
                    $mbtiGroups = [
                    'Analystes' => [
                    'color' => 'purple',
                    'types' => [
                    'INTJ' => 'Architecte imaginatif et stratège',
                    'INTP' => 'Inventeur innovant et curieux',
                    'ENTJ' => 'Commandant audacieux',
                    'ENTP' => 'Innovateur astucieux'
                    ]
                    ],
                    'Diplomates' => [
                    'color' => 'green',
                    'types' => [
                    'INFJ' => 'Idéaliste calme et inspirant',
                    'INFP' => 'Poète enthousiaste et altruiste',
                    'ENFJ' => 'Leader charismatique',
                    'ENFP' => 'Esprit libre et créatif'
                    ]
                    ],
                    'Sentinelles' => [
                    'color' => 'blue',
                    'types' => [
                    'ISTJ' => 'Factuel et fiable',
                    'ISFJ' => 'Protecteur dévoué',
                    'ESTJ' => 'Administrateur efficace',
                    'ESFJ' => 'Personnel attentionné'
                    ]
                    ],
                    'Explorateurs' => [
                    'color' => 'yellow',
                    'types' => [
                    'ISTP' => 'Expérimentateur audacieux',
                    'ISFP' => 'Artiste flexible',
                    'ESTP' => 'Entrepreneur intelligent',
                    'ESFP' => 'Artiste spontané'
                    ]
                    ]
                    ];
                    @endphp

                    <div class="space-y-4">
                        <div class="flex justify-end -mt-10 mb-6">
                            <button type="button" onclick="toggleAll('mbti_types[]', true)"
                                class="text-xs text-gray-500 hover:text-indigo-600 underline">Tout sélectionner</button>
                        </div>
                        @foreach($mbtiGroups as $groupName => $group)
                        <div>
                            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">{{ $groupName }}
                            </h3>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                @foreach($group['types'] as $type => $desc)
                                <label class="relative group cursor-pointer">
                                    <input type="checkbox" name="mbti_types[]" value="{{ $type }}" {{ in_array($type,
                                        $resource->mbti_types ?? []) ? 'checked' : '' }}
                                    class="peer sr-only">
                                    <div
                                        class="p-2 rounded-lg border border-gray-200 hover:border-{{ $group['color'] }}-500 hover:bg-{{ $group['color'] }}-50 peer-checked:bg-{{ $group['color'] }}-100 peer-checked:border-{{ $group['color'] }}-500 transition-all text-center">
                                        <span
                                            class="block font-bold text-sm text-gray-700 peer-checked:text-{{ $group['color'] }}-800">{{
                                            $type }}</span>
                                    </div>
                                    <div
                                        class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 hidden group-hover:block w-48 p-2 bg-gray-900 text-white text-xs rounded shadow-lg z-10 text-center">
                                        {{ $desc }}
                                        <div
                                            class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-900">
                                        </div>
                                    </div>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Ciblage Avancé (Dynamique) -->
                <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-6">
                    <h2 class="text-lg font-semibold text-gray-900 border-b border-gray-100 pb-4">Ciblage Avancé <span
                            class="text-sm font-normal text-gray-500 ml-2">(Optionnel)</span></h2>

                    @php
                    $targeting = $resource->targeting ?? [];
                    $selectedEducation = $targeting['education_levels'] ?? [];
                    $selectedSit = $targeting['situations'] ?? [];
                    $selectedInterests = $targeting['interests'] ?? [];
                    $selectedCountries = $targeting['countries'] ?? [];
                    @endphp

                    @if(empty($targetingOptions['education_levels']) && empty($targetingOptions['situations']) &&
                    empty($targetingOptions['countries']) && empty($targetingOptions['interests']))
                    <div class="text-sm text-gray-500 italic p-4 bg-gray-50 rounded-lg text-center">
                        Pas encore de données utilisateurs suffisantes pour le ciblage dynamique.
                    </div>
                    @else
                    <div class="flex justify-between items-center mb-4">
                        <button type="button" onclick="selectAllGlobal()"
                            class="text-indigo-600 text-xs font-semibold hover:text-indigo-800 underline">
                            Tout sélectionner (Public large)
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Niveau d'études -->
                        @if(!empty($targetingOptions['education_levels']))
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <label class="block text-sm font-medium text-gray-700">Niveau d'études</label>
                                <button type="button" onclick="toggleAll('targeting[education_levels][]', true)"
                                    class="text-xs text-gray-500 hover:text-indigo-600 underline">Tous</button>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                @foreach($targetingOptions['education_levels'] as $key => $label)
                                <label class="cursor-pointer">
                                    <input type="checkbox" name="targeting[education_levels][]" value="{{ $key }}" {{
                                        in_array($key, $selectedEducation) ? 'checked' : '' }} class="peer sr-only">
                                    <span
                                        class="inline-block px-3 py-1.5 text-xs text-gray-600 border border-gray-200 rounded-full hover:bg-gray-50 peer-checked:bg-indigo-100 peer-checked:border-indigo-400 peer-checked:text-indigo-800 transition select-none">
                                        {{ $label }}
                                    </span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <!-- Situation -->
                        @if(!empty($targetingOptions['situations']))
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <label class="block text-sm font-medium text-gray-700">Situation</label>
                                <button type="button" onclick="toggleAll('targeting[situations][]', true)"
                                    class="text-xs text-gray-500 hover:text-indigo-600 underline">Tous</button>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                @foreach($targetingOptions['situations'] as $key => $label)
                                <label class="cursor-pointer">
                                    <input type="checkbox" name="targeting[situations][]" value="{{ $key }}" {{
                                        in_array($key, $selectedSit) ? 'checked' : '' }} class="peer sr-only">
                                    <span
                                        class="inline-block px-3 py-1.5 text-xs text-gray-600 border border-gray-200 rounded-full hover:bg-purple-100 hover:text-purple-800 peer-checked:bg-purple-100 peer-checked:border-purple-400 peer-checked:text-purple-800 transition select-none">
                                        {{ $label }}
                                    </span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Pays & Intérêts -->
                    <div class="grid grid-cols-1 gap-6 pt-4 border-t border-gray-50 mt-4">
                        <!-- Pays (Nouveau Design) -->
                        @if(!empty($targetingOptions['countries']))
                        <div class="space-y-2">
                            <div class="flex justify-between items-center">
                                <label class="block text-sm font-medium text-gray-700">Pays cibles <span
                                        class="text-xs text-gray-500 font-normal">(Basé sur les jeunes
                                        inscrits)</span></label>
                                <button type="button" onclick="toggleAll('targeting[countries][]', true)"
                                    class="text-xs text-gray-500 hover:text-indigo-600 underline">Tous</button>
                            </div>
                            <div class="flex flex-wrap gap-3">
                                @foreach($targetingOptions['countries'] as $label => $value)
                                <label class="cursor-pointer group">
                                    <input type="checkbox" name="targeting[countries][]" value="{{ $value }}" {{
                                        in_array($value, $selectedCountries) ? 'checked' : '' }} class="peer sr-only">
                                    <div
                                        class="flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-200 bg-white hover:border-indigo-300 peer-checked:bg-indigo-50 peer-checked:border-indigo-500 transition shadow-sm">
                                        <span class="text-lg">🌍</span>
                                        <span class="text-sm font-medium text-gray-700 peer-checked:text-indigo-900">{{
                                            $label }}</span>
                                    </div>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <!-- Centres d'intérêt -->
                        @if(!empty($targetingOptions['interests']))
                        <div x-data="{
                                selected: {{ Js::from($selectedInterests) }},
                                options: {{ Js::from($targetingOptions['interests']) }},
                                toggle(option) {
                                    if (this.selected.includes(option)) {
                                        this.selected = this.selected.filter(item => item !== option);
                                    } else {
                                        this.selected.push(option);
                                    }
                                },
                                selectAll() {
                                    this.selected = [...this.options];
                                }
                            }" @select-all-interests.window="selectAll()" class="space-y-2 pt-2">
                            <div class="flex justify-between items-center">
                                <label class="block text-sm font-medium text-gray-700">Intérêts principaux</label>
                                <button type="button" @click="selectAll()"
                                    class="text-xs text-gray-500 hover:text-indigo-600 underline">Tous</button>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <template x-for="option in options" :key="option">
                                    <button type="button" @click="toggle(option)"
                                        :class="selected.includes(option) ? 'bg-orange-50 text-orange-700 border-orange-200' : 'bg-white text-gray-500 border-gray-200 hover:bg-gray-50'"
                                        class="px-2.5 py-1 text-xs rounded-lg border transition">
                                        <span x-text="option"></span>
                                    </button>
                                </template>
                            </div>
                            <template x-for="item in selected" :key="item">
                                <input type="hidden" name="targeting[interests][]" :value="item">
                            </template>
                        </div>
                        @endif
                    </div>
                    @endif

                    <!-- Tags (Toujours dispo car manuel) -->
                    <div x-data="{ 
                        tags: {{ Js::from($resource->tags ?? []) }},
                        newTag: '',
                        addTag() {
                            if (this.newTag.trim() !== '' && !this.tags.includes(this.newTag.trim())) {
                                this.tags.push(this.newTag.trim());
                            }
                            this.newTag = '';
                        },
                        removeTag(index) {
                            this.tags.splice(index, 1);
                        }
                    }" class="space-y-2 pt-4 border-t border-gray-50 mt-4">
                        <label class="block text-sm font-medium text-gray-700">Tags mots-clés</label>
                        <div
                            class="flex flex-wrap items-center gap-2 p-2 border border-gray-200 rounded-lg focus-within:ring-1 focus-within:ring-indigo-500 bg-white min-h-[42px]">
                            <template x-for="(tag, index) in tags" :key="index">
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                    <span x-text="tag"></span>
                                    <button type="button" @click="removeTag(index)"
                                        class="ml-1 text-gray-400 hover:text-gray-600">×</button>
                                </span>
                            </template>
                            <input type="text" x-model="newTag" @keydown.enter.prevent="addTag()"
                                @keydown.comma.prevent="addTag()" @blur="addTag()" placeholder="Tags..."
                                class="flex-1 border-none focus:ring-0 p-0 text-sm bg-transparent !outline-none h-6 placeholder-gray-400">
                        </div>
                        <input type="hidden" name="tags" :value="tags.join(',')">
                    </div>
                </div>
            </div>

            <!-- Colonne Latérale -->
            <div class="space-y-6">
                <!-- Configuration & Prix -->
                <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-6 sticky top-24">
                    <h2 class="text-sm font-bold text-gray-900 border-b border-gray-100 pb-3 uppercase tracking-wide">
                        Paramètres</h2>

                    <div class="space-y-5">
                        <!-- Type -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase mb-2">Type</label>
                            <select name="type"
                                class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5">
                                @foreach(['article', 'video', 'tool', 'exercise', 'template', 'script', 'advertisement']
                                as $type)
                                <option value="{{ $type }}" {{ old('type', $resource->type) === $type ? 'selected' : ''
                                    }}>
                                    {{ $type === 'advertisement' ? 'Publicité / Partenariat' : ucfirst($type) }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Prix -->
                        <div x-data="{ isPremium: '{{ old('is_premium', $resource->is_premium) ? '1' : '0' }}' }"
                            class="space-y-3">
                            <label class="block text-xs font-semibold text-gray-500 uppercase">Tarification</label>
                            <div class="flex gap-2">
                                <label class="flex-1 cursor-pointer">
                                    <input type="radio" name="is_premium" value="0" x-model="isPremium"
                                        @click="document.getElementById('editPriceInput').value = 0"
                                        class="peer sr-only">
                                    <div
                                        class="py-2 px-3 text-center rounded border border-gray-200 bg-white hover:bg-gray-50 peer-checked:bg-green-50 peer-checked:border-green-500 peer-checked:text-green-700 transition">
                                        <span class="text-sm font-medium">Gratuit</span>
                                    </div>
                                </label>
                                <label class="flex-1 cursor-pointer">
                                    <input type="radio" name="is_premium" value="1" x-model="isPremium"
                                        class="peer sr-only">
                                    <div
                                        class="py-2 px-3 text-center rounded border border-gray-200 bg-white hover:bg-gray-50 peer-checked:bg-purple-50 peer-checked:border-purple-500 peer-checked:text-purple-700 transition">
                                        <span class="text-sm font-medium">Payant</span>
                                    </div>
                                </label>
                            </div>

                            <div x-show="isPremium === '1'" x-transition>
                                <div class="relative mt-2">
                                    <input id="editPriceInput" type="number" name="price"
                                        value="{{ old('price', $resource->price) }}" :required="isPremium === '1'"
                                        min="0" step="100"
                                        class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5 pr-12"
                                        placeholder="Prix">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <span class="text-gray-500 text-xs">FCFA</span>
                                    </div>
                                </div>
                                @error('price') <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Fichiers -->
                <div class="space-y-5 pt-5 border-t border-gray-100" x-data="{
                            coverPreview: null,
                            fileName: null,
                            isUploading: false,
                            uploadSuccess: false,
                            uploadError: null,
                            handleCoverChange(event) {
                                const file = event.target.files[0];
                                if (file) {
                                    this.coverPreview = URL.createObjectURL(file);
                                }
                            },
                            handleFileChange(event) {
                                const file = event.target.files[0];
                                if (file) {
                                     // Validation Type (Pas de vidéo)
                                    if (file.type.startsWith('video/')) {
                                        this.uploadError = 'Les vidéos ne sont pas autorisées ici.';
                                        this.fileName = null;
                                        this.uploadSuccess = false;
                                        event.target.value = ''; // Reset input
                                        return;
                                    }
                                    
                                     // Validation Taille (ex: 20MB)
                                    if (file.size > 20 * 1024 * 1024) {
                                        this.uploadError = 'Le fichier est trop volumineux (Max 20MB).';
                                        this.fileName = null;
                                        this.uploadSuccess = false;
                                        event.target.value = ''; // Reset input
                                        return;
                                    }

                                    this.uploadError = null;
                                    this.isUploading = true;
                                    this.uploadSuccess = false;
                                    
                                    // Simulation d'upload
                                    setTimeout(() => {
                                        this.isUploading = false;
                                        this.uploadSuccess = true;
                                        this.fileName = file.name;
                                    }, 1500);
                                }
                            },
                            removeFile() {
                                this.fileName = null;
                                this.uploadSuccess = false;
                                this.uploadError = null;
                                document.getElementById('file_input').value = '';
                            }
                        }">
                    <!-- Image -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-2">Couverture</label>
                        <label for="preview_image_input"
                            class="relative flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 transition relative overflow-hidden group">
                            <template x-if="!coverPreview">
                                <!-- Affichage par défaut ou image existante -->
                                <div class="w-full h-full flex flex-col items-center justify-center">
                                    @if($resource->preview_image_path)
                                    <img src="{{ Storage::url($resource->preview_image_path) }}" alt="Preview"
                                        class="absolute inset-0 w-full h-full object-cover">
                                    <div
                                        class="absolute bottom-0 inset-x-0 bg-black/50 p-1 text-white text-center text-[10px] opacity-0 group-hover:opacity-100 transition">
                                        Changer
                                    </div>
                                    @else
                                    <div class="flex flex-col items-center justify-center pt-2 pb-3">
                                        <svg class="w-6 h-6 mb-1 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                            </path>
                                        </svg>
                                        <p class="text-[10px] text-gray-500">JPG, PNG</p>
                                    </div>
                                    @endif
                                </div>
                            </template>

                            <template x-if="coverPreview">
                                <img :src="coverPreview" class="w-full h-full object-cover">
                            </template>

                            <input id="preview_image_input" type="file" name="preview_image" accept="image/*"
                                class="hidden" @change="handleCoverChange" />
                        </label>
                        <p class="text-[10px] text-gray-400 mt-1">Format: JPG, PNG. Taille max : 5 Mo.</p>
                        @error('preview_image') <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- PJ -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-2">Fichier
                            (Optionnel)</label>

                        <div class="relative">
                            <label for="file_input" x-show="!uploadSuccess"
                                class="flex flex-col items-center justify-center w-full h-20 border border-gray-200 rounded-lg cursor-pointer bg-white hover:bg-gray-50 transition relative overflow-hidden"
                                :class="uploadError ? 'border-red-300 bg-red-50' : ''">

                                <!-- État Initial / Existant -->
                                <div x-show="!isUploading && !uploadSuccess" class="flex flex-col items-center w-full">
                                    @if($resource->file_path)
                                    <div class="flex items-center gap-2 text-green-600 mb-1" x-show="!uploadError">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span class="text-xs font-bold">Fichier actuel (conservé)</span>
                                    </div>
                                    <span class="text-[10px] text-gray-500" x-show="!uploadError">Cliquez pour
                                        remplacer</span>
                                    @else
                                    <span class="text-xs text-gray-600 font-medium"
                                        x-text="uploadError ? 'Réessayer' : 'Choisir un fichier...'"></span>
                                    <span x-show="!uploadError" class="text-[9px] text-gray-400 mt-1">Docs, Images,
                                        Zip</span>
                                    @endif
                                    <span x-show="uploadError" class="text-[9px] text-red-500 mt-1"
                                        x-text="uploadError"></span>
                                </div>

                                <!-- État "Upload" (Loader) -->
                                <div x-show="isUploading" class="flex flex-col items-center text-indigo-600">
                                    <svg class="animate-spin h-6 w-6 mb-1" xmlns="http://www.w3.org/2000/svg"
                                        fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    <span class="text-[10px] font-semibold animate-pulse">Traitement...</span>
                                </div>
                            </label>

                            <input id="file_input" type="file" name="file"
                                accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.zip,.rar,.7z,.jpg,.jpeg,.png,.webp"
                                class="hidden" @change="handleFileChange" />

                            <div x-show="uploadSuccess"
                                class="flex items-center justify-between w-full h-20 border border-green-200 bg-green-50 rounded-lg px-4 transition">
                                <div class="flex items-center gap-3 overflow-hidden">
                                    <div class="bg-white p-2 rounded-lg border border-green-100 flex-shrink-0">
                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                            </path>
                                        </svg>
                                    </div>
                                    <div class="flex flex-col min-w-0">
                                        <span class="text-xs font-bold text-gray-900 truncate" x-text="fileName"></span>
                                        <span class="text-[10px] text-green-600 font-medium">Prêt à être publié</span>
                                    </div>
                                </div>
                                <button type="button" @click="removeFile"
                                    class="p-2 text-gray-400 hover:text-red-500 transition rounded-full hover:bg-white">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                        </path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <p class="text-[10px] text-gray-400 mt-1">Formats acceptés : PDF, Word, Excel, PowerPoint, ZIP,
                            Images. Taille max : 20 Mo.</p>
                        @error('file') <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Boutons -->
                <div class="pt-4 border-t border-gray-100 space-y-3">
                    <button type="submit"
                        class="w-full bg-indigo-600 text-white font-bold py-3 px-4 rounded-xl hover:bg-indigo-700 transition shadow-lg shadow-indigo-200">
                        Mettre à jour
                    </button>

                    <button type="button"
                        onclick="if(confirm('Supprimer cette ressource ?')) document.getElementById('delete-form').submit();"
                        class="w-full text-red-600 text-xs font-semibold hover:text-red-800 transition text-center underline">
                        Supprimer la ressource
                    </button>
                </div>
            </div>
        </div>
</div>
</form>

<form id="delete-form" action="{{ route('admin.resources.destroy', $resource) }}" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>
</div>
@endsection

@push('scripts')
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script>
    function toggleAll(name, checked) {
        document.getElementsByName(name).forEach(el => {
            el.checked = checked;
            el.dispatchEvent(new Event('change'));
        });
    }

    function selectAllGlobal() {
        // MBTI
        toggleAll('mbti_types[]', true);
        // Education
        toggleAll('targeting[education_levels][]', true);
        // Situations
        toggleAll('targeting[situations][]', true);
        // Countries
        toggleAll('targeting[countries][]', true);

        // Interests (Alpine)
        window.dispatchEvent(new CustomEvent('select-all-interests'));
    }
</script>
@endpush