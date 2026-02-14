@extends('reports.layout')

@section('content')
<table>
    <thead>
        <tr>
            <th>Nom</th>
            <th>Email</th>
            <th>Inscription</th>
            <th>Ville</th>
            <th>Profil (%)</th>
            <th>Mentor</th>
            <th>Statut</th>
        </tr>
    </thead>
    <tbody>
        @foreach($users as $user)
        @php
        $mentorship = $user->mentorshipsAsMentee->first();
        @endphp
        <tr>
            <td>{{ $user->name }}</td>
            <td>{{ $user->email }}</td>
            <td>{{ $user->created_at->format('d/m/Y') }}</td>
            <td>{{ $user->city ?? '-' }}</td>
            <td style="text-align: center;">{{ $user->profile_completion_percentage }}%</td>
            <td>{{ $mentorship ? $mentorship->mentor->name : 'Non assigné' }}</td>
            <td>
                <span
                    class="badge {{ $mentorship && $mentorship->status === 'active' ? 'status-completed' : 'status-pending' }}">
                    {{ $mentorship ? ucfirst($mentorship->status) : 'N/A' }}
                </span>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection