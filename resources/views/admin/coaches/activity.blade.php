@extends('layouts.admin')

@section('title', 'Activité des Coachs')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

    <!-- Page header -->
    <div class="sm:flex sm:justify-between sm:items-center mb-8">
        <div class="mb-4 sm:mb-0">
            <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Activité de Prise en Charge (Chat d'Orientation)</h1>
        </div>
    </div>

    <!-- Cards Stats Globales -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <!-- Card 1 -->
        <div class="flex flex-col bg-white shadow-sm rounded-xl border border-slate-200">
            <div class="p-5">
                <div class="text-slate-500 font-semibold uppercase text-xs mb-1">Total des prises en charge</div>
                <div class="flex items-start">
                    <div class="text-3xl font-bold text-slate-800 mr-2">{{ $stats['total_chats'] }}</div>
                </div>
            </div>
        </div>
        
        <!-- Card 2 -->
        <div class="flex flex-col bg-white shadow-sm rounded-xl border border-slate-200">
            <div class="p-5">
                <div class="text-slate-500 font-semibold uppercase text-xs mb-1">Temps Total Dédié (Mins)</div>
                <div class="flex items-start">
                    <div class="text-3xl font-bold text-emerald-600 mr-2">{{ number_format($stats['total_support_time'], 0, ',', ' ') }} min</div>
                </div>
            </div>
        </div>

        <!-- Card 3 -->
        <div class="flex flex-col bg-white shadow-sm rounded-xl border border-slate-200">
            <div class="p-5">
                <div class="text-slate-500 font-semibold uppercase text-xs mb-1">Temps Moyen / Chat</div>
                <div class="flex items-start">
                    <div class="text-3xl font-bold text-indigo-600 mr-2">{{ $stats['avg_support_time'] }} min</div>
                </div>
            </div>
        </div>

        <!-- Card 4 -->
        <div class="flex flex-col bg-white shadow-sm rounded-xl border border-slate-200">
            <div class="p-5">
                <div class="text-slate-500 font-semibold uppercase text-xs mb-1">Messages Échangés</div>
                <div class="flex items-start">
                    <div class="text-3xl font-bold text-amber-500 mr-2">{{ number_format($stats['total_messages'], 0, ',', ' ') }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres & Export -->
    <div class="bg-white rounded-lg shadow-sm border border-slate-200 mb-6 p-5">
        <form method="GET" action="{{ route('admin.coaches.activity') }}" class="flex flex-col md:flex-row gap-4 items-end">
            <!-- Intervenant -->
            <div class="w-full md:w-1/4">
                <label class="block text-sm font-medium mb-1">Intervenant :</label>
                <select name="coach_id" class="form-select w-full bg-slate-100 border-transparent focus:bg-white focus:border-slate-300">
                    <option value="">Tous les Coachs/Admins</option>
                    @foreach($coaches as $coach)
                        <option value="{{ $coach->id }}" {{ request('coach_id') == $coach->id ? 'selected' : '' }}>
                            {{ $coach->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <!-- Dates -->
            <div class="w-full md:w-1/4">
                <label class="block text-sm font-medium mb-1">Du :</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-input w-full bg-slate-100 border-transparent focus:bg-white focus:border-slate-300">
            </div>
            
            <div class="w-full md:w-1/4">
                <label class="block text-sm font-medium mb-1">Au :</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-input w-full bg-slate-100 border-transparent focus:bg-white focus:border-slate-300">
            </div>

            <!-- Actions -->
            <div class="flex gap-2 w-full md:w-auto">
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium transition-colors w-full md:w-auto text-center">
                    Filtrer
                </button>
                @if(request()->hasAny(['coach_id', 'date_from', 'date_to']))
                <a href="{{ route('admin.coaches.activity') }}" class="px-4 py-2 bg-white border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 text-sm font-medium transition-colors w-full md:w-auto text-center">
                    Effacer
                </a>
                @endif
                <button type="submit" name="export" value="csv" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 text-sm font-medium transition-colors w-full md:w-auto text-center flex items-center justify-center gap-2">
                    <i class="fas fa-file-csv"></i> CSV
                </button>
                <button type="submit" name="export" value="pdf" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm font-medium transition-colors w-full md:w-auto text-center flex items-center justify-center gap-2">
                    <i class="fas fa-file-pdf"></i> PDF
                </button>
            </div>
        </form>
    </div>

    <!-- Table des activités -->
    <div class="bg-white shadow-lg rounded-sm border border-slate-200">
        <header class="px-5 py-4 border-b border-slate-100">
            <h2 class="font-semibold text-slate-800">Détails des sessions ({!! $paginatedActivities->total() !!})</h2>
        </header>
        <div class="overflow-x-auto">
            <table class="table-auto w-full">
                <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3 whitespace-nowrap"><div class="font-semibold text-left">Date & Heure Début</div></th>
                        <th class="px-4 py-3 whitespace-nowrap"><div class="font-semibold text-left">Coach / Admin</div></th>
                        <th class="px-4 py-3 whitespace-nowrap"><div class="font-semibold text-left">Jeune</div></th>
                        <th class="px-4 py-3 whitespace-nowrap"><div class="font-semibold text-center">Statut</div></th>
                        <th class="px-4 py-3 whitespace-nowrap"><div class="font-semibold text-center">D. Totale Chat</div></th>
                        <th class="px-4 py-3 whitespace-nowrap"><div class="font-semibold text-center">D. Prise en Charge</div></th>
                        <th class="px-4 py-3 whitespace-nowrap"><div class="font-semibold text-center">Messages</div></th>
                        <th class="px-4 py-3 whitespace-nowrap"><div class="font-semibold text-center">Actions</div></th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-slate-200">
                    @forelse($paginatedActivities as $activity)
                    <tr>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="font-medium text-slate-800">
                                {{ $activity->started_at ? $activity->started_at->format('d/m/Y') : '-' }}
                            </div>
                            <div class="text-xs text-slate-500">
                                {{ $activity->started_at ? $activity->started_at->format('H:i') : '-' }}
                            </div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="font-medium text-indigo-500">{{ $activity->coach_name }}</div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="text-slate-800">{{ $activity->jeune_name }}</div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-center">
                            @if($activity->is_active)
                                <span class="inline-flex bg-amber-100 text-amber-600 rounded-full text-xs font-medium px-2 py-0.5">En cours</span>
                            @else
                                <span class="inline-flex bg-emerald-100 text-emerald-600 rounded-full text-xs font-medium px-2 py-0.5">Terminé</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-center">
                            {{ $activity->chat_duration_mins }} min
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-center font-semibold text-slate-800">
                            {{ $activity->support_duration_mins }} min
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-center">
                            <span class="inline-flex items-center justify-center bg-slate-100 text-slate-500 rounded-full text-xs font-semibold px-2 py-0.5">
                                {{ $activity->messages_count }}
                            </span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-center">
                            <a href="{{ route('admin.chat.show', $activity->id) }}" class="btn btn-sm bg-white border-slate-200 hover:border-slate-300 text-indigo-500" target="_blank" title="Consulter le chat">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-6 text-center text-slate-500">
                            Aucune activité trouvée pour cette sélection.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-8">
        {{ $paginatedActivities->appends(request()->query())->links() }}
    </div>

</div>
@endsection
