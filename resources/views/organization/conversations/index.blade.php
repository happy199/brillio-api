@extends('layouts.organization')

@section('title', 'Suivi des Conversations')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Suivi des Conversations</h1>
            <p class="text-sm text-gray-600">Accédez en lecture seule aux échanges entre vos jeunes mentorés et leurs
                mentors.</p>
        </div>
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-lg border border-gray-200">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Menteur
                            / Mentoré</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Dernier
                            message</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Lien
                        </th>
                        <th scope="col" class="relative px-6 py-3">
                            <span class="sr-only">Visualiser</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($mentorships as $mentorship)
                    <tr class="hover:bg-gray-50 transition-colors cursor-pointer"
                        onclick="window.location='{{ route('organization.conversations.show', $mentorship) }}'">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-4">
                                <div class="flex -space-x-3 overflow-hidden">
                                    <img class="inline-block h-10 w-10 rounded-full ring-2 ring-white object-cover"
                                        src="{{ $mentorship->mentee->avatar_url }}"
                                        alt="{{ $mentorship->mentee->name }}">
                                    <img class="inline-block h-10 w-10 rounded-full ring-2 ring-white object-cover"
                                        src="{{ $mentorship->mentor->avatar_url }}"
                                        alt="{{ $mentorship->mentor->name }}">
                                </div>
                                <div class="text-sm">
                                    <p class="font-bold text-gray-900">{{ $mentorship->mentee->name }} & {{
                                        $mentorship->mentor->name }}</p>
                                    <p class="text-gray-500 text-xs">Débuté le {{
                                        $mentorship->created_at->format('d/m/Y') }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-500 max-w-xs md:max-w-md truncate">
                                @if($mentorship->messages->isNotEmpty())
                                @php $lastMessage = $mentorship->messages->first(); @endphp
                                @if($lastMessage->body)
                                {{ $lastMessage->body }}
                                @elseif($lastMessage->hasAttachment())
                                <span class="flex items-center gap-1.5 italic text-indigo-500 font-medium">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                    </svg>
                                    Fichier : {{ $lastMessage->attachment_name }}
                                </span>
                                @endif
                                @else
                                <span class="italic text-gray-400">Aucun message</span>
                                @endif
                            </div>
                            @if($mentorship->messages->isNotEmpty())
                            <p class="text-[10px] text-gray-400 mt-0.5">{{
                                $mentorship->messages->first()->created_at->diffForHumans() }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex flex-col gap-1">
                                @if($mentorship->mentee->sponsored_by_organization_id === $organization->id)
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">Jeune
                                    parrainé</span>
                                @endif
                                @if($organization->mentors()->where('users.id', $mentorship->mentor_id)->exists())
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-800">Mentor
                                    membre</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('organization.conversations.show', $mentorship) }}"
                                class="text-indigo-600 hover:text-indigo-900 font-bold border border-indigo-200 px-3 py-1.5 rounded-lg hover:bg-indigo-50 transition">
                                Consulter
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                            <p class="text-lg font-medium">Aucune conversation trouvée.</p>
                            <p class="text-sm">Les conversations apparaîtront ici dès que vos parrainés commenceront à
                                échanger.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Info Banner -->
    <div class="bg-indigo-50 border-l-4 border-indigo-400 p-4 rounded-lg">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-indigo-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                        clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-indigo-700">
                    <strong>Note :</strong> L'accès aux conversations est en mode lecture seule. Vous pouvez consulter
                    l'historique des échanges pour assurer le bon suivi pédagogique du mentorat.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection