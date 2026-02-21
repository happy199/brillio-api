@extends('layouts.organization')

@section('title', 'Créer une invitation')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="md:flex md:items-center md:justify-between mb-6">
        <div class="flex-1 min-w-0">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                Créer une invitation
            </h2>
        </div>
    </div>

    <div class="bg-white shadow sm:rounded-lg">
        <form method="POST" action="{{ route('organization.invitations.store') }}" class="space-y-6 p-6">
            @csrf

            <!-- Info Box -->
            <div class="bg-organization-50 border border-organization-200 rounded-md p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-organization-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm text-organization-700">
                            Un code de parrainage unique sera généré automatiquement pour chaque invitation. Vous
                            pourrez partager le lien d'inscription ou copier le code.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Emails (Multiple) -->
            <div>
                <label for="invited_emails" class="block text-sm font-medium text-gray-700 mb-2">
                    Adresses email des destinataires <span class="text-gray-400 font-normal">(optionnel)</span>
                </label>
                <div class="mt-1">
                    <textarea name="invited_emails" id="invited_emails" rows="5"
                        class="shadow-sm focus:ring-organization-500 focus:border-organization-500 block w-full sm:text-sm border-gray-300 rounded-md px-4 py-3"
                        placeholder="contact@exemple.com&#10;autre@exemple.com&#10;email3@exemple.com">{{ old('invited_emails') }}</textarea>
                </div>
                <p class="mt-2 text-sm text-gray-500">
                    📧 <strong>Plusieurs emails :</strong> Entrez une adresse email par ligne ou séparez-les par des
                    virgules. Une invitation sera créée pour chaque email.
                </p>
                <p class="mt-1 text-sm text-gray-500">
                    🔗 <strong>Lien partageable :</strong> Laissez ce champ vide pour créer un lien unique partageable
                    avec plusieurs personnes.
                </p>
                @error('invited_emails')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <!-- Role -->
            <div>
                <label for="role" class="block text-sm font-medium text-gray-700 mb-2">
                    Type d'invitation (Rôle)
                </label>
                <div class="mt-1">
                    <select id="role" name="role"
                        class="shadow-sm focus:ring-organization-500 focus:border-organization-500 block w-full sm:text-sm border-gray-300 rounded-md px-4 py-3">
                        <option value="jeune" selected>Jeune ou Mentor (Accès utilisateur)</option>
                        @if($organization->isEnterprise())
                        <option value="admin">Administrateur (Gestion complète de l'organisation)</option>
                        <option value="viewer">Observateur (Consultation en lecture seule)</option>
                        @endif
                    </select>
                </div>
                <p class="mt-2 text-sm text-gray-500">
                    Détermine le niveau d'accès de la personne que vous invitez.
                </p>
                @error('role')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <!-- Expiration -->
            <div>
                <label for="expires_days" class="block text-sm font-medium text-gray-700 mb-2">
                    Durée de validité
                </label>
                <div class="mt-1">
                    <select id="expires_days" name="expires_days"
                        class="shadow-sm focus:ring-organization-500 focus:border-organization-500 block w-full sm:text-sm border-gray-300 rounded-md px-4 py-3">
                        <option value="7">7 jours</option>
                        <option value="14">14 jours</option>
                        <option value="30" selected>30 jours (recommandé)</option>
                        <option value="60">60 jours</option>
                        <option value="90">90 jours</option>
                        <option value="365">1 an</option>
                    </select>
                </div>
                <p class="mt-2 text-sm text-gray-500">
                    L'invitation expirera après cette période et ne pourra plus être utilisée.
                </p>
            </div>

            <!-- Actions -->
            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                <a href="{{ route('organization.invitations.index') }}"
                    class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
                    Annuler
                </a>
                <button type="submit"
                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-organization-600 hover:bg-organization-700 focus:outline-none">
                    Créer l'invitation
                </button>
            </div>
        </form>
    </div>

    <!-- Quick Tips -->
    <div class="mt-6 bg-gray-50 border border-gray-200 rounded-lg p-4">
        <h3 class="text-sm font-semibold text-gray-900 mb-3">💡 Conseils d'utilisation</h3>
        <ul class="space-y-2 text-sm text-gray-600">
            <li class="flex items-start">
                <svg class="h-5 w-5 text-organization-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd" />
                </svg>
                <span><strong>Invitations personnelles :</strong> Entrez les emails pour suivre précisément qui a
                    utilisé chaque invitation</span>
            </li>
            <li class="flex items-start">
                <svg class="h-5 w-5 text-organization-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd" />
                </svg>
                <span><strong>Invitations en masse :</strong> Vous pouvez créer jusqu'à 100 invitations en une seule
                    fois</span>
            </li>
            <li class="flex items-start">
                <svg class="h-5 w-5 text-organization-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd" />
                </svg>
                <span><strong>Durée :</strong> Une durée de 30 jours est recommandée pour les campagnes de
                    recrutement</span>
            </li>
        </ul>
    </div>
</div>
@endsection