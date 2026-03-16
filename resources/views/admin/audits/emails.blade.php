@extends('layouts.admin')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
    
    <!-- Page header -->
    <div class="sm:flex sm:justify-between sm:items-center mb-8">
        <div class="mb-4 sm:mb-0">
            <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Audit des Emails Envoyés</h1>
        </div>
    </div>

    <!-- Filtres -->
    <div class="bg-white rounded-lg shadow-sm mb-6 p-4">
        <form method="GET" action="{{ route('admin.audits.emails') }}" class="flex flex-col sm:flex-row gap-4">
            <div class="flex-grow">
                <input type="text" name="search" placeholder="Rechercher par adresse email ou sujet" value="{{ request('search') }}" class="w-full form-input bg-slate-100 border-transparent focus:bg-white focus:border-slate-300">
            </div>
            <button type="submit" class="btn bg-indigo-500 hover:bg-indigo-600 text-white flex-shrink-0">
                Rechercher
            </button>
            @if(request('search'))
                <a href="{{ route('admin.audits.emails') }}" class="btn border-slate-200 hover:border-slate-300 text-slate-600 flex-shrink-0">Effacer</a>
            @endif
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white shadow-lg rounded-sm border border-slate-200">
        <div class="overflow-x-auto">
            <table class="table-auto w-full">
                <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-b border-slate-200">
                    <tr>
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap"><div class="font-semibold text-left">Date d'Envoi</div></th>
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap"><div class="font-semibold text-left">Destinataire(s)</div></th>
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap"><div class="font-semibold text-left">Sujet</div></th>
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap"><div class="font-semibold text-center">Action</div></th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-slate-200">
                    @forelse($logs as $log)
                    <tr>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="text-left font-medium text-slate-800">{{ $log->sent_at->format('d/m/Y H:i') }}</div>
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="text-left">{{ $log->to }}</div>
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 min-w-[200px]">
                            <div class="text-left font-medium text-slate-800 truncate" title="{{ $log->subject }}">{{ \Illuminate\Support\Str::limit($log->subject, 60) }}</div>
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="text-center">
                                <!-- Trigger Modal -->
                                <button type="button" class="btn btn-sm bg-indigo-50 hover:bg-indigo-100 text-indigo-500" onclick="document.getElementById('modal-email-{{ $log->id }}').classList.remove('hidden')">
                                    Voir contenu
                                </button>
                            </div>

                            <!-- Modal -->
                            <div id="modal-email-{{ $log->id }}" class="fixed inset-0 z-50 bg-slate-900 bg-opacity-30 flex items-center justify-center p-4 hidden">
                                <div class="bg-white rounded shadow-lg overflow-hidden w-full max-w-4xl max-h-[90vh] flex flex-col">
                                    <div class="px-5 py-3 border-b border-slate-200 flex justify-between items-center">
                                        <div class="font-semibold text-slate-800">Email à {{ $log->to }}</div>
                                        <button class="text-slate-400 hover:text-slate-500" onclick="document.getElementById('modal-email-{{ $log->id }}').classList.add('hidden')">&times;</button>
                                    </div>
                                    <div class="px-5 py-4 overflow-y-auto flex-1 bg-slate-50">
                                        <div class="mb-4">
                                            <strong>Sujet :</strong> {{ $log->subject }}<br>
                                            <strong>Date :</strong> {{ $log->sent_at->format('d/m/Y H:i:s') }}
                                        </div>
                                        <div class="p-4 bg-white border border-slate-200 rounded shadow-sm overflow-x-hidden">
                                            {!! $log->body !!}
                                        </div>
                                    </div>
                                    <div class="px-5 py-3 border-t border-slate-200 flex justify-end">
                                        <button class="btn-sm border-slate-200 hover:border-slate-300 text-slate-600" onclick="document.getElementById('modal-email-{{ $log->id }}').classList.add('hidden')">Fermer</button>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-2 py-4 text-center text-slate-500">Aucun email enregistré dans ces dates.</td>
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
