@extends('reports.layout')

@section('content')
<div class="info-section">
    <h3>Informations Personnelles</h3>
    <table class="info-grid">
        <tr>
            <td class="label" width="30%">Nom complet:</td>
            <td>{{ $user->name }}</td>
        </tr>
        <tr>
            <td class="label">Email:</td>
            <td>{{ $user->email }}</td>
        </tr>
        <tr>
            <td class="label">Ville:</td>
            <td>{{ $user->city ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Inscription:</td>
            <td>{{ $user->created_at->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td class="label">Complétion du profil:</td>
            <td>{{ $user->profile_completion_percentage }}%</td>
        </tr>
    </table>
</div>

@if($user->jeuneProfile)
<div class="info-section" style="margin-top: 20px;">
    <h3>Profil & Objectifs</h3>
    <p><strong>Bio:</strong> {{ $user->jeuneProfile->bio ?? 'Non renseigné' }}</p>
    <p><strong>Spécialité souhaitée:</strong> {{ $user->jeuneProfile->desired_specialization ?? 'N/A' }}</p>
</div>
@endif

<div class="info-section" style="margin-top: 30px;">
    <h3>Historique des Séances de Mentorat</h3>
    @if($sessions->count() > 0)
    <table>
        <thead>
            <tr>
                <th width="15%">Date</th>
                <th width="15%">Mentor</th>
                <th width="10%">Statut</th>
                <th width="60%">Compte-rendu détaillé</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sessions as $session)
            <tr>
                <td>{{ $session->scheduled_at->format('d/m/Y H:i') }}</td>
                <td>{{ $session->mentor->name }}</td>
                <td>
                    <span
                        class="badge {{ $session->status === 'completed' ? 'status-completed' : ($session->status === 'cancelled' ? 'status-cancelled' : 'status-pending') }}">
                        {{ $session->translated_status }}
                    </span>
                </td>
                <td>
                    @if($session->report_content)
                    <div style="margin-bottom: 2px;"><strong>Progrès:</strong> {{ $session->report_content['progress']
                        ?? '-' }}</div>
                    <div style="margin-bottom: 2px;"><strong>Obstacles:</strong> {{
                        $session->report_content['obstacles'] ?? '-' }}</div>
                    <div><strong>Objectifs SMART:</strong> {{ $session->report_content['smart_goals'] ?? '-' }}</div>
                    @else
                    -
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p>Aucune séance enregistrée pour le moment.</p>
    @endif
</div>
@endsectionama