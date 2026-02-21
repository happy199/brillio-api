@extends('layouts.organization')

@section('title', 'Invitations')

@section('content')
<div class="space-y-6" x-data="{ 
    showDeleteModal: false,
    invitationToDelete: null,
    copyToClipboard(url) {
        navigator.clipboard.writeText(url).then(() => {
            window.dispatchEvent(new CustomEvent('copy-notification', { 
                detail: { message: 'Lien copié dans le presse-papiers !', type: 'success' } 
            }));
        }).catch(err => {
            window.dispatchEvent(new CustomEvent('copy-notification', { 
                detail: { message: 'Erreur lors de la copie.', type: 'error' } 
            }));
        });
    },
    confirmDelete(url) {
        this.invitationToDelete = url;
        this.showDeleteModal = true;
    }
}">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Invitations</h1>
            <p class="mt-1 text-sm text-gray-600">Gérez vos invitations et codes de parrainage</p>
        </div>
        <a href="{{ route('organization.invitations.create') }}"
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-organization-600 hover:bg-organization-700 transition-colors">
            <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Créer une invitation
        </a>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total</dt>
                            <dd class="text-2xl font-semibold text-gray-900">{{ $invitations->total() }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Actives</dt>
                            <dd class="text-2xl font-semibold text-gray-900">{{
                                $organization->invitations()->where('status', '!=', 'expired')->where(function($q) {
                                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                                })->count() }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-organization-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Utilisées</dt>
                            <dd class="text-2xl font-semibold text-gray-900">{{
                                $organization->invitations()->whereNotNull('accepted_at')->count() }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Invitations List -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        @if($invitations->count() > 0)
        <ul role="list" class="divide-y divide-gray-200">
            @foreach($invitations as $invitation)
            @php /** @var \App\Models\OrganizationInvitation $invitation */ @endphp
            <li class="px-6 py-4 hover:bg-gray-50">
                <div class="flex items-center justify-between">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0">
                                @if($invitation->isExpired())
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Expiré
                                </span>
                                @elseif($invitation->uses_count > 0)
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-organization-100 text-organization-800">
                                    Utilisé ({{ $invitation->uses_count }})
                                </span>
                                @else
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    Non utilisé
                                </span>
                                @endif
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center space-x-2">
                                    <p class="text-sm font-medium text-gray-900 font-mono">{{ $invitation->referral_code
                                        }}</p>
                                    @if($invitation->role === 'admin')
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">Admin</span>
                                    @elseif($invitation->role === 'viewer')
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">Observateur</span>
                                    @else
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">Jeune</span>
                                    @endif
                                </div>
                                @if($invitation->invited_email)
                                <p class="text-sm text-gray-500">{{ $invitation->invited_email }}</p>
                                @endif
                                <p class="text-xs text-gray-400 mt-1">
                                    Créée le {{ $invitation->invited_at->format('d/m/Y à H:i') }}
                                    @if($invitation->accepted_at)
                                    • Acceptée le {{ $invitation->accepted_at->format('d/m/Y') }}
                                    @elseif(!$invitation->isExpired())
                                    • Expire le {{ $invitation->expires_at->format('d/m/Y') }}
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        @if(!$invitation->isExpired())
                        <button
                            @click="copyToClipboard('{{ $invitation->role === 'jeune' ? route('auth.jeune.register', ['ref' => $invitation->referral_code]) : route('organization.register', ['ref' => $invitation->referral_code]) }}')"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
                            <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                            Copier le lien
                        </button>
                        @endif
                        @if(!$invitation->isExpired())
                        <button type="button"
                            @click="confirmDelete('{{ route('organization.invitations.destroy', $invitation) }}')"
                            class="inline-flex items-center px-3 py-2 border border-red-300 shadow-sm text-sm leading-4 font-medium rounded-md text-red-700 bg-white hover:bg-red-50">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                        @endif
                    </div>
                </div>
            </li>
            @endforeach
        </ul>

        <!-- Pagination -->
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $invitations->links() }}
        </div>
        @else
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Aucune invitation</h3>
            <p class="mt-1 text-sm text-gray-500">Commencez par créer une invitation pour inviter des jeunes talents.
            </p>
            <div class="mt-6">
                <a href="{{ route('organization.invitations.create') }}"
                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-organization-600 hover:bg-organization-700">
                    <svg class="mr-2 -ml-1 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Créer une invitation
                </a>
            </div>
        </div>
        @endif
    </div>


    <!-- Delete Confirmation Modal -->
    <div x-show="showDeleteModal" style="display: none;" class="fixed z-10 inset-0 overflow-y-auto"
        aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showDeleteModal" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                aria-hidden="true" @click="showDeleteModal = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="showDeleteModal" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <div class="sm:flex sm:items-start">
                    <div
                        class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Supprimer l'invitation
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                Êtes-vous sûr de vouloir supprimer cette invitation ? Cette action est irréversible et
                                le
                                lien ne fonctionnera plus.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                    <form :action="invitationToDelete" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Supprimer
                        </button>
                    </form>
                    <button type="button" @click="showDeleteModal = false"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-organization-500 sm:mt-0 sm:w-auto sm:text-sm">
                        Annuler
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection