@extends('reports.layout')

@section('content')
<div class="info-section">
    <h3>Informations Professionnelles</h3>
    <table class="info-grid">
        <tr>
            <td class="label" width="30%">Nom complet:</td>
            <td>{{ $mentor->name }}</td>
        </tr>
        <tr>
            <td class="label">Email:</td>
            <td>{{ $mentor->email }}</td>
        </tr>
        <tr>
            <td class="label">Poste actuel:</td>
            <td>{{ $mentor->mentorProfile->current_position ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Entreprise:</td>
            <td>{{ $mentor->mentorProfile->current_company ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Spécialisation:</td>
            <td>{{ $mentor->mentorProfile->specialization_label ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Expérience:</td>
            <td>{{ $mentor->mentorProfile->years_of_experience ?? 'N/A' }} ans</td>
        </tr>
        <tr>
            <td class="label">Ville:</td>
            <td>{{ $mentor->city ?? 'N/A' }}</td>
        </tr>
    </table>
</div>

<div class="info-section" style="margin-top: 20px;">
    <h3>Biographie</h3>
    <div
        style="background-color: #f8f9fa; padding: 15px; border-radius: 5px; border: 1px solid #e2e8f0; font-style: italic; color: #4a5568;">
        {{ $mentor->mentorProfile->bio ?? 'Pas de biographie renseignée.' }}
    </div>
</div>

@if($mentor->mentorProfile && $mentor->mentorProfile->roadmapSteps->count() > 0)
<div class="info-section" style="margin-top: 20px;">
    <h3>Parcours & Étapes Clés</h3>
    <ul style="list-style-type: none; padding: 0;">
        @foreach($mentor->mentorProfile->roadmapSteps as $step)
        <li style="margin-bottom: 10px; padding-left: 15px; border-left: 3px solid #4a5568;">
            <div style="font-weight: bold; color: #2d3748;">{{ $step->title }}</div>
            <div style="font-size: 12px; color: #718096;">{{ $step->description }}</div>
        </li>
        @endforeach
    </ul>
</div>
@endif

<div class="info-section" style="margin-top: 30px; page-break-before: always;">
    <h3>Récapitulatif de l'Impact ({{ $organization->name }})</h3>
    <p style="font-size: 13px; color: #4a5568; margin-bottom: 15px;">
        Ce rapport présente les interactions de <strong>{{ $mentor->name }}</strong> avec les jeunes parrainés par votre
        organisation.
    </p>

    <table class="info-grid">
        <tr>
            <td class="label" width="40%">Jeunes accompagnés:</td>
            <td style="font-weight: bold;">{{ $mentor->mentorshipsAsMentor()->whereHas('mentee', function($q) use
                ($organization) { $q->whereHas('organizations', function($sq) use ($organization) {
                $sq->where('organizations.id', $organization->id); }); })->count() }}</td>
        </tr>
        <tr>
            <td class="label">Total des séances effectuées:</td>
            <td style="font-weight: bold;">{{ \App\Models\MentoringSession::where('mentor_id',
                $mentor->id)->whereHas('mentees', function($q) use ($organization) { $q->whereHas('organizations',
                function($sq) use ($organization) { $sq->where('organizations.id', $organization->id); }); })->count()
                }}</td>
        </tr>
    </table>
</div>

<div
    style="margin-top: 40px; text-align: center; color: #a0aec0; font-size: 10px; border-top: 1px solid #edf2f7; padding-top: 10px;">
    Généré par {{ config('app.name') }} pour {{ $organization->name }} le {{ now()->format('d/m/Y H:i') }}
</div>
@endsection