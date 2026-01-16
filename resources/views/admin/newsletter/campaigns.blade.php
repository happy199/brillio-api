@extends('layouts.admin')

@section('title', 'Historique des Campagnes Email')

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Historique des Campagnes</h1>
                <p class="text-gray-600 mt-1">Suivi des envois de newsletters</p>
            </div>
            <a href="{{ route('admin.newsletter.index') }}"
                class="px-6 py-3 bg-gray-600 text-white font-bold rounded-lg hover:bg-gray-700">
                <i class="fas fa-arrow-left mr-2"></i>Retour aux abonnés
            </a>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sujet</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Destinataires</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Succès / Échecs</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date d'envoi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($campaigns as $campaign)
                        <tr>
                            <td class="px-6 py-4">
                                @if($campaign->status == 'sent')
                                    <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">Envoyé</span>
                                @elseif($campaign->status == 'queued' || $campaign->status == 'sending')
                                    <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">En cours</span>
                                @elseif($campaign->status == 'partial')
                                    <span class="px-2 py-1 text-xs bg-orange-100 text-orange-800 rounded-full">Partiel</span>
                                @else
                                    <span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded-full">Échec</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <div class="font-medium">{{ $campaign->subject }}</div>
                                <div class="text-xs text-gray-500 truncate max-w-xs">{{ Str::limit($campaign->body, 50) }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $campaign->recipients_count }} destinataires
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <span class="text-green-600 font-bold">{{ $campaign->sent_count }}</span> /
                                <span class="text-red-600">{{ $campaign->failed_count }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $campaign->created_at->format('d/m/Y H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">Aucune campagne envoyée pour le moment.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $campaigns->links() }}
        </div>
    </div>
@endsection