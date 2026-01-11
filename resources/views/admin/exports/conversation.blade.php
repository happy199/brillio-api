<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Brillio - Conversation #{{ $conversation->id }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.5;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #4f46e5;
            margin: 0;
            font-size: 20px;
        }
        .header p {
            color: #666;
            margin: 5px 0 0;
        }
        .meta-section {
            background: #f9fafb;
            padding: 15px;
            margin-bottom: 25px;
            border: 1px solid #e5e7eb;
        }
        .meta-section h2 {
            color: #4f46e5;
            font-size: 14px;
            margin: 0 0 10px 0;
        }
        .meta-grid {
            display: table;
            width: 100%;
        }
        .meta-row {
            display: table-row;
        }
        .meta-label {
            display: table-cell;
            padding: 3px 10px 3px 0;
            font-weight: bold;
            width: 150px;
        }
        .meta-value {
            display: table-cell;
            padding: 3px 0;
        }
        .messages-section {
            margin-top: 25px;
        }
        .messages-section h2 {
            color: #4f46e5;
            font-size: 14px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }
        .message {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 5px;
        }
        .message-user {
            background: #dbeafe;
            border-left: 3px solid #2563eb;
        }
        .message-assistant {
            background: #f3f4f6;
            border-left: 3px solid #6b7280;
        }
        .message-human {
            background: #ffedd5;
            border-left: 3px solid #ea580c;
        }
        .message-system {
            background: #fef3c7;
            border-left: 3px solid #d97706;
            font-style: italic;
        }
        .message-header {
            font-size: 10px;
            color: #666;
            margin-bottom: 5px;
        }
        .message-role {
            font-weight: bold;
            text-transform: uppercase;
        }
        .message-content {
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #999;
            border-top: 1px solid #e5e7eb;
            padding-top: 15px;
        }
        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: bold;
        }
        .status-normal {
            background: #dcfce7;
            color: #166534;
        }
        .status-pending {
            background: #fef2f2;
            color: #991b1b;
        }
        .status-active {
            background: #ffedd5;
            color: #9a3412;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Brillio - Export de Conversation</h1>
        <p>{{ $conversation->title ?? 'Conversation #' . $conversation->id }}</p>
    </div>

    <div class="meta-section">
        <h2>Informations</h2>
        <div class="meta-grid">
            <div class="meta-row">
                <div class="meta-label">ID Conversation</div>
                <div class="meta-value">#{{ $conversation->id }}</div>
            </div>
            <div class="meta-row">
                <div class="meta-label">Utilisateur</div>
                <div class="meta-value">{{ $conversation->user->name ?? 'Utilisateur supprime' }} ({{ $conversation->user->email ?? '-' }})</div>
            </div>
            <div class="meta-row">
                <div class="meta-label">Nombre de messages</div>
                <div class="meta-value">{{ $conversation->messages->count() }}</div>
            </div>
            <div class="meta-row">
                <div class="meta-label">Date de creation</div>
                <div class="meta-value">{{ $conversation->created_at->format('d/m/Y H:i') }}</div>
            </div>
            <div class="meta-row">
                <div class="meta-label">Derniere activite</div>
                <div class="meta-value">{{ $conversation->updated_at->format('d/m/Y H:i') }}</div>
            </div>
            <div class="meta-row">
                <div class="meta-label">Statut</div>
                <div class="meta-value">
                    @if($conversation->needs_human_support && !$conversation->human_support_active)
                        <span class="status-badge status-pending">En attente conseiller</span>
                    @elseif($conversation->human_support_active)
                        <span class="status-badge status-active">Support actif</span>
                    @else
                        <span class="status-badge status-normal">Normal</span>
                    @endif
                </div>
            </div>
            @if($conversation->human_support_started_at)
            <div class="meta-row">
                <div class="meta-label">Support demarre</div>
                <div class="meta-value">{{ $conversation->human_support_started_at->format('d/m/Y H:i') }}</div>
            </div>
            @endif
            @if($conversation->human_support_ended_at)
            <div class="meta-row">
                <div class="meta-label">Support termine</div>
                <div class="meta-value">{{ $conversation->human_support_ended_at->format('d/m/Y H:i') }}</div>
            </div>
            @endif
            @if($conversation->supportAdmin)
            <div class="meta-row">
                <div class="meta-label">Conseiller</div>
                <div class="meta-value">{{ $conversation->supportAdmin->name }}</div>
            </div>
            @endif
        </div>
    </div>

    @if($conversation->user && $conversation->user->personalityTest)
    <div class="meta-section">
        <h2>Profil utilisateur</h2>
        <div class="meta-grid">
            <div class="meta-row">
                <div class="meta-label">Type</div>
                <div class="meta-value">{{ $conversation->user->user_type === 'mentor' ? 'Mentor' : 'Jeune' }}</div>
            </div>
            <div class="meta-row">
                <div class="meta-label">Pays</div>
                <div class="meta-value">{{ $conversation->user->country ?? '-' }}</div>
            </div>
            <div class="meta-row">
                <div class="meta-label">Personnalite</div>
                <div class="meta-value">{{ $conversation->user->personalityTest->personality_type }} - {{ $conversation->user->personalityTest->personality_label }}</div>
            </div>
        </div>
    </div>
    @endif

    <div class="messages-section">
        <h2>Messages ({{ $conversation->messages->count() }})</h2>

        @foreach($conversation->messages as $message)
        <div class="message
            @if($message->role === 'user')
                message-user
            @elseif($message->is_from_human)
                message-human
            @elseif($message->is_system_message)
                message-system
            @else
                message-assistant
            @endif">
            <div class="message-header">
                <span class="message-role">
                    @if($message->role === 'user')
                        {{ $conversation->user->name ?? 'Utilisateur' }}
                    @elseif($message->is_from_human)
                        Conseiller {{ $message->admin ? '(' . $message->admin->name . ')' : '' }}
                    @elseif($message->is_system_message)
                        Systeme
                    @else
                        Brillio IA
                    @endif
                </span>
                - {{ $message->created_at->format('d/m/Y H:i:s') }}
            </div>
            <div class="message-content">{{ $message->content }}</div>
        </div>
        @endforeach
    </div>

    <div class="footer">
        <p>Document genere le {{ $generatedAt->format('d/m/Y a H:i') }} - Brillio Administration</p>
        <p>Ce document est confidentiel et destine uniquement a un usage interne.</p>
    </div>
</body>
</html>
