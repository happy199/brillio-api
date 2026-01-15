@extends('layouts.admin')

@section('title', 'Messages de Contact')

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Messages de Contact</h1>
                <p class="text-gray-600 mt-1">Gérer et répondre aux messages</p>
            </div>
            <a href="{{ route('admin.contact-messages.export-pdf', request()->query()) }}"
                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                <i class="fas fa-file-pdf mr-2"></i>Export PDF
            </a>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-lg">
                        <i class="fas fa-envelope text-blue-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">Total</p>
                        <p class="text-2xl font-bold">{{ $stats['total'] }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-yellow-100 rounded-lg">
                        <i class="fas fa-exclamation-circle text-yellow-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">Nouveaux</p>
                        <p class="text-2xl font-bold">{{ $stats['new'] }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-lg">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">Répondus</p>
                        <p class="text-2xl font-bold">{{ $stats['replied'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-6">
            <form method="GET" class="flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Rechercher</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Nom, email, sujet..."
                        class="w-full px-4 py-2 border rounded-lg">
                </div>
                <div class="min-w-[150px]">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                    <select name="status" class="w-full px-4 py-2 border rounded-lg">
                        <option value="">Tous</option>
                        <option value="new" {{ request('status') == 'new' ? 'selected' : '' }}>Nouveaux</option>
                        <option value="read" {{ request('status') == 'read' ? 'selected' : '' }}>Lus</option>
                        <option value="replied" {{ request('status') == 'replied' ? 'selected' : '' }}>Répondus</option>
                    </select>
                </div>
                <button type="submit" class="px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                    Filtrer
                </button>
            </form>
        </div>

        <!-- Messages List -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nom</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sujet</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($messages as $message)
                        <tr class="{{ $message->status == 'new' ? 'bg-yellow-50' : '' }}">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $message->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $message->email }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ Str::limit($message->subject, 50) }}</td>
                            <td class="px-6 py-4">
                                @if($message->status == 'new')
                                    <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded-full">Nouveau</span>
                                @elseif($message->status == 'read')
                                    <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">Lu</span>
                                @else
                                    <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">Répondu</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $message->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-6 py-4 text-sm space-x-3">
                                <a href="{{ route('admin.contact-messages.show', $message->id) }}"
                                    class="text-blue-600 hover:text-blue-900">
                                    <i class="fas fa-eye"></i> Voir
                                </a>
                                <form action="{{ route('admin.contact-messages.destroy', $message->id) }}" method="POST"
                                    class="inline" onsubmit="return confirm('Supprimer ce message ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">Aucun message trouvé</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $messages->links() }}
        </div>
    </div>
@endsection