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
            <td class="label">Téléphone:</td>
            <td>{{ $user->phone ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Date de naissance:</td>
            <td>{{ $user->date_of_birth ? $user->date_of_birth->format('d/m/Y') : 'N/A' }}</td>
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

<div class="info-section" style="margin-top: 20px;">
    <h3>Parcours & Objectifs</h3>
    <table class="info-grid">
        <tr>
            <td class="label" width="30%">Situation actuelle:</td>
            <td>{{ $profileData['situation'] }}</td>
        </tr>
        <tr>
            <td class="label">Niveau d'études:</td>
            <td>{{ $profileData['education_level'] }}</td>
        </tr>
        <tr>
            <td class="label">Objectifs:</td>
            <td>{{ $profileData['goals'] }}</td>
        </tr>
        <tr>
            <td class="label">Centres d'intérêt:</td>
            <td>{{ $profileData['interests'] }}</td>
        </tr>
        <tr>
            <td class="label">Défis principaux:</td>
            <td>{{ $profileData['challenges'] }}</td>
        </tr>
    </table>
</div>

<div class="info-section" style="margin-top: 20px;">
    <h3>Activité IA & Ressources</h3>
    <table class="info-grid">
        <tr>
            <td class="label" width="30%">Conversations IA:</td>
            <td>{{ $aiStats['count'] }} conversations</td>
        </tr>
        <tr>
            <td class="label">Dernière activité IA:</td>
            <td>{{ $aiStats['last_activity'] ? \Carbon\Carbon::parse($aiStats['last_activity'])->format('d/m/Y H:i') :
                'Jamais' }}</td>
        </tr>
        <tr>
            <td class="label">Ressources consultées:</td>
            <td>{{ $resourcesViewedCount }} ressources</td>
        </tr>
    </table>
</div>

<div class="info-section" style="margin-top: 20px;">
    <h3>Personnalité (MBTI)</h3>
    @if($personalityTest)
    <div style="background-color: #f8f9fa; padding: 10px; border-radius: 5px; border: 1px solid #e2e8f0;">
        <h4 style="margin-top: 0; margin-bottom: 5px; color: #2d3748;">{{ $personalityTest->personality_type }} - {{
            $personalityTest->personality_label }}</h4>
        <p style="font-style: italic; color: #4a5568; font-size: 12px; margin: 0;">{{
            strip_tags($personalityTest->personality_description) }}</p>
    </div>
    @else
    <p>Le test de personnalité n'a pas encore été réalisé.</p>
    @endif
</div>

<div class="info-section" style="margin-top: 20px;">
    <h3>Mentors & Réseau</h3>

    <h4
        style="margin-bottom: 5px; font-size: 14px; color: #2d3748; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px;">
        Mentors Consultés ({{ $viewedMentors->count() }})</h4>
    @if($viewedMentors->count() > 0)
    <ul style="list-style-type: none; padding: 0; margin-bottom: 15px;">
        @foreach($viewedMentors as $view)
        <li style="margin-bottom: 3px; padding-bottom: 3px; font-size: 12px;">
            <strong>{{ $view->mentor->name }}</strong>
            <span style="color: #718096;">- Vu le {{ $view->viewed_at->format('d/m/Y H:i') }}</span>
        </li>
        @endforeach
    </ul>
    @else
    <p style="color: #718096; font-style: italic; margin-bottom: 15px; font-size: 12px;">Aucun mentor consulté
        récemment.</p>
    @endif

    <h4
        style="margin-bottom: 5px; font-size: 14px; color: #2d3748; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px;">
        Mentorats ({{ $activeMentorships->count() }})</h4>
    @if($activeMentorships->count() > 0)
    <ul style="list-style-type: none; padding: 0;">
        @foreach($activeMentorships as $mentorship)
        <li style="margin-bottom: 3px; padding-bottom: 3px; font-size: 12px;">
            <strong>{{ $mentorship->mentor->name }}</strong>
            <span style="color: #718096;">- {{ $mentorship->translated_status }} (depuis {{
                $mentorship->created_at->format('d/m/Y') }})</span>
        </li>
        @endforeach
    </ul>
    @else
    <p style="color: #718096; font-style: italic; font-size: 12px;">Aucun mentorat actif.</p>
    @endif
</div>

<div class="info-section" style="margin-top: 30px; page-break-before: always;">
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
@endsection