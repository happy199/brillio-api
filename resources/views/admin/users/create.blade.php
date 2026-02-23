@extends('layouts.admin')

@section('title', 'Créer un utilisateur')

@section('header')
<div class="flex items-center">
    <a href="{{ route('admin.users.index') }}" class="text-gray-500 hover:text-gray-700 mr-4 transition-colors">
        <i class="fas fa-arrow-left"></i> Retour
    </a>
    <h2 class="text-2xl font-bold text-gray-900">Nouveau compte (Démo/Test)</h2>
</div>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white shadow-xl rounded-2xl overflow-hidden border border-gray-100">
        <div class="p-8">
            <div class="mb-6">
                <p class="text-gray-600">
                    Utilisez ce formulaire pour créer rapidement des comptes de test.
                    <strong>Le mot de passe sera généré automatiquement</strong> et affiché à l'écran suivant.
                    L'email sera automatiquement considéré comme vérifié.
                </p>
            </div>

            <form action="{{ route('admin.users.store') }}" method="POST">
                @csrf

                <div class="space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Nom complet</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}"
                            class="p-3 bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block w-full transition-all"
                            required placeholder="Ex: Jean Dupont">
                        @error('name')
                        <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Adresse Email</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}"
                            class="p-3 bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block w-full transition-all"
                            required placeholder="test@brillio.com">
                        @error('email')
                        <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="user_type" class="block text-sm font-semibold text-gray-700 mb-2">Type de
                            compte</label>
                        <select name="user_type" id="user_type"
                            class="p-3 bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block w-full appearance-none transition-all">
                            <option value="jeune" {{ old('user_type')=='jeune' ? 'selected' : '' }}>Jeune (Étudiant)
                            </option>
                            <option value="mentor" {{ old('user_type')=='mentor' ? 'selected' : '' }}>Mentor</option>
                            <option value="organization" {{ old('user_type')=='organization' ? 'selected' : '' }}>
                                Organisation</option>
                            <option value="admin" {{ old('user_type')=='admin' ? 'selected' : '' }}>Administrateur
                            </option>
                        </select>
                        @error('user_type')
                        <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex items-center justify-end gap-x-6 border-t border-gray-50 pt-8 mt-8">
                    <a href="{{ route('admin.users.index') }}"
                        class="text-sm font-bold text-gray-600 hover:text-gray-900 transition-colors uppercase tracking-wider">Annuler</a>
                    <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 px-8 rounded-xl shadow-lg hover:shadow-xl transition-all uppercase tracking-widest text-xs">
                        Créer le compte & Générer accès
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection