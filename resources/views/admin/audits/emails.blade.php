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
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium flex-shrink-0 transition-colors">
                Rechercher
            </button>
            @if(request('search'))
                <a href="{{ route('admin.audits.emails') }}" class="px-4 py-2 bg-white border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 text-sm font-medium flex-shrink-0 transition-colors">Effacer</a>
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
                                <button type="button" class="inline-flex items-center px-3 py-1.5 border border-slate-200 text-sm font-medium rounded-lg text-slate-600 bg-white hover:bg-slate-50 hover:border-slate-300 transition-all shadow-sm" onclick="document.getElementById('modal-email-{{ $log->id }}').classList.remove('hidden')">
                                    <svg class="w-4 h-4 mr-1.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    Explorer
                                </button>
                            </div>

                            <!-- Modal -->
                            <div id="modal-email-{{ $log->id }}" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 hidden">
                                <div class="bg-white rounded-xl shadow-2xl overflow-hidden w-full max-w-5xl max-h-[95vh] flex flex-col border border-slate-200">
                                    <!-- Modal Header -->
                                    <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <h3 class="font-bold text-slate-900 leading-tight">Aperçu du message</h3>
                                                <p class="text-xs text-slate-500">Log ID: #{{ $log->id }} • Automatisé par Brillio</p>
                                            </div>
                                        </div>
                                        <button class="w-8 h-8 flex items-center justify-center rounded-full text-slate-400 hover:bg-slate-200 transition-colors" onclick="document.getElementById('modal-email-{{ $log->id }}').classList.add('hidden')">&times;</button>
                                    </div>

                                    <!-- Email Header Details -->
                                    <div class="px-8 py-6 border-b border-slate-100 bg-white">
                                        <h2 class="text-xl font-bold text-slate-800 mb-4">{{ $log->subject }}</h2>
                                        
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                            <div class="flex gap-2">
                                                <span class="text-slate-400 font-medium min-w-[60px]">De :</span>
                                                <span class="text-slate-700">Brillio <span class="text-slate-400">&lt;contact@brillio.africa&gt;</span></span>
                                            </div>
                                            <div class="flex gap-2">
                                                <span class="text-slate-400 font-medium min-w-[60px]">Date :</span>
                                                <span class="text-slate-700 italic">{{ $log->sent_at->translatedFormat('d F Y \à H:i:s') }}</span>
                                            </div>
                                            <div class="flex gap-2 col-span-full">
                                                <span class="text-slate-400 font-medium min-w-[60px]">À :</span>
                                                <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded border border-slate-200">{{ $log->to }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Email Content Area -->
                                    <div class="flex-1 overflow-hidden flex flex-col bg-slate-100 p-4 sm:p-8">
                                        <div class="bg-white rounded-lg shadow-lg border border-slate-200 overflow-hidden flex-1 flex flex-col mx-auto w-full max-w-[700px]">
                                            <iframe 
                                                srcdoc="{{ $log->body }}" 
                                                class="w-full h-full border-none"
                                                onload="this.style.height = '100%';"
                                            ></iframe>
                                        </div>
                                    </div>

                                    <!-- Modal Footer -->
                                    <div class="px-6 py-4 border-t border-slate-100 flex justify-between items-center bg-slate-50">
                                        <p class="text-[10px] text-slate-400 uppercase tracking-widest font-semibold">Audit Système d'Expédition</p>
                                        <button class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-sm font-bold transition-all shadow-md" onclick="document.getElementById('modal-email-{{ $log->id }}').classList.add('hidden')">Fermer l'aperçu</button>
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
