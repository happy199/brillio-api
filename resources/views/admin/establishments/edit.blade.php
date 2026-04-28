@extends('layouts.admin')

@section('title', 'Modifier l\'Établissement')

@section('header', 'Modifier : ' . $establishment->name)

@section('content')
<div class="max-w-5xl mx-auto py-8">
    <div class="mb-8">
        <a href="{{ route('admin.establishments.index') }}" class="text-indigo-600 hover:text-indigo-800 font-medium flex items-center gap-2">
            <i class="fas fa-arrow-left text-sm"></i> Retour à la liste
        </a>
        <h1 class="text-3xl font-bold text-gray-900 mt-4">Modifier : {{ $establishment->name }}</h1>
        <p class="text-gray-500">Mettez à jour les informations de cet établissement.</p>
    </div>

    <form action="{{ route('admin.establishments.update', $establishment) }}" method="POST" enctype="multipart/form-data" class="space-y-8" x-data="establishmentForm()">
        @csrf
        @method('PUT')

        <!-- Section 1: Infos Générales -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                <h2 class="text-lg font-bold text-gray-800">Informations Générales</h2>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Nom de l'établissement <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ $establishment->name }}" required class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Type d'établissement</label>
                    <select name="type" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="university" {{ $establishment->type === 'university' ? 'selected' : '' }}>Université / École Supérieure</option>
                        <option value="training_center" {{ $establishment->type === 'training_center' ? 'selected' : '' }}>Centre de formation professionnelle</option>
                        <option value="bootcamp" {{ $establishment->type === 'bootcamp' ? 'selected' : '' }}>Bootcamp / Accélérateur</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Pays</label>
                    <input type="text" name="country" value="{{ $establishment->country }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Ville</label>
                    <input type="text" name="city" value="{{ $establishment->city }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Adresse physique</label>
                    <input type="text" name="address" value="{{ $establishment->address }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Email de contact</label>
                    <input type="email" name="email" value="{{ $establishment->email }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Téléphone</label>
                    <input type="text" name="phone" value="{{ $establishment->phone }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="4" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">{{ $establishment->description }}</textarea>
                </div>
            </div>
        </div>

        <!-- Section 2: Médias -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                <h2 class="text-lg font-bold text-gray-800">Médias & Web</h2>
            </div>
            <div class="p-6 space-y-6">
                <div class="flex items-center gap-6">
                    @if($establishment->photo_path)
                    <div class="shrink-0">
                        <img src="{{ Storage::url($establishment->photo_path) }}" class="w-24 h-24 object-cover rounded-xl border-2 border-gray-100">
                    </div>
                    @endif
                    <div class="flex-1">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Modifier le logo</label>
                        <input type="file" name="photo" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    </div>
                </div>
                
                <div class="mt-4 border-t border-gray-100 pt-4">
                    <label class="block text-sm font-bold text-gray-700 mb-1"><i class="fas fa-images text-gray-400 mr-1"></i> Galerie Photos additionnelles (Illimité)</label>
                    <input type="file" name="gallery[]" multiple accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-gray-50 file:text-gray-700 hover:file:bg-gray-100">
                    <p class="text-xs text-gray-400 mt-1">Maintenez Maj ou Ctrl pour ajouter plusieurs images. Les nouvelles s'ajouteront à la galerie existante.</p>
                    
                    @if(is_array($establishment->gallery) && count($establishment->gallery) > 0)
                        <div class="mt-4 flex flex-wrap gap-3">
                            @foreach($establishment->gallery as $img)
                                <div class="relative group">
                                    <img src="{{ Storage::url($img) }}" class="w-20 h-20 object-cover rounded-lg border border-gray-200">
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="mt-4 border-t border-gray-100 pt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Brochures -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-3"><i class="fas fa-file-pdf text-red-400 mr-1"></i> Documents / Brochures (Max 3)</label>
                        <div class="space-y-3">
                            @php $brochures = is_array($establishment->brochures) ? $establishment->brochures : []; @endphp
                            @for($i=0; $i<3; $i++)
                            <div>
                                <input type="file" name="brochures[{{$i}}]" accept=".pdf,.doc,.docx,.xls,.xlsx,.zip" class="w-full text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border border-gray-200 file:text-xs file:font-semibold file:bg-white file:text-gray-700 hover:file:bg-gray-50 mb-1">
                                @if(isset($brochures[$i]))
                                    <a href="{{ Storage::url($brochures[$i]) }}" target="_blank" class="text-xs text-indigo-600 font-medium hover:underline"><i class="fas fa-external-link-alt mr-1"></i> Fichier actuel ({{ basename($brochures[$i]) }})</a>
                                @endif
                            </div>
                            @endfor
                        </div>
                    </div>
                    
                    <!-- Youtube Videos -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-3"><i class="fab fa-youtube text-red-600 mr-1"></i> Vidéos de présentation (Max 3)</label>
                        <div class="space-y-3">
                            @php $videos = is_array($establishment->presentation_videos) ? $establishment->presentation_videos : []; @endphp
                            @for($i=0; $i<3; $i++)
                            <input type="url" name="presentation_videos[]" value="{{ $videos[$i] ?? '' }}" placeholder="https://youtube.com/watch?v=..." class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-red-500 focus:border-red-500 text-sm">
                            @endfor
                        </div>
                    </div>
                </div>

                <div class="mt-6 border-t border-gray-100 pt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Site Web (URL)</label>
                        <input type="url" name="website_url" value="{{ $establishment->website_url }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1 flex items-center gap-1"><i class="fas fa-map-marker-alt text-red-500"></i> Google Maps (URL)</label>
                        <input type="url" name="google_maps_url" value="{{ $establishment->google_maps_url }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">LinkedIn</label>
                        <input type="url" name="linkedin" value="{{ $establishment->social_links['linkedin'] ?? '' }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Facebook</label>
                        <input type="url" name="facebook" value="{{ $establishment->social_links['facebook'] ?? '' }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Instagram</label>
                        <input type="url" name="instagram" value="{{ $establishment->social_links['instagram'] ?? '' }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 3: Matching & Tarifs -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                <h2 class="text-lg font-bold text-gray-800">Matching MBTI & Tarifs</h2>
            </div>
            <div class="p-6 space-y-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-3">Profils MBTI ciblés</label>
                    <div class="grid grid-cols-4 md:grid-cols-8 gap-2">
                        @foreach($mbtiTypes as $type)
                        <label class="relative flex flex-col items-center justify-center p-2 border-2 border-gray-100 rounded-xl cursor-pointer hover:bg-indigo-50 transition"
                            :class="mbti_types.includes('{{ $type }}') ? 'border-indigo-500 bg-indigo-50' : ''">
                            <input type="checkbox" name="mbti_types[]" value="{{ $type }}" x-model="mbti_types" class="hidden">
                            <span class="text-xs font-bold" :class="mbti_types.includes('{{ $type }}') ? 'text-indigo-700' : 'text-gray-400'">{{ $type }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Scolarité Min (CFA)</label>
                        <input type="number" name="tuition_min" value="{{ $establishment->tuition_min }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Scolarité Max (CFA)</label>
                        <input type="number" name="tuition_max" value="{{ $establishment->tuition_max }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 4: Form Builder -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                <h2 class="text-lg font-bold text-gray-800">Formulaire d'intérêt précis</h2>
                <label class="inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="has_precise_form" x-model="has_precise_form" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:bg-indigo-600 after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
                    <span class="ms-3 text-sm font-medium text-gray-700">Activer</span>
                </label>
            </div>
            <div class="p-6 space-y-4" x-show="has_precise_form" x-transition>
                <div class="space-y-3">
                    <template x-for="(field, index) in precise_form_config" :key="index">
                        <div class="flex items-start gap-2 bg-gray-50 p-4 rounded-xl border border-gray-100">
                            <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[10px] uppercase font-bold text-gray-400 mb-1">Label du champ</label>
                                    <input type="text" :name="'precise_form_config['+index+'][label]'" x-model="field.label" required class="w-full text-sm border-gray-200 rounded-lg shadow-sm focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-[10px] uppercase font-bold text-gray-400 mb-1">Type</label>
                                    <select :name="'precise_form_config['+index+'][type]'" x-model="field.type" class="w-full text-sm border-gray-200 rounded-lg shadow-sm focus:ring-indigo-500">
                                        <option value="text">Texte Court</option>
                                        <option value="textarea">Texte Long</option>
                                        <option value="select">Liste déroulante</option>
                                        <option value="radio">Choix Unique (Radio)</option>
                                        <option value="checkbox">Choix Multiple (Cocher)</option>
                                        <option value="number">Nombre</option>
                                        <option value="date">Date</option>
                                    </select>
                                </div>
                                <div class="md:col-span-2" x-show="['select', 'radio', 'checkbox'].includes(field.type)">
                                    <label class="block text-[10px] uppercase font-bold text-gray-400 mb-1">Options (séparées par une virgule)</label>
                                    <input type="text" :name="'precise_form_config['+index+'][options]'" x-model="field.options_string" class="w-full text-sm border-gray-200 rounded-lg shadow-sm focus:ring-indigo-500">
                                </div>
                            </div>
                            <button type="button" @click="removeField(index)" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </template>
                </div>
                <button type="button" @click="addField()" class="w-full py-3 border-2 border-dashed border-gray-200 rounded-xl text-gray-500 hover:bg-gray-50 transition font-bold text-sm">
                    <i class="fas fa-plus mr-2"></i> Ajouter un champ
                </button>
            </div>
        </div>

        <!-- Submit -->
        <div class="flex items-center justify-between pt-4">
            <label class="inline-flex items-center">
                <input type="checkbox" name="is_published" {{ $establishment->is_published ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                <span class="ml-2 text-gray-700 font-medium">Brouillon / Publié</span>
            </label>
            <button type="submit" class="px-10 py-4 bg-indigo-600 text-white rounded-2xl font-bold text-lg hover:bg-indigo-700 transition shadow-lg shadow-indigo-200">
                Mettre à jour l'établissement
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    function establishmentForm() {
        return {
            mbti_types: @json($establishment->mbti_types ?? []),
            has_precise_form: @json((bool)$establishment->has_precise_form),
            precise_form_config: (@json($establishment->precise_form_config ?? [])).map(f => ({
                ...f,
                options_string: f.options || ''
            })),
            
            addField() {
                this.precise_form_config.push({
                    label: '',
                    type: 'text',
                    options_string: '',
                    required: true
                });
            },
            
            removeField(index) {
                this.precise_form_config.splice(index, 1);
            }
        }
    }
</script>
@endpush
