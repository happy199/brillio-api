@extends('layouts.admin')

@section('title', 'Nouvelle Ressource')

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
                <h1 class="text-2xl font-bold text-gray-900">Nouvelle Ressource</h1>
                <p class="text-gray-600">Ajouter du contenu pédagogique</p>
            </div>
        </div>

        <form action="{{ route('admin.resources.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf

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
                                <input type="text" name="title" value="{{ old('title') }}" required
                                    placeholder="Ex: Les 10 clés de la confiance en soi"
                                    class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-3 transition duration-150 ease-in-out">
                                @error('title') <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Description courte</label>
                                <textarea name="description" rows="3" required
                                    placeholder="Un résumé accrocheur pour donner envie de consulter la ressource..."
                                    class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-3 transition duration-150 ease-in-out resize-y">{{ old('description') }}</textarea>
                                @error('description') <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Contenu complet
                                    <span class="text-xs font-normal text-gray-500 ml-1">(Format HTML accepté)</span>
                                </label>
                                <textarea name="content" rows="12" placeholder="<p>Écrivez votre contenu ici...</p>"
                                    class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-3 font-mono transition duration-150 ease-in-out">{{ old('content') }}</textarea>
                                @error('content') <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Cibles & Métadonnées -->
                    <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-6">
                        <h2 class="text-lg font-semibold text-gray-900 border-b border-gray-100 pb-4">Ciblage</h2>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-4">Types MBTI ciblés
                                (Optionnel)</label>

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
                                @foreach($mbtiGroups as $groupName => $group)
                                    <div>
                                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">
                                            {{ $groupName }}</h3>
                                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                            @foreach($group['types'] as $type => $desc)
                                                <label class="relative group cursor-pointer">
                                                    <input type="checkbox" name="mbti_types[]" value="{{ $type }}"
                                                        class="peer sr-only">
                                                    <div
                                                        class="p-3 rounded-lg border border-gray-200 hover:border-{{ $group['color'] }}-500 hover:bg-{{ $group['color'] }}-50 peer-checked:bg-{{ $group['color'] }}-100 peer-checked:border-{{ $group['color'] }}-500 transition-all text-center">
                                                        <span
                                                            class="block font-bold text-gray-700 peer-checked:text-{{ $group['color'] }}-800">{{ $type }}</span>
                                                    </div>
                                                    <!-- Tooltip -->
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

                        <div x-data="{ 
                                tags: [],
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
                            }">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tags</label>
                            <div
                                class="flex flex-wrap items-center gap-2 p-2 border border-gray-200 rounded-lg focus-within:ring-2 focus-within:ring-indigo-500 focus-within:border-indigo-500 bg-white">
                                <template x-for="(tag, index) in tags" :key="index">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800">
                                        <span x-text="tag"></span>
                                        <button type="button" @click="removeTag(index)"
                                            class="ml-1.5 inline-flex items-center justify-center text-indigo-400 hover:text-indigo-600 focus:outline-none">
                                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </span>
                                </template>
                                <input type="text" x-model="newTag" @keydown.enter.prevent="addTag()"
                                    @keydown.comma.prevent="addTag()" @blur="addTag()" placeholder="Ajouter un tag..."
                                    class="flex-1 border-none focus:ring-0 p-1 text-sm min-w-[100px]">
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Appuyez sur Entrée ou Virgule pour ajouter.</p>
                            <!-- Input caché pour envoyer au serveur -->
                            <input type="hidden" name="tags" :value="tags.join(',')">
                        </div>
                    </div>
                </div>

                <!-- Colonne Latérale -->
                <div class="space-y-6">
                    <!-- Configuration -->
                    <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-6">
                        <h2 class="text-lg font-semibold text-gray-900 border-b border-gray-100 pb-4">Configuration</h2>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Type de ressource</label>
                                <select name="type"
                                    class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5">
                                    <option value="article" selected>Article</option>
                                    <option value="video">Vidéo</option>
                                    <option value="tool">Outil Pratique</option>
                                    <option value="exercise">Exercice</option>
                                    <option value="template">Modèle / Template</option>
                                    <option value="script">Script</option>
                                    <option value="advertisement">Publicité / Partenariat</option>
                                </select>
                            </div>

                            <div x-data="{ isPremium: '0' }" class="pt-4 border-t border-gray-100">
                                <label class="block text-sm font-semibold text-gray-700 mb-3">Accès à la ressource</label>

                                <div class="grid grid-cols-2 gap-3 mb-4">
                                    <label class="cursor-pointer">
                                        <input type="radio" name="is_premium" value="0" x-model="isPremium"
                                            @click="document.getElementById('priceInput').value = 0" class="peer sr-only">
                                        <div
                                            class="p-3 text-center rounded-lg border border-gray-200 bg-white hover:bg-gray-50 peer-checked:bg-green-50 peer-checked:border-green-500 peer-checked:text-green-700 transition">
                                            <span class="block text-sm font-bold">Gratuit</span>
                                        </div>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input type="radio" name="is_premium" value="1" x-model="isPremium"
                                            class="peer sr-only">
                                        <div
                                            class="p-3 text-center rounded-lg border border-gray-200 bg-white hover:bg-gray-50 peer-checked:bg-purple-50 peer-checked:border-purple-500 peer-checked:text-purple-700 transition">
                                            <span class="block text-sm font-bold">Premium</span>
                                        </div>
                                    </label>
                                </div>

                                <div x-show="isPremium === '1'" x-transition class="space-y-2">
                                    <label class="block text-sm font-semibold text-gray-700">Prix (FCFA) <span
                                            class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <input id="priceInput" type="number" name="price" value="{{ old('price', 0) }}"
                                            :required="isPremium === '1'" min="0" step="100"
                                            class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5 pr-12 text-right">
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">FCFA</span>
                                        </div>
                                    </div>
                                    <p class="text-xs text-gray-500 text-right">Le prix doit être supérieur à 0 pour
                                        premium.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Fichiers -->
                    <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-6">
                        <h2 class="text-lg font-semibold text-gray-900 border-b border-gray-100 pb-4">Fichiers</h2>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Image de couverture</label>
                                <div class="flex items-center justify-center w-full">
                                    <label for="preview_image_input"
                                        class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 transition">
                                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                            <svg class="w-8 h-8 mb-3 text-gray-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                                </path>
                                            </svg>
                                            <p class="mb-2 text-sm text-gray-500"><span class="font-semibold">Cliquez pour
                                                    upload</span></p>
                                            <p class="text-xs text-gray-500">JPG, PNG (Max 2Mo)</p>
                                        </div>
                                        <input id="preview_image_input" type="file" name="preview_image" accept="image/*"
                                            class="hidden" />
                                    </label>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Fichier joint (PDF,
                                    Zip...)</label>
                                <div class="flex items-center justify-center w-full">
                                    <label for="file_input"
                                        class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 transition">
                                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                            <svg class="w-8 h-8 mb-3 text-gray-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                                                </path>
                                            </svg>
                                            <p class="mb-2 text-sm text-gray-500"><span class="font-semibold">Fichier
                                                    Ressource</span></p>
                                            <p class="text-xs text-gray-500">Optionnel</p>
                                        </div>
                                        <input id="file_input" type="file" name="file" class="hidden" />
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full bg-indigo-600 text-white font-bold py-3 px-4 rounded-xl hover:bg-indigo-700 transition focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 shadow-md hover:shadow-lg transform active:scale-95 duration-150">
                        Publier la ressource
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection