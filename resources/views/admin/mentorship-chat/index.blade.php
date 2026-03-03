@extends('layouts.admin')

@section('title', 'Suivi Chat Mentorat')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Suivi Chat Mentorat</h1>
            <p class="text-gray-600">Surveillez les échanges entre mentors et jeunes (PII et modération)</p>
        </div>
    </div>

    <!-- Filtres -->
    <div class="bg-white rounded-xl shadow-sm p-4">
        <form method="GET" action="{{ route('admin.mentorship-chat.index') }}" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">Rechercher</label>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Nom du mentor ou du jeune..." class="w-full rounded-lg border-gray-300 text-sm">
            </div>
            <div class="w-48">
                <label class="block text-sm font-medium text-gray-700 mb-1">Filtrer par</label>
                <div class="flex items-center mt-2">
                    <input type="checkbox" name="flagged" id="flagged" value="1" {{ request('flagged') ? 'checked' : ''
                        }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <label for="flagged" class="ml-2 text-sm text-gray-700">Contenu signalé</label>
                </div>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm">
                    Filtrer
                </button>
                @if(request()->hasAny(['search', 'flagged']))
                <a href="{{ route('admin.mentorship-chat.index') }}"
                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm">
                    Réinitialiser
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Liste des conversations -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Alertes</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Participants</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dernier Message</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Activité</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($mentorships as $mentorship)
                <tr class="hover:bg-gray-50 {{ $mentorship->messages_count > 0 ? 'bg-red-50' : '' }}">
                    <td class="px-6 py-4 text-center">
                        @if($mentorship->reported_at)
                        <div class="mb-2">
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-red-600 text-white animate-pulse">
                                SIGNALÉ
                            </span>
                        </div>
                        @endif

                        @if($mentorship->messages_count > 0)
                        <div class="flex items-center justify-center">
                            <span class="relative flex h-4 w-4">
                                <span
                                    class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-4 w-4 bg-red-500"></span>
                            </span>
                            <span class="ml-2 text-xs font-bold text-red-600">{{ $mentorship->messages_count }}
                                alertes PII</span>
                        </div>
                        @else
                        @if(!$mentorship->reported_at)
                        <span class="text-green-500 text-xs">Sain</span>
                        @endif
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm">
                            <div class="font-medium text-gray-900">Mentor: {{ $mentorship->mentor->name }}</div>
                            <div class="text-gray-500">Jeune: {{ $mentorship->mentee->name }}</div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        @if($mentorship->messages->isNotEmpty())
                        <div class="text-sm text-gray-900">
                            {{ Str::limit($mentorship->messages->first()->body, 50) }}
                        </div>
                        <div class="text-xs text-gray-500">
                            par {{ $mentorship->messages->first()->sender_id == $mentorship->mentor_id ? 'Mentor' :
                            'Jeune' }}
                        </div>
                        @else
                        <span class="text-xs text-gray-400 italic">Aucun message</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        {{ $mentorship->updated_at->diffForHumans() }}
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('admin.mentorship-chat.show', $mentorship) }}"
                            class="inline-flex items-center px-3 py-1 bg-indigo-100 text-indigo-700 rounded-md hover:bg-indigo-200 text-sm font-medium">
                            Visualiser
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                        Aucune conversation de mentorat trouvée
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Pagination -->
        @if($mentorships->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $mentorships->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection