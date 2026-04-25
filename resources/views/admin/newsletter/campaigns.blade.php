@extends('layouts.admin')

@section('title', 'Historique des Campagnes Email')

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Marketing & Newsletters</h1>
                <p class="text-gray-600 mt-1">Gérer les campagnes ponctuelles et récurrentes</p>
            </div>
            <a href="{{ route('admin.newsletter.index') }}"
                class="px-6 py-3 bg-gray-600 text-white font-bold rounded-lg hover:bg-gray-700">
                <i class="fas fa-arrow-left mr-2"></i>Retour aux abonnés
            </a>
        </div>

        <!-- Campagnes Récurrentes Actives -->
        @php $recurringCampaigns = $campaigns->where('is_recurring', true); @endphp
        @if($recurringCampaigns->count() > 0)
        <div class="bg-purple-50 border border-purple-200 rounded-xl p-6">
            <h2 class="text-lg font-bold text-purple-900 mb-4 flex items-center">
                <i class="fas fa-redo-alt mr-2"></i> Campagnes Récurrentes Actives
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($recurringCampaigns as $rc)
                <div class="bg-white rounded-lg shadow-sm border p-4">
                    <div class="flex justify-between items-start mb-3">
                        <span class="px-2 py-1 text-[10px] font-bold uppercase rounded {{ $rc->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700' }}">
                            {{ $rc->status === 'active' ? 'En cours' : 'En pause' }}
                        </span>
                        <div class="flex space-x-2">
                            <form action="{{ route('admin.newsletter.campaigns.toggle', $rc->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-gray-400 hover:text-{{ $rc->status === 'active' ? 'orange' : 'green' }}-600 transition" title="{{ $rc->status === 'active' ? 'Suspendre' : 'Relancer' }}">
                                    <i class="fas fa-{{ $rc->status === 'active' ? 'pause-circle' : 'play-circle' }} text-xl"></i>
                                </button>
                            </form>
                            <form action="{{ route('admin.newsletter.campaigns.destroy', $rc->id) }}" method="POST" class="inline delete-campaign-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-gray-400 hover:text-red-600 transition" title="Supprimer la planification">
                                    <i class="fas fa-times-circle text-xl"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    <h3 class="font-bold text-gray-900 truncate">{{ $rc->subject }}</h3>
                    <div class="mt-3 space-y-1 text-sm text-gray-600">
                        <p><i class="fas fa-clock mr-2 w-4"></i>{{ ucfirst($rc->frequency) }}</p>
                        <p><i class="fas fa-calendar-alt mr-2 w-4"></i>Jusqu'au {{ $rc->end_date->format('d/m/Y') }}</p>
                        <p><i class="fas fa-paper-plane mr-2 w-4"></i>Prochain : {{ $rc->next_run_at?->format('d/m/Y H:i') ?? 'N/A' }}</p>
                    </div>
                    <div class="mt-4 pt-4 border-t flex items-center text-xs text-gray-500">
                        <span>{{ $rc->children()->count() }} envois effectués</span>
                        <button type="button" 
                            data-id="{{ $rc->id }}" 
                            class="ml-auto show-campaign-btn text-purple-600 hover:underline font-medium">
                            Voir les détails
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Historique Global -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-4 border-b bg-gray-50">
                <h2 class="font-bold text-gray-800">Historique des envois récents</h2>
            </div>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sujet</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Destinataires</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase text-center">Succès / Échecs</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($campaigns->where('is_recurring', false) as $campaign)
                        <tr>
                            <td class="px-6 py-4">
                                @if($campaign->status == 'sent')
                                    <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">Envoyé</span>
                                @elseif($campaign->status == 'queued' || $campaign->status == 'sending')
                                    <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">En cours</span>
                                @elseif($campaign->status == 'partial')
                                    <span class="px-2 py-1 text-xs bg-orange-100 text-orange-800 rounded-full">Partiel</span>
                                @else
                                    <span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded-full">{{ $campaign->status }}</span>
                                @endif
                                
                                @if($campaign->parent_id)
                                    <span class="block mt-1 text-[10px] text-purple-600 font-medium italic">
                                        <i class="fas fa-redo mr-1"></i>Auto
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <div class="font-medium inline-flex items-center">
                                    {{ $campaign->subject }}
                                    <button type="button" data-id="{{ $campaign->id }}" class="show-campaign-btn ml-2 text-gray-400 hover:text-blue-500">
                                        <i class="fas fa-eye text-xs"></i>
                                    </button>
                                </div>
                                <div class="text-xs text-gray-500 truncate max-w-xs">{{ Str::limit(strip_tags($campaign->body), 60) }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $campaign->recipients_count }}
                            </td>
                            <td class="px-6 py-4 text-sm text-center">
                                <span class="text-green-600 font-bold">{{ $campaign->sent_count }}</span>
                                <span class="text-gray-300 mx-1">|</span>
                                <span class="text-red-600">{{ $campaign->failed_count }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $campaign->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <form action="{{ route('admin.newsletter.campaigns.destroy', $campaign->id) }}" method="POST" class="inline delete-campaign-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-gray-400 hover:text-red-600" title="Supprimer de l'historique">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500 italic">
                                Aucun envoi individuel enregistré.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $campaigns->links() }}
        </div>
    </div>

    <!-- Detail Modal -->
    <div id="detailModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl max-w-4xl w-full max-h-[90vh] overflow-hidden flex flex-col shadow-2xl">
            <div class="p-6 border-b bg-gray-50 flex justify-between items-center">
                <div class="flex items-center">
                    <div id="modalTypeIcon" class="p-2 rounded-lg mr-3">
                        <i class="fas fa-envelope-open-text text-xl"></i>
                    </div>
                    <div>
                        <h3 id="modalSubject" class="text-xl font-bold text-gray-900">Détails de la campagne</h3>
                        <p id="modalMeta" class="text-sm text-gray-500"></p>
                    </div>
                </div>
                <button type="button" class="close-detail-modal text-gray-400 hover:text-gray-600 transition">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
            
            <div class="flex-1 overflow-y-auto p-6 space-y-6">
                <!-- Recurrence Info if applicable -->
                <div id="modalRecurrenceBox" class="hidden p-4 bg-purple-50 border border-purple-100 rounded-lg">
                    <h4 class="text-purple-900 font-bold text-sm mb-2 flex items-center">
                        <i class="fas fa-redo-alt mr-2"></i> Planification Récurrente
                    </h4>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-xs">
                        <div>
                            <span class="text-purple-600 block">Fréquence</span>
                            <span id="modalFreq" class="font-bold"></span>
                        </div>
                        <div>
                            <span class="text-purple-600 block">Début</span>
                            <span id="modalStart" class="font-bold"></span>
                        </div>
                        <div>
                            <span class="text-purple-600 block">Fin</span>
                            <span id="modalEnd" class="font-bold"></span>
                        </div>
                        <div>
                            <span class="text-purple-600 block">Prochain envoi</span>
                            <span id="modalNext" class="font-bold text-purple-700"></span>
                        </div>
                    </div>
                </div>

                <!-- Content Preview -->
                <div class="border rounded-lg overflow-hidden">
                    <div class="bg-gray-100 px-4 py-2 border-b text-xs font-bold text-gray-600 flex justify-between items-center">
                        <span>APERÇU DU MESSAGE</span>
                        <span id="modalDate" class="font-normal italic"></span>
                    </div>
                    <div id="modalBody" class="p-6 bg-white prose max-w-none min-h-[200px]">
                        <!-- Content will be injected here -->
                    </div>
                </div>

                <!-- Attachments -->
                <div id="modalAttachmentsBox" class="hidden">
                    <h4 class="text-sm font-bold text-gray-700 mb-3 flex items-center">
                        <i class="fas fa-paperclip mr-2"></i> Pièces jointes
                    </h4>
                    <div id="modalAttachmentsList" class="flex flex-wrap gap-2">
                        <!-- Files will be injected here -->
                    </div>
                </div>
            </div>

            <div class="p-6 border-t bg-gray-50 flex justify-end">
                <button type="button" class="close-detail-modal px-6 py-2 bg-white border border-gray-300 text-gray-700 font-bold rounded-lg hover:bg-gray-100 transition">
                    Fermer
                </button>
            </div>
        </div>
    </div>

    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        document.addEventListener('DOMContentLoaded', function() {
            // Confirmation for deletion
            document.querySelectorAll('.delete-campaign-form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    if (!confirm('🗑️ Confirmation : Voulez-vous supprimer cette campagne ? Cette action est irréversible.')) {
                        e.preventDefault();
                    }
                });
            });

            const detailModal = document.getElementById('detailModal');
            
            // Show details logic
            document.addEventListener('click', function(e) {
                const btn = e.target.closest('.show-campaign-btn');
                if (btn) {
                    const id = btn.dataset.id;
                    fetchDetails(id);
                }

                if (e.target.closest('.close-detail-modal')) {
                    detailModal.classList.add('hidden');
                }
            });

            function fetchDetails(id) {
                // Show loading or just fetch
                let url = "{{ route('admin.newsletter.campaigns.show', ':id') }}";
                url = url.replace(':id', id);

                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        fillModal(data);
                        detailModal.classList.remove('hidden');
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('❌ Erreur lors de la récupération des détails.');
                    });
            }

            function fillModal(data) {
                document.getElementById('modalSubject').textContent = data.subject;
                document.getElementById('modalMeta').textContent = `Envoyé par ${data.sent_by} • ${data.recipients_count} destinataires`;
                document.getElementById('modalDate').textContent = `Le ${data.created_at}`;
                document.getElementById('modalBody').innerHTML = data.body;

                // Icons and colors
                const iconBox = document.getElementById('modalTypeIcon');
                if (data.is_recurring) {
                    iconBox.className = 'p-2 rounded-lg mr-3 bg-purple-100 text-purple-600';
                    document.getElementById('modalRecurrenceBox').classList.remove('hidden');
                    document.getElementById('modalFreq').textContent = data.frequency.charAt(0).toUpperCase() + data.frequency.slice(1);
                    document.getElementById('modalStart').textContent = data.start_date;
                    document.getElementById('modalEnd').textContent = data.end_date;
                    document.getElementById('modalNext').textContent = data.next_run_at || 'Terminé';
                } else {
                    iconBox.className = 'p-2 rounded-lg mr-3 bg-blue-100 text-blue-600';
                    document.getElementById('modalRecurrenceBox').classList.add('hidden');
                }

                // Attachments
                const attachBox = document.getElementById('modalAttachmentsBox');
                const attachList = document.getElementById('modalAttachmentsList');
                attachList.innerHTML = '';
                
                if (data.attachments && data.attachments.length > 0) {
                    attachBox.classList.remove('hidden');
                    data.attachments.forEach(file => {
                        const link = document.createElement('a');
                        link.href = `/storage/${file.path}`;
                        link.target = '_blank';
                        link.className = 'inline-flex items-center px-3 py-1 bg-white border rounded text-xs text-indigo-700 hover:bg-indigo-50 transition';
                        link.innerHTML = `<i class="fas fa-file-alt mr-2"></i> ${file.name}`;
                        attachList.appendChild(link);
                    });
                } else {
                    attachBox.classList.add('hidden');
                }
            }

            // Close on escape
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && !detailModal.classList.contains('hidden')) {
                    detailModal.classList.add('hidden');
                }
            });
        });
    </script>
@endsection