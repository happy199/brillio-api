<x-mail::message>
# Bonjour {{ $recipient->name }},

Vous avez reçu un nouveau message de **{{ $senderName }}** sur Brillio.

<x-mail::button :url="$conversationUrl">
Voir le message
</x-mail::button>

Merci,<br>
L'équipe {{ config('app.name') }}
</x-mail::message>
