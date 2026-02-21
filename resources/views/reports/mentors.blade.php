@extends('reports.layout')

@section('content')
<p style="margin-bottom: 20px;">
    Liste complète des mentors (internes et externes) accompagnant les bénéficiaires de {{ $organization->name }}.
</p>

<table>
    <thead>
        <tr>
            <th>Nom</th>
            <th>Email</th>
            <th>Type</th>
            <th>Ville</th>
            <th>Entreprise / Poste</th>
        </tr>
    </thead>
    <tbody>
        @foreach($mentors as $mentor)
        <tr>
            <td><strong>{{ $mentor->name }}</strong></td>
            <td>{{ $mentor->email }}</td>
            <td>
                <span class="badge {{ $mentor->is_internal ? 'status-completed' : 'status-pending' }}">
                    {{ $mentor->is_internal ? 'Interne' : 'Externe' }}
                </span>
            </td>
            <td>{{ $mentor->city ?? '-' }}</td>
            <td>
                {{ $mentor->mentorProfile->company ?? '-' }}<br>
                <small style="color: #666;">{{ $mentor->mentorProfile->job_title ?? '-' }}</small>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection