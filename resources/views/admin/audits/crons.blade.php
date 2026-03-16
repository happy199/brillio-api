@extends('layouts.admin')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
    
    <!-- Page header -->
    <div class="sm:flex sm:justify-between sm:items-center mb-8">
        <div class="mb-4 sm:mb-0">
            <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Audit des Tâches Planifiées (CRON)</h1>
        </div>
    </div>

    <!-- Filtres -->
    <div class="bg-white rounded-lg shadow-sm mb-6 p-4">
        <form method="GET" action="{{ route('admin.audits.crons') }}" class="flex flex-col sm:flex-row gap-4">
            <div class="flex-grow">
                <input type="text" name="search" placeholder="Rechercher par commande (ex: messages:send-unread-reminders)" value="{{ request('search') }}" class="w-full form-input bg-slate-100 border-transparent focus:bg-white focus:border-slate-300">
            </div>
            <div>
                <select name="status" class="form-select bg-slate-100 border-transparent focus:bg-white focus:border-slate-300">
                    <option value="">Tous les statuts</option>
                    <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Succès</option>
                    <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Échec</option>
                </select>
            </div>
            <button type="submit" class="btn bg-indigo-500 hover:bg-indigo-600 text-white flex-shrink-0">
                Filtrer
            </button>
            @if(request('search') || request('status'))
                <a href="{{ route('admin.audits.crons') }}" class="btn border-slate-200 hover:border-slate-300 text-slate-600 flex-shrink-0">Effacer</a>
            @endif
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white shadow-lg rounded-sm border border-slate-200">
        <div class="overflow-x-auto">
            <table class="table-auto w-full">
                <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-b border-slate-200">
                    <tr>
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap"><div class="font-semibold text-left">Date d'Exécution</div></th>
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap"><div class="font-semibold text-left">Commande</div></th>
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap"><div class="font-semibold text-center">Durée</div></th>
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap"><div class="font-semibold text-center">Statut</div></th>
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap"><div class="font-semibold text-center">Logs</div></th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-slate-200">
                    @forelse($logs as $log)
                    <tr>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="text-left font-medium text-slate-800">{{ $log->run_at->format('d/m/Y H:i:s') }}</div>
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="text-left font-mono font-medium text-slate-800">{{ $log->command }}</div>
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="text-center font-medium">{{ number_format($log->duration, 2) }} s</div>
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="text-center">
                                @if($log->status === 'success')
                                    <div class="inline-flex font-medium bg-emerald-100 text-emerald-600 rounded-full text-center px-2.5 py-0.5">Succès</div>
                                @else
                                    <div class="inline-flex font-medium bg-rose-100 text-rose-600 rounded-full text-center px-2.5 py-0.5">Échec</div>
                                @endif
                            </div>
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            @if($log->output)
                            <div class="text-center">
                                <button type="button" class="btn btn-sm bg-slate-50 border-slate-200 hover:bg-slate-100 text-slate-600" onclick="document.getElementById('modal-cron-{{ $log->id }}').classList.remove('hidden')">
                                    Voir
                                </button>
                            </div>

                            <!-- Modal -->
                            <div id="modal-cron-{{ $log->id }}" class="fixed inset-0 z-50 bg-slate-900 bg-opacity-30 flex items-center justify-center p-4 hidden">
                                <div class="bg-white rounded shadow-lg overflow-hidden w-full max-w-4xl max-h-[90vh] flex flex-col">
                                    <div class="px-5 py-3 border-b border-slate-200 flex justify-between items-center">
                                        <div class="font-semibold text-slate-800">Logs de la tâche ({{ $log->command }})</div>
                                        <button class="text-slate-400 hover:text-slate-500" onclick="document.getElementById('modal-cron-{{ $log->id }}').classList.add('hidden')">&times;</button>
                                    </div>
                                    <div class="px-5 py-4 overflow-y-auto flex-1">
                                        <div class="p-4 bg-slate-900 border border-slate-700 rounded shadow-sm overflow-x-auto text-emerald-400 font-mono text-sm whitespace-pre-wrap">
                                            {{ $log->output }}
                                        </div>
                                    </div>
                                    <div class="px-5 py-3 border-t border-slate-200 flex justify-end">
                                        <button class="btn-sm border-slate-200 hover:border-slate-300 text-slate-600" onclick="document.getElementById('modal-cron-{{ $log->id }}').classList.add('hidden')">Fermer</button>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-2 py-4 text-center text-slate-500">Aucune exécution enregistrée.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-8">
        {{ $logs->links() }}
    </div>
</div>
@endsection
