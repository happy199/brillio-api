<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Export Activité Coachs</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        h1 { color: #4338ca; font-size: 18px; margin-bottom: 5px; }
        .meta { margin-bottom: 20px; font-size: 11px; color: #666; }
        
        .stats-grid { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .stats-grid td { width: 25%; padding: 10px; border: 1px solid #e2e8f0; text-align: center; background-color: #f8fafc; }
        .stats-grid .stat-label { font-size: 10px; text-transform: uppercase; color: #64748b; margin-bottom: 5px; }
        .stats-grid .stat-value { font-size: 16px; font-weight: bold; color: #1e293b; }
        
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.data-table th, table.data-table td { border: 1px solid #cbd5e1; padding: 6px; text-align: left; }
        table.data-table th { background-color: #f1f5f9; font-weight: bold; font-size: 11px; color: #475569; }
        table.data-table td { font-size: 10px; }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .status-active { color: #d97706; font-weight: bold; }
        .status-done { color: #059669; font-weight: bold; }
        
        @page { margin: 1cm; }
    </style>
</head>
<body>

    <h1>Activité de Prise en Charge (Chat d'Orientation)</h1>
    
    <div class="meta">
        <strong>Filtres appliqués :</strong><br>
        Intervenant: {{ $filters['coach'] }}<br>
        Période: du {{ $filters['date_from'] }} au {{ $filters['date_to'] }}<br>
        Généré le: {{ now()->format('d/m/Y à H:i') }}
    </div>

    <!-- Stats Globales -->
    <table class="stats-grid">
        <tr>
            <td>
                <div class="stat-label">Total Prises en Charge</div>
                <div class="stat-value">{{ $stats['total_chats'] }}</div>
            </td>
            <td>
                <div class="stat-label">Temps Total Dédié</div>
                <div class="stat-value">{{ $stats['total_support_time'] }}</div>
            </td>
            <td>
                <div class="stat-label">Temps Moyen / Chat</div>
                <div class="stat-value">{{ $stats['avg_support_time'] }}</div>
            </td>
            <td>
                <div class="stat-label">Messages Échangés</div>
                <div class="stat-value">{{ number_format($stats['total_messages'], 0, ',', ' ') }}</div>
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th>Prise en charge le</th>
                <th>Coach / Admin</th>
                <th>Jeune</th>
                <th class="text-center">Statut</th>
                <th class="text-center">Msgs</th>
                <th class="text-center">Durée Chat</th>
                <th class="text-center">Durée PEC*</th>
            </tr>
        </thead>
        <tbody>
            @foreach($activities as $activity)
            <tr>
                <td>{{ $activity->started_at ? $activity->started_at->format('d/m/Y H:i') : '-' }}</td>
                <td>{{ $activity->coach_name }}</td>
                <td>{{ $activity->jeune_name }}</td>
                <td class="text-center">
                    @if($activity->is_active)
                        <span class="status-active">En cours</span>
                    @else
                        <span class="status-done">Terminé</span>
                    @endif
                </td>
                <td class="text-center">{{ $activity->messages_count }}</td>
                <td class="text-center">{{ $activity->chat_duration_formatted }}</td>
                <td class="text-center"><strong>{{ $activity->support_duration_formatted }}</strong></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div style="font-size: 9px; color: #64748b; margin-top: 10px;">
        *PEC : Prise en charge. (Écart entre le clic sur la prise en charge et la clôture du chat par le coach).
    </div>

</body>
</html>
