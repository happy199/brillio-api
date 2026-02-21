@extends('layouts.admin')

@section('title', 'Newsletter - Abonnés')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Newsletter - Abonnés</h1>
            <p class="text-gray-600 mt-1">Gérer les abonnés et envoyer des campagnes email</p>
        </div>
        <a href="{{ route('admin.newsletter.campaigns') }}"
            class="mr-3 px-6 py-3 bg-white border border-gray-300 text-gray-700 font-bold rounded-lg hover:bg-gray-50">
            <i class="fas fa-history mr-2"></i>Historique
        </a>
        <button onclick="document.getElementById('emailModal').classList.remove('hidden')"
            class="px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-bold rounded-lg hover:shadow-lg">
            <i class="fas fa-paper-plane mr-2"></i>Nouvelle Campagne Email
        </button>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 rounded-lg">
                    <i class="fas fa-users text-blue-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Total</p>
                    <p class="text-2xl font-bold">{{ $stats['total'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 bg-green-100 rounded-lg">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Actifs</p>
                    <p class="text-2xl font-bold">{{ $stats['active'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 bg-red-100 rounded-lg">
                    <i class="fas fa-times-circle text-red-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Désabonnés</p>
                    <p class="text-2xl font-bold">{{ $stats['unsubscribed'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Actions -->
    <div class="bg-white rounded-lg shadow p-6">
        <form method="GET" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-gray-700 mb-2">Rechercher</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Email..."
                    class="w-full px-4 py-2 border rounded-lg">
            </div>
            <div class="min-w-[150px]">
                <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                <select name="status" class="w-full px-4 py-2 border rounded-lg">
                    <option value="">Tous</option>
                    <option value="active" {{ request('status')=='active' ? 'selected' : '' }}>Actifs</option>
                    <option value="unsubscribed" {{ request('status')=='unsubscribed' ? 'selected' : '' }}>Désabonnés
                    </option>
                </select>
            </div>
            <button type="submit" class="px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                Filtrer
            </button>
            <a href="{{ route('admin.newsletter.export-csv', request()->query()) }}"
                class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                <i class="fas fa-file-csv mr-2"></i>Export CSV
            </a>
            <a href="{{ route('admin.newsletter.export-pdf', request()->query()) }}"
                class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                <i class="fas fa-file-pdf mr-2"></i>Export PDF
            </a>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                        <input type="checkbox" id="selectAll" class="rounded">
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Inscrit le</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($subscribers as $subscriber)
                <tr>
                    <td class="px-6 py-4">
                        <input type="checkbox" name="selected[]" value="{{ $subscriber->email }}"
                            class="subscriber-checkbox rounded" {{ $subscriber->status != 'active' ? 'disabled' : '' }}>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $subscriber->email }}</td>
                    <td class="px-6 py-4">
                        @if($subscriber->status == 'active')
                        <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">Actif</span>
                        @else
                        <span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded-full">Désabonné</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $subscriber->subscribed_at->format('d/m/Y H:i') }}
                    </td>
                    <td class="px-6 py-4 text-sm space-x-3">
                        <button
                            onclick="editSubscriber({{ $subscriber->id }}, '{{ $subscriber->email }}', '{{ $subscriber->status }}')"
                            class="text-blue-600 hover:text-blue-900" title="Modifier">
                            <i class="fas fa-edit"></i>
                        </button>
                        <form action="{{ route('admin.newsletter.destroy', $subscriber->id) }}" method="POST"
                            class="inline" onsubmit="return confirm('❌ Supprimer cet abonné ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900" title="Supprimer">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">Aucun abonné trouvé</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $subscribers->links() }}
    </div>
</div>

<!-- Email Marketing Modal -->
<div id="emailModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg max-w-3xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b bg-gradient-to-r from-blue-600 to-purple-600 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-bold">📧 Campagne Email Marketing</h3>
                    <p class="text-sm text-white/90 mt-1">Composez et envoyez votre message</p>
                </div>
                <button onclick="document.getElementById('emailModal').classList.add('hidden')"
                    class="text-white hover:text-gray-200">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>
        <form action="{{ route('admin.newsletter.send-email') }}" method="POST" class="p-6 space-y-6" id="emailForm"
            x-data="{ 
                tags: [], 
                inputValue: '',
                emailBody: '',
                format: 'html',
                addTag() {
                    const emails = this.inputValue.split(/[,\s;]+/).map(e => e.trim()).filter(e => e.includes('@') && !this.tags.includes(e));
                    if (emails.length > 0) {
                        this.tags = [...this.tags, ...emails];
                        this.inputValue = '';
                    }
                },
                removeTag(tag) {
                    this.tags = this.tags.filter(t => t !== tag);
                }
            }">
            @csrf

            <!-- Destinataires -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <label class="block text-sm font-bold text-gray-900 mb-3">
                    <i class="fas fa-users mr-2"></i>Destinataires
                </label>
                <div class="space-y-3">
                    <label class="flex items-start cursor-pointer p-3 bg-white rounded border hover:border-blue-500">
                        <input type="radio" name="recipient_type" value="all" checked class="mt-1 mr-3"
                            onchange="updateRecipientCount()">
                        <div>
                            <span class="font-medium">Tous les abonnés actifs</span>
                            <p class="text-sm text-gray-600">{{ $stats['active'] }} destinataires</p>
                        </div>
                    </label>
                    <label class="flex items-start cursor-pointer p-3 bg-white rounded border hover:border-blue-500">
                        <input type="radio" name="recipient_type" value="all_users" class="mt-1 mr-3"
                            onchange="updateRecipientCount()">
                        <div>
                            <span class="font-medium">Tous les utilisateurs du système</span>
                            <p class="text-sm text-gray-600">{{ $stats['total_users'] }} destinataires</p>
                        </div>
                    </label>
                    <label class="flex items-start cursor-pointer p-3 bg-white rounded border hover:border-blue-500">
                        <input type="radio" name="recipient_type" value="custom" class="mt-1 mr-3"
                            onchange="updateRecipientCount()">
                        <div>
                            <span class="font-medium">Liste d'emails personnalisée</span>
                            <p class="text-sm text-gray-600">Saisissez les adresses manuellement</p>
                        </div>
                    </label>
                    <label class="flex items-start cursor-pointer p-3 bg-white rounded border hover:border-blue-500">
                        <input type="radio" name="recipient_type" value="selected" class="mt-1 mr-3"
                            onchange="updateRecipientCount()">
                        <div>
                            <span class="font-medium">Sélection dans le tableau</span>
                            <p class="text-sm text-gray-600">Cochez les abonnés ci-dessus</p>
                        </div>
                    </label>
                </div>
                <div id="customEmailsSection" class="hidden mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Liste des emails (Tags)
                    </label>
                    <div
                        class="flex flex-wrap gap-2 p-2 border rounded-lg bg-white focus-within:ring-2 focus-within:ring-blue-500">
                        <template x-for="tag in tags" :key="tag">
                            <span
                                class="inline-flex items-center px-2 py-1 bg-blue-100 text-blue-700 text-sm rounded cursor-default border border-blue-200">
                                <span x-text="tag"></span>
                                <button type="button" @click="removeTag(tag)"
                                    class="ml-1 text-blue-500 hover:text-blue-700">
                                    <i class="fas fa-times text-xs"></i>
                                </button>
                            </span>
                        </template>
                        <input type="text" x-model="inputValue" @keydown.enter.prevent="addTag()"
                            @keydown.comma.prevent="addTag()" @blur="addTag()"
                            placeholder="Saisissez un email et appuyez sur Entrée..."
                            class="flex-1 outline-none text-sm min-w-[200px]">
                    </div>
                    <input type="hidden" name="custom_emails" :value="tags.join(',')">
                    <p class="text-xs text-gray-500 mt-1 italic">
                        Astuce : Vous pouvez coller une liste d'emails séparés par des virgules.
                    </p>
                </div>
                <div id="selectedCount" class="hidden mt-3 p-3 bg-white rounded border border-blue-300">
                    <span class="font-medium">✓ Sélectionnés : </span>
                    <span id="countDisplay" class="text-blue-600 font-bold">0</span> destinataire(s)
                </div>
            </div>

            <!-- Format -->
            <div class="bg-gray-50 border rounded-lg p-4">
                <label class="block text-sm font-bold text-gray-900 mb-3">
                    <i class="fas fa-code mr-2"></i>Format du message
                </label>
                <div class="flex gap-4">
                    <label class="flex items-center cursor-pointer p-3 bg-white rounded border hover:border-gray-400">
                        <input type="radio" name="format" value="html" x-model="format" class="mr-2"
                            onchange="toggleFormatHelp()">
                        <span><i class="fas fa-code mr-1"></i>HTML (avec mise en forme)</span>
                    </label>
                    <label class="flex items-center cursor-pointer p-3 bg-white rounded border hover:border-gray-400">
                        <input type="radio" name="format" value="text" x-model="format" class="mr-2"
                            onchange="toggleFormatHelp()">
                        <span><i class="fas fa-align-left mr-1"></i>Texte brut</span>
                    </label>
                </div>
            </div>

            <!-- Sujet -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-heading mr-1"></i>Sujet de l'email *
                </label>
                <input type="text" name="subject" required
                    class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500"
                    placeholder="Ex: Nouveautés Brillio - Janvier 2026">
            </div>

            <!-- Message -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-envelope-open-text mr-1"></i>Message *
                </label>
                <div id="htmlHelp" class="mb-2 p-3 bg-yellow-50 border border-yellow-200 rounded text-sm">
                    <strong>💡 Mode HTML :</strong> Utilisez des balises :
                    <code class="bg-white px-1">&lt;h1&gt;</code>, <code class="bg-white px-1">&lt;p&gt;</code>,
                    <code class="bg-white px-1">&lt;strong&gt;</code>, <code
                        class="bg-white px-1">&lt;a href=""&gt;</code>
                </div>
                <div id="textHelp" class="hidden mb-2 p-3 bg-blue-50 border border-blue-200 rounded text-sm">
                    <strong>📝 Mode Texte :</strong> Message sans mise en forme HTML
                </div>
                <textarea name="body" rows="14" required x-model="emailBody"
                    class="w-full px-4 py-3 border rounded-lg font-mono text-sm focus:ring-2 focus:ring-blue-500"
                    placeholder="Écrivez votre message ici..."></textarea>
            </div>

            <!-- Collapsibles -->
            <div class="space-y-4">
                <!-- Template Example -->
                <details class="border rounded-lg bg-gray-50 overflow-hidden">
                    <summary class="cursor-pointer p-3 font-medium hover:bg-gray-100 transition flex items-center">
                        <i class="fas fa-lightbulb mr-2 text-yellow-500"></i>
                        Voir un exemple de template HTML
                    </summary>
                    <div class="p-4 bg-gray-900 text-green-400 text-xs overflow-x-auto">
                        <pre><code>&lt;!DOCTYPE html&gt;
&lt;html&gt;
&lt;body style="font-family: Arial; max-width: 600px; margin: 0 auto; padding: 20px;"&gt;
  &lt;h1 style="color: #6366f1;"&gt;Bonjour ! 👋&lt;/h1&gt;
  &lt;p style="font-size: 16px; line-height: 1.6;"&gt;
    Nous avons de grandes nouvelles à partager avec vous...
  &lt;/p&gt;
  &lt;a href="https://brillio.africa" 
     style="background: #6366f1; color: white; padding: 12px 24px; 
            text-decoration: none; border-radius: 6px; display: inline-block;"&gt;
    Découvrir maintenant
  &lt;/a&gt;
  &lt;p style="color: #666; font-size: 12px; margin-top: 30px;"&gt;
    Vous recevez cet email car vous êtes inscrit à notre newsletter.
  &lt;/p&gt;
&lt;/body&gt;
&lt;/html&gt;</code></pre>
                    </div>
                </details>

                <!-- Dynamic Preview -->
                <details class="border rounded-lg bg-blue-50/50 overflow-hidden">
                    <summary class="cursor-pointer p-3 font-medium hover:bg-blue-100 transition flex items-center">
                        <i class="fas fa-eye mr-2 text-blue-500"></i>
                        Voir l'aperçu dynamique du message
                    </summary>
                    <div class="p-4 bg-white border-t min-h-[100px] max-h-[400px] overflow-y-auto">
                        <div x-show="emailBody.length === 0" class="text-gray-400 italic text-center py-8">
                            Commencez à rédiger votre message pour voir l'aperçu ici...
                        </div>
                        <div x-show="emailBody.length > 0">
                            <!-- HTML Preview -->
                            <template x-if="format === 'html'">
                                <div class="prose max-w-none" x-html="emailBody"></div>
                            </template>
                            <!-- Text Preview -->
                            <template x-if="format === 'text'">
                                <div class="whitespace-pre-wrap font-sans text-gray-800" x-text="emailBody"></div>
                            </template>
                        </div>
                    </div>
                </details>
            </div>

            <!-- Actions -->
            <div class="flex justify-between items-center pt-4 border-t">
                <button type="button" onclick="document.getElementById('emailModal').classList.add('hidden')"
                    class="px-6 py-2 border rounded-lg hover:bg-gray-50">
                    Annuler
                </button>
                <button type="submit"
                    class="px-8 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-bold rounded-lg hover:shadow-xl transform hover:scale-105 transition">
                    <i class="fas fa-paper-plane mr-2"></i>Envoyer la campagne
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Subscriber Modal -->
<div id="editModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg max-w-md w-full">
        <div class="p-6 border-b">
            <h3 class="text-xl font-bold">Modifier l'abonné</h3>
        </div>
        <form id="editForm" method="POST" class="p-6 space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                <input type="email" name="email" id="editEmail" required class="w-full px-4 py-2 border rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                <select name="status" id="editStatus" class="w-full px-4 py-2 border rounded-lg">
                    <option value="active">Actif</option>
                    <option value="unsubscribed">Désabonné</option>
                </select>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')"
                    class="px-4 py-2 border rounded-lg hover:bg-gray-50">
                    Annuler
                </button>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Select all
    document.getElementById('selectAll').addEventListener('change', function () {
        document.querySelectorAll('.subscriber-checkbox:not([disabled])').forEach(cb => {
            cb.checked = this.checked;
        });
        updateRecipientCount();
    });

    // Update count on checkbox change
    document.querySelectorAll('.subscriber-checkbox').forEach(cb => {
        cb.addEventListener('change', updateRecipientCount);
    });

    function updateRecipientCount() {
        const type = document.querySelector('input[name="recipient_type"]:checked')?.value || 'all';
        const count = document.querySelectorAll('.subscriber-checkbox:checked').length;

        document.getElementById('countDisplay').textContent = count;
        document.getElementById('selectedCount').classList.toggle('hidden', type !== 'selected');
        document.getElementById('customEmailsSection').classList.toggle('hidden', type !== 'custom');
    }

    function toggleFormatHelp() {
        const format = document.querySelector('input[name="format"]:checked').value;
        document.getElementById('htmlHelp').classList.toggle('hidden', format !== 'html');
        document.getElementById('textHelp').classList.toggle('hidden', format !== 'text');
    }

    // Email form submission
    document.getElementById('emailForm').addEventListener('submit', function (e) {
        const type = document.querySelector('input[name="recipient_type"]:checked').value;
        const selected = Array.from(document.querySelectorAll('.subscriber-checkbox:checked')).map(cb => cb.value);

        if (type === 'selected' && selected.length === 0) {
            e.preventDefault();
            alert('⚠️ Veuillez sélectionner au moins un destinataire');
            return;
        }

        if (type === 'custom') {
            const customEmails = this.querySelector('input[name="custom_emails"]').value;
            if (!customEmails || customEmails.trim() === '') {
                e.preventDefault();
                alert('⚠️ Veuillez saisir au moins un email');
                return;
            }
        }

        let count = 0;
        const stats = {
            active: Number("{{ $stats['active'] }}"),
            total: Number("{{ $stats['total_users'] }}")
        };

        switch (type) {
            case 'all': count = stats.active; break;
            case 'all_users': count = stats.total; break;
            case 'selected': count = selected.length; break;
            case 'custom': count = 'plusieurs'; break;
        }

        if (!confirm(`📧 Confirmer l'envoi de la campagne ?`)) {
            e.preventDefault();
            return;
        }

        // Pour selected, on envoie la liste. Pour les autres, le contrôleur s'en charge.
        if (type === 'selected') {
            const recipientsInput = document.createElement('input');
            recipientsInput.type = 'hidden';
            recipientsInput.name = 'recipients';
            recipientsInput.value = JSON.stringify(selected);
            this.appendChild(recipientsInput);
        }
    });

    // Edit subscriber
    function editSubscriber(id, email, status) {
        document.getElementById('editEmail').value = email;
        document.getElementById('editStatus').value = status;
        document.getElementById('editForm').action = `/admin/newsletter/${id}`;
        document.getElementById('editModal').classList.remove('hidden');
    }
</script>
@endsection