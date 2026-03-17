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
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium flex-shrink-0 transition-colors">
                Filtrer
            </button>
            @if(request('search') || request('status'))
                <a href="{{ route('admin.audits.crons') }}" class="px-4 py-2 bg-white border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 text-sm font-medium flex-shrink-0 transition-colors">Effacer</a>
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
                                    <div class="inline-flex font-semibold bg-emerald-100 text-emerald-700 rounded-full text-center px-3 py-1 text-xs">Succès</div>
                                @else
                                    <div class="inline-flex font-semibold bg-rose-100 text-rose-700 rounded-full text-center px-3 py-1 text-xs">Échec</div>
                                @endif
                            </div>
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            @if($log->output || $log->status === 'failed')
                            <div class="text-center">
                                <button type="button" class="inline-flex items-center px-3 py-1.5 border border-slate-200 text-sm font-medium rounded-lg text-slate-600 bg-white hover:bg-slate-50 hover:border-slate-300 transition-all shadow-sm" onclick="document.getElementById('modal-cron-{{ $log->id }}').classList.remove('hidden')">
                                    <svg class="w-4 h-4 mr-1.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Logs
                                </button>
                            </div>

                            <!-- Modal -->
                            <div id="modal-cron-{{ $log->id }}" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 hidden">
                                <div class="bg-white rounded-xl shadow-2xl overflow-hidden w-full max-w-4xl max-h-[90vh] flex flex-col border border-slate-200">
                                    <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                                        <div class="flex items-center gap-2">
                                            <div class="w-2 h-2 rounded-full {{ $log->status === 'success' ? 'bg-emerald-500' : 'bg-rose-500' }}"></div>
                                            <div class="font-bold text-slate-800">Détails de l'exécution : {{ $log->command }}</div>
                                        </div>
                                        <button class="w-8 h-8 flex items-center justify-center rounded-full text-slate-400 hover:bg-slate-200 transition-colors" onclick="document.getElementById('modal-cron-{{ $log->id }}').classList.add('hidden')">&times;</button>
                                    </div>
                                    <div class="px-6 py-6 overflow-y-auto flex-1 bg-slate-950">
                                        @if($log->output || $log->status === 'failed')
                                        <div class="font-mono text-sm leading-relaxed text-slate-300 whitespace-pre-wrap">
                                            <span class="text-slate-500 select-none">[{{ $log->run_at->format('Y-m-d H:i:s') }}]</span> <span class="text-indigo-400">STARTING</span> {{ $log->command }}...
                                            <br>
                                            @if($log->output)
                                                {{ $log->output }}
                                            @elseif($log->status === 'failed')
                                                <span class="text-rose-500">ERROR: La tâche s'est terminée prématurément sans sortie standard.</span>
                                            @endif
                                            <br>
                                            <span class="text-slate-500 select-none">[{{ now()->format('Y-m-d H:i:s') }}]</span> <span class="{{ $log->status === 'success' ? 'text-emerald-400' : 'text-rose-400' }}">FINISHED</span> ({{ number_format($log->duration, 2) }}s)
                                        </div>
                                        @else
                                        <div class="text-center py-12 text-slate-500 italic">
                                            Aucune sortie console enregistrée pour cette tâche.
                                        </div>
                                        @endif
                                    </div>
                                    <div class="px-6 py-4 border-t border-slate-100 flex justify-end bg-slate-50">
                                        <button class="px-4 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 rounded-lg text-sm font-bold transition-all shadow-sm" onclick="document.getElementById('modal-cron-{{ $log->id }}').classList.add('hidden')">Fermer</button>
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
