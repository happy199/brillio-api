@extends('layouts.admin')

@section('title', 'Activités Commerciales')
@section('header', 'Activités Commerciales')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div>
        <h2 class="text-xl font-bold text-gray-800">Suivi des activités commerciales</h2>
        <p class="text-sm text-gray-500">Consultez les dossiers pris en charge par les commerciaux.</p>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Commercial</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type / Cible</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Début</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fin / Rapport</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @foreach($activities as $activity)
            <tr>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ $activity->commercial->name ?? 'Inconnu' }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    @if($activity->assignable_type === \App\Models\User::class)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mb-1">
                            Utilisateur
                        </span>
                        <div class="text-sm text-gray-900">{{ $activity->assignable->name ?? 'N/A' }}</div>
                    @elseif($activity->assignable_type === \App\Models\MentorProfile::class)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 mb-1">
                            Mentor
                        </span>
                        <div class="text-sm text-gray-900">{{ $activity->assignable->user->name ?? 'N/A' }}</div>
                    @elseif($activity->assignable_type === \App\Models\Organization::class)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 mb-1">
                            Organisation
                        </span>
                        <div class="text-sm text-gray-900">{{ $activity->assignable->name ?? 'N/A' }}</div>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    @if($activity->status === 'active')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            En cours
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                            Clôturé
                        </span>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ $activity->started_at->format('d/m/Y H:i') }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-500">
                    @if($activity->status === 'closed')
                        <div class="text-xs mb-1">Clôturé le {{ $activity->ended_at->format('d/m/Y H:i') }}</div>
                        <p class="whitespace-pre-line text-xs italic">{{ Str::limit($activity->summary, 100) }}</p>
                    @else
                        @if(auth()->user()->isAdmin() || auth()->id() === $activity->commercial_id)
                            <div x-data="{ open: false }">
                                <button @click="open = true" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">Clôturer</button>
                                
                                <div x-show="open" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                                    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                                        <div class="fixed inset-0 transition-opacity" aria-hidden="true" @click="open = false">
                                            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                                        </div>

                                        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                            <form action="{{ route('admin.commercials.end_charge', $activity) }}" method="POST">
                                                @csrf
                                                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Clôturer le dossier</h3>
                                                    
                                                    <div class="mb-4">
                                                        <label class="block text-sm font-medium text-gray-700 mb-2">Rapport de clôture</label>
                                                        <textarea name="summary" required rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Décrivez les actions menées, le résultat..."></textarea>
                                                    </div>
                                                </div>
                                                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                                                        Enregistrer
                                                    </button>
                                                    <button type="button" @click="open = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                                        Annuler
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            -
                        @endif
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="p-4 border-t">
        {{ $activities->links() }}
    </div>
</div>
@endsection
