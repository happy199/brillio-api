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
            <th>Métrique</th>
            <th>Valeur</th>
            <th>Description</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Invitations envoyées</td>
            <td>{{ $data['totalInvited'] }}</td>
            <td>Nombre total d'invitations créées pour rejoindre l'organisation.</td>
        </tr>
        <tr>
            <td>Jeunes inscrits</td>
            <td>{{ $data['totalRegistered'] }}</td>
            <td>Utilisateurs ayant finalisé leur inscription via un lien d'invitation.</td>
        </tr>
        <tr>
            <td>Utilisateurs actifs</td>
            <td>{{ $data['activeUsers'] }}</td>
            <td>Jeunes s'étant connectés au cours des 30 derniers jours.</td>
        </tr>
        <tr>
            <td>Sessions réalisées</td>
            <td>{{ $data['sessionsCount'] }}</td>
            <td>Nombre total de séances de mentorat marquées comme terminées.</td>
        </tr>
        <tr>
            <td>Total Mentors</td>
            <td>{{ $data['totalMentors'] }}</td>
            <td>Total des mentors (internes et externes) liés à votre activité.</td>
        </tr>
        <tr>
            <td>Mentors Internes</td>
            <td>{{ $data['internalMentors'] }}</td>
            <td>Mentors appartenant directement à votre organisation.</td>
        </tr>
        <tr>
            <td>Mentors Externes</td>
            <td>{{ $data['externalMentors'] }}</td>
            <td>Mentors externes accompagnant vos bénéficiaires.</td>
        </tr>
    </tbody>
</table>

<div class="info-section">
    <h3>Démographie</h3>
    <div style="width: 100%;">
        <div style="width: 48%; display: inline-block; vertical-align: top;">
            <h4>Top Villes</h4>
            <table>
                <thead>
                    <tr>
                        <th>Ville</th>
                        <th>Nombre</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['cityStats'] as $city)
                    <tr>
                        <td>{{ $city->city }}</td>
                        <td>{{ $city->count }}</td>
                    </tr>
                    @endforeach
                    @if($data['cityStats']->isEmpty())
                    <tr>
                        <td colspan="2">Aucune donnée disponible</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
        <div style="width: 4%; display: inline-block;"></div>
        <div style="width: 48%; display: inline-block; vertical-align: top;">
            <h4>Tranches d'âge</h4>
            <table>
                <thead>
                    <tr>
                        <th>Tranche</th>
                        <th>Nombre</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['ageStats'] as $range => $count)
                    <tr>
                        <td>{{ $range }} ans</td>
                        <td>{{ $count }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="info-section">
    <h3>Répartition des documents</h3>
    <table>
        <thead>
            <tr>
                <th>Type de document</th>
                <th>Nombre</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['documentStats'] as $doc)
            <tr>
                <td>{{ $doc->type }}</td>
                <td>{{ $doc->count }}</td>
            </tr>
            @endforeach
            @if($data['documentStats']->isEmpty())
            <tr>
                <td colspan="2">Aucun document partagé</td>
            </tr>
            @endif
        </tbody>
    </table>
</div>
@endsection