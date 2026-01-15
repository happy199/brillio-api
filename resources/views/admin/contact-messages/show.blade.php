@extends('layouts.admin')

@section('title', 'Message de Contact')

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Message de Contact</h1>
                <a href="{{ route('admin.contact-messages.index') }}" class="text-blue-600 hover:underline text-sm">
                    ← Retour à la liste
                </a>
            </div>
        </div>

        <!-- Message Details -->
        <div class="bg-white rounded-lg shadow p-6 space-y-4">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <h2 class="text-xl font-bold text-gray-900">{{ $message->subject }}</h2>
                    <div class="mt-2 space-y-1 text-sm text-gray-600">
                        <p><strong>De:</strong> {{ $message->name }} ({{ $message->email }})</p>
                        <p><strong>Date:</strong> {{ $message->created_at->format('d/m/Y à H:i') }}</p>
                        <p><strong>IP:</strong> {{ $message->ip_address ?? 'N/A' }}</p>
                    </div>
                </div>
                <div>
                    @if($message->status == 'new')
                        <span class="px-3 py-1 text-sm bg-yellow-100 text-yellow-800 rounded-full">Nouveau</span>
                    @elseif($message->status == 'read')
                        <span class="px-3 py-1 text-sm bg-blue-100 text-blue-800 rounded-full">Lu</span>
                    @else
                        <span class="px-3 py-1 text-sm bg-green-100 text-green-800 rounded-full">Répondu</span>
                    @endif
                </div>
            </div>

            <div class="border-t pt-4">
                <h3 class="font-semibold text-gray-900 mb-2">Message:</h3>
                <div class="bg-gray-50 rounded-lg p-4 whitespace-pre-wrap text-gray-700">{{ $message->message }}</div>
            </div>

            @if($message->status == 'replied' && $message->reply_message)
                <div class="border-t pt-4">
                    <h3 class="font-semibold text-gray-900 mb-2">Réponse envoyée:</h3>
                    <div class="bg-green-50 rounded-lg p-4">
                        <p class="text-sm text-gray-600 mb-2">
                            Par {{ $message->repliedBy->name }} le {{ $message->replied_at->format('d/m/Y à H:i') }}
                        </p>
                        <div class="whitespace-pre-wrap text-gray-700">{{ $message->reply_message }}</div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Reply Form -->
        @if($message->status != 'replied')
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Répondre au message</h3>
                <form action="{{ route('admin.contact-messages.reply', $message->id) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Format</label>
                        <div class="flex gap-4">
                            <label class="flex items-center">
                                <input type="radio" name="format" value="text" checked class="mr-2">
                                <span>Texte brut</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="format" value="html" class="mr-2">
                                <span>HTML</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Votre réponse</label>
                        <textarea name="reply_message" rows="10" required
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                            placeholder="Écrivez votre réponse ici..."></textarea>
                        <p class="text-sm text-gray-500 mt-1">
                            Si vous choisissez HTML, vous pouvez utiliser des balises HTML pour formater votre message.
                        </p>
                    </div>

                    <div class="flex justify-end gap-3">
                        <a href="{{ route('admin.contact-messages.index') }}"
                            class="px-6 py-2 border rounded-lg hover:bg-gray-50">
                            Annuler
                        </a>
                        <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            <i class="fas fa-paper-plane mr-2"></i>Envoyer la réponse
                        </button>
                    </div>
                </form>
            </div>
        @endif
    </div>
@endsection