@extends('reports.layout')

@section('content')
<div class="info-section">
    <h3>Période du rapport</h3>
    <p>
        @if($startDate && $endDate)
        Du {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} au {{
        \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
        @elseif($startDate)
        À partir du {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }}
        @elseif($endDate)
        Jusqu'au {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
        @else
        Toutes les données
        @endif
    </p>
</div>

<table>
    <thead>
        <tr>
            <th width="15%">Date & Heure</th>
            <th width="15%">Jeune</th>
            <th width="15%">Mentor</th>
            <th width="10%">Statut</th>
            <th width="45%">Compte-rendu détaillé</th>
        </tr>
    </thead>
    <tbody>
        @foreach($sessions as $session)
        <tr>
            <td>{{ $session->scheduled_at->format('d/m/Y H:i') }}</td>
            <td>{{ $session->mentees->first()?->name ?? 'N/A' }}</td>
            <td>{{ $session->mentor->name }}</td>
            <td>
                <span
                    class="badge {{ $session->status === 'completed' ? 'status-completed' : ($session->status === 'cancelled' ? 'status-cancelled' : 'status-pending') }}">
                    {{ $session->translated_status }}
                </span>
            </td>
            <td>
                @if($session->report_content)
                <div style="margin-bottom: 5px;"><strong>Progrès:</strong> {{ $session->report_content['progress'] ??
                    'N/A' }}</div>
                <div style="margin-bottom: 5px;"><strong>Obstacles:</strong> {{ $session->report_content['obstacles'] ??
                    'N/A' }}</div>
                <div><strong>Objectifs SMART:</strong> {{ $session->report_content['smart_goals'] ?? 'N/A' }}</div>
                @else
                -
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection