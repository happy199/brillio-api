@extends('layouts.admin')

@push('styles')
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet" nonce="{{ request()->attributes->get('csp_nonce') }}">
<style nonce="{{ request()->attributes->get('csp_nonce') }}">
    .ql-editor { min-height: 250px; font-size: 16px; }
    .ql-toolbar.ql-snow { border-top-left-radius: 0.5rem; border-top-right-radius: 0.5rem; background: #f9fafb; border-color: #e5e7eb; }
    .ql-container.ql-snow { border-bottom-left-radius: 0.5rem; border-bottom-right-radius: 0.5rem; border-color: #e5e7eb; }
</style>
@endpush


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
        <button id="openEmailModalBtn"
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
                            data-id="{{ $subscriber->id }}"
                            data-email="{{ $subscriber->email }}"
                            data-status="{{ $subscriber->status }}"
                            class="edit-subscriber-btn text-blue-600 hover:text-blue-900" title="Modifier">
                            <i class="fas fa-edit"></i>
                        </button>
                        <form action="{{ route('admin.newsletter.destroy', $subscriber->id) }}" method="POST"
                            class="inline delete-subscriber-form">
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
                <button class="close-email-modal text-white hover:text-gray-200">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>
        <form action="{{ route('admin.newsletter.send-email') }}" method="POST" class="p-6 space-y-6" id="emailForm" enctype="multipart/form-data"
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
                },
                updateBody(html) {
                    this.emailBody = html;
                }
            }"
            x-on:body-updated.window="updateBody($event.detail.html)">
            @csrf

            <!-- Destinataires -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <label class="block text-sm font-bold text-gray-900 mb-3">
                    <i class="fas fa-users mr-2"></i>Destinataires
                </label>
                <div class="space-y-3">
                    <label class="flex items-start cursor-pointer p-3 bg-white rounded border hover:border-blue-500">
                        <input type="radio" name="recipient_type" value="all" checked class="mt-1 mr-3 recipient-type-radio">
                        <div>
                            <span class="font-medium">Tous les abonnés actifs</span>
                            <p class="text-sm text-gray-600">{{ $stats['active'] }} destinataires</p>
                        </div>
                    </label>
                    <label class="flex items-start cursor-pointer p-3 bg-white rounded border hover:border-blue-500">
                        <input type="radio" name="recipient_type" value="all_users" class="mt-1 mr-3 recipient-type-radio">
                        <div>
                            <span class="font-medium">Tous les utilisateurs du système</span>
                            <p class="text-sm text-gray-600">{{ $stats['total_users'] }} destinataires</p>
                        </div>
                    </label>
                    <label class="flex items-start cursor-pointer p-3 bg-white rounded border hover:border-blue-500">
                        <input type="radio" name="recipient_type" value="specific_population" class="mt-1 mr-3 recipient-type-radio">
                        <div>
                            <span class="font-medium">Population spécifique</span>
                            <p class="text-sm text-gray-600">Ciblez par type (Jeunes, Mentors, Orgas)</p>
                        </div>
                    </label>
                    <label class="flex items-start cursor-pointer p-3 bg-white rounded border hover:border-blue-500">
                        <input type="radio" name="recipient_type" value="custom" class="mt-1 mr-3 recipient-type-radio">
                        <div>
                            <span class="font-medium">Liste d'emails personnalisée</span>
                            <p class="text-sm text-gray-600">Saisissez les adresses manuellement</p>
                        </div>
                    </label>
                    <label class="flex items-start cursor-pointer p-3 bg-white rounded border hover:border-blue-500">
                        <input type="radio" name="recipient_type" value="selected" class="mt-1 mr-3 recipient-type-radio">
                        <div>
                            <span class="font-medium">Sélection dans le tableau</span>
                            <p class="text-sm text-gray-600">Cochez les abonnés ci-dessus</p>
                        </div>
                    </label>
                </div>

                <div id="specificPopulationSection" class="hidden mt-4 p-4 bg-white rounded border border-blue-200">
                    <p class="text-sm font-bold text-gray-900 mb-3">Sélectionnez les populations :</p>
                    <div class="flex flex-wrap gap-4">
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" name="populations[]" value="jeune" class="rounded text-blue-600">
                            <div class="text-sm">
                                <span class="font-medium">Jeunes</span>
                                <span class="text-gray-500 ml-1">({{ $stats['total_jeunes'] }})</span>
                            </div>
                        </label>
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" name="populations[]" value="mentor" class="rounded text-blue-600">
                            <div class="text-sm">
                                <span class="font-medium">Mentors</span>
                                <span class="text-gray-500 ml-1">({{ $stats['total_mentors'] }})</span>
                            </div>
                        </label>
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" name="populations[]" value="organization" class="rounded text-blue-600">
                            <div class="text-sm">
                                <span class="font-medium">Organisations</span>
                                <span class="text-gray-500 ml-1">({{ $stats['total_organizations'] }})</span>
                            </div>
                        </label>
                    </div>
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

            <!-- Sujet -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-heading mr-1"></i>Sujet de l'email *
                </label>
                <input type="text" name="subject" required 
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" 
                    placeholder="Ex: Newsletter de Printemps - Brillio">
            </div>

            <!-- Message -->
            <div>
                <div class="flex justify-between items-center mb-2">
                    <label class="block text-sm font-medium text-gray-700">
                        <i class="fas fa-envelope-open-text mr-1"></i>Message *
                    </label>
                    <button type="button" id="toggle-html" class="text-xs font-semibold px-3 py-1 bg-gray-100 border rounded-lg hover:bg-gray-200 text-gray-600 transition">
                        <i class="fas fa-code mr-1"></i>Mode HTML / Source
                    </button>
                </div>
                <div id="editor-container" class="bg-white border rounded-lg focus-within:ring-2 focus-within:ring-blue-500"></div>
                <textarea id="html-editor" class="hidden w-full h-64 px-4 py-3 border rounded-lg font-mono text-sm bg-gray-900 text-green-400 focus:ring-2 focus:ring-blue-500" placeholder="Collez ou écrivez votre HTML ici..."></textarea>
                <input type="hidden" name="body" id="bodyInput" required>
            </div>

            <!-- Pièces jointes -->
            <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-5">
                <label class="block text-indigo-900 font-bold mb-3 flex items-center">
                    <i class="fas fa-paperclip mr-2 text-indigo-600"></i>Pièces jointes
                </label>
                <div class="space-y-4">
                    <div class="flex items-center justify-center w-full">
                        <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-indigo-300 border-dashed rounded-lg cursor-pointer bg-white hover:bg-indigo-50 transition">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <i class="fas fa-cloud-upload-alt text-3xl text-indigo-500 mb-2"></i>
                                <p class="mb-2 text-sm text-indigo-700"><span class="font-bold">Cliquez pour ajouter</span> ou glissez-déposez</p>
                                <p class="text-xs text-indigo-500">PDF, Images, ZIP... (Max 10Mo par envoi)</p>
                            </div>
                            <input type="file" name="attachments[]" multiple class="hidden" id="attachmentsInput" />
                        </label>
                    </div>
                    <div id="fileList" class="flex flex-wrap gap-2"></div>
                </div>
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
&lt;body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;"&gt;
  &lt;table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4; padding: 20px 0;"&gt;
    &lt;tr&gt;
      &lt;td align="center"&gt;
        &lt;table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden;"&gt;
          &lt;!-- Header --&gt;
          &lt;tr&gt;
            &lt;td style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); padding: 30px; text-align: center;"&gt;
              &lt;div style="margin-bottom: 15px;"&gt;
                &lt;img src="{{ config('app.url') }}/android-chrome-512x512.png" 
                     style="width: 70px; height: 70px; border-radius: 50%; background: white; padding: 3px;"&gt;
              &lt;/div&gt;
              &lt;h1 style="color: #ffffff; margin: 0; font-size: 24px;"&gt;Brillio&lt;/h1&gt;
            &lt;/td&gt;
          &lt;/tr&gt;
          &lt;!-- Content --&gt;
          &lt;tr&gt;
            &lt;td style="padding: 40px 30px; color: #374151; font-size: 16px; line-height: 1.6;"&gt;
              &lt;h2 style="color: #6366f1; margin-top: 0;"&gt;Bonjour ! 👋&lt;/h2&gt;
              &lt;p&gt;Votre message ici...&lt;/p&gt;
              &lt;div style="text-align: center; margin-top: 30px;"&gt;
                &lt;a href="https://brillio.africa" 
                   style="background: #6366f1; color: white; padding: 12px 25px; text-decoration: none; border-radius: 6px; display: inline-block;"&gt;
                   Découvrir maintenant
                &lt;/a&gt;
              &lt;/div&gt;
            &lt;/td&gt;
          &lt;/tr&gt;
          &lt;!-- Footer --&gt;
          &lt;tr&gt;
            &lt;td style="background-color: #f9fafb; padding: 20px; text-align: center; font-size: 11px; color: #9ca3af; border-top: 1px solid #e5e7eb;"&gt;
              © 2026 Brillio - Tous droits réservés
            &lt;/td&gt;
          &lt;/tr&gt;
        &lt;/table&gt;
      &lt;/td&gt;
    &lt;/tr&gt;
  &lt;/table&gt;
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
            
            <div class="border-t pt-6 space-y-6">
                <label class="flex items-center space-x-3 cursor-pointer">
                    <input type="checkbox" name="is_recurring" id="isRecurringCheckbox" class="rounded text-blue-600">
                    <span class="font-bold text-gray-900">Campagne récurrente ?</span>
                </label>

                <div id="recurrenceOptions" class="hidden space-y-4 bg-gray-50 p-4 rounded-lg border border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Fréquence</label>
                            <select name="frequency" class="w-full px-4 py-2 border rounded-lg">
                                <option value="daily">Quotidien</option>
                                <option value="weekly" selected>Hebdomadaire</option>
                                <option value="monthly">Mensuel</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Date de début</label>
                            <input type="date" name="start_date" value="{{ date('Y-m-d') }}" class="w-full px-4 py-2 border rounded-lg">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Date de fin</label>
                            <input type="date" name="end_date" value="{{ date('Y-m-d', strtotime('+3 months')) }}" class="w-full px-4 py-2 border rounded-lg">
                        </div>
                    </div>
                    <p class="text-xs text-gray-600 italic">
                        <i class="fas fa-info-circle mr-1"></i>L'email sera envoyé périodiquement aux destinataires re-évalués à chaque envoi.
                    </p>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-between items-center pt-4 border-t">
                <button type="button" class="close-email-modal px-6 py-2 border rounded-lg hover:bg-gray-50">
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
                <button type="button" class="close-edit-modal px-4 py-2 border rounded-lg hover:bg-gray-50">
                    Annuler
                </button>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>
<script nonce="{{ request()->attributes->get('csp_nonce') }}" src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    // Select all
    const selectAll = document.getElementById('selectAll');
    if (selectAll) {
        selectAll.addEventListener('change', function () {
            document.querySelectorAll('.subscriber-checkbox:not([disabled])').forEach(cb => {
                cb.checked = this.checked;
            });
            updateRecipientCount();
        });
    }

    // Update count on checkbox change
    document.querySelectorAll('.subscriber-checkbox').forEach(cb => {
        cb.addEventListener('change', updateRecipientCount);
    });

    // Recipient type radio buttons
    document.querySelectorAll('.recipient-type-radio').forEach(radio => {
        radio.addEventListener('change', updateRecipientCount);
    });

    // Confirmation for deletion
    document.querySelectorAll('.delete-subscriber-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!confirm('❌ Supprimer cet abonné ?')) {
                e.preventDefault();
            }
        });
    });

    // Initialisation de Quill
    const quill = new Quill('#editor-container', {
        theme: 'snow',
        placeholder: 'Écrivez votre message ici...',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                ['blockquote', 'code-block'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'color': [] }, { 'background': [] }],
                ['link', 'image'],
                ['clean']
            ]
        }
    });

    const htmlEditor = document.getElementById('html-editor');
    const editorContainer = document.getElementById('editor-container');
    const toggleHtmlBtn = document.getElementById('toggle-html');
    let isHtmlMode = false;

    // Toggle entre Mode Riche et Mode HTML
    toggleHtmlBtn.addEventListener('click', function() {
        isHtmlMode = !isHtmlMode;
        
        if (isHtmlMode) {
            const html = quill.root.innerHTML;
            htmlEditor.value = html;
            editorContainer.classList.add('hidden');
            htmlEditor.classList.remove('hidden');
            toggleHtmlBtn.innerHTML = '<i class="fas fa-edit mr-1"></i>Mode Texte Riche';
            toggleHtmlBtn.classList.add('bg-indigo-600', 'text-white');
        } else {
            const html = htmlEditor.value;
            quill.root.innerHTML = html;
            htmlEditor.classList.add('hidden');
            editorContainer.classList.remove('hidden');
            toggleHtmlBtn.innerHTML = '<i class="fas fa-code mr-1"></i>Mode HTML / Source';
            toggleHtmlBtn.classList.remove('bg-indigo-600', 'text-white');
        }
    });

    // Synchronisation avec Alpine.js et le champ caché
    function syncContent(html) {
        document.getElementById('bodyInput').value = html;
        
        // Dispatch d'un événement personnalisé pour Alpine.js
        window.dispatchEvent(new CustomEvent('body-updated', { 
            detail: { html: html } 
        }));
    }

    quill.on('text-change', function() {
        if (!isHtmlMode) syncContent(quill.root.innerHTML);
    });

    htmlEditor.addEventListener('input', function() {
        if (isHtmlMode) syncContent(this.value);
    });

    // Initial sync
    syncContent(quill.root.innerHTML);

    // Gestion des fichiers joints
    const attachmentsInput = document.getElementById('attachmentsInput');
    const fileList = document.getElementById('fileList');

    if (attachmentsInput) {
        attachmentsInput.addEventListener('change', function() {
            fileList.innerHTML = '';
            Array.from(this.files).forEach((file) => {
                const div = document.createElement('div');
                div.className = 'flex items-center space-x-2 px-3 py-1 bg-white border rounded text-xs text-indigo-700';
                div.innerHTML = `
                    <i class="fas fa-file-alt text-indigo-400"></i>
                    <span class="truncate max-w-[150px] font-medium">${file.name}</span>
                    <span class="text-gray-400">(${(file.size / 1024).toFixed(0)} KB)</span>
                `;
                fileList.appendChild(div);
            });
        });
    }

    // Delegation d'evenements
    document.addEventListener('click', function(e) {
        if (e.target.closest('#openEmailModalBtn')) {
            document.getElementById('emailModal').classList.remove('hidden');
        }
        if (e.target.closest('.close-email-modal')) {
            document.getElementById('emailModal').classList.add('hidden');
        }
        if (e.target.closest('.close-edit-modal')) {
            document.getElementById('editModal').classList.add('hidden');
        }
        if (e.target.closest('#isRecurringCheckbox')) {
            document.getElementById('recurrenceOptions').classList.toggle('hidden', !e.target.checked);
        }

        const editBtn = e.target.closest('.edit-subscriber-btn');
        if (editBtn) {
            const id = editBtn.dataset.id;
            const email = editBtn.dataset.email;
            const status = editBtn.dataset.status;
            
            document.getElementById('editEmail').value = email;
            document.getElementById('editStatus').value = status;
            
            let url = "{{ route('admin.newsletter.update', ':id') }}";
            document.getElementById('editForm').action = url.replace(':id', id);
            
            document.getElementById('editModal').classList.remove('hidden');
        }
    });

    function updateRecipientCount() {
        const type = document.querySelector('input[name="recipient_type"]:checked')?.value || 'all';
        const count = document.querySelectorAll('.subscriber-checkbox:checked').length;

        document.getElementById('countDisplay').textContent = count;
        document.getElementById('selectedCount').classList.toggle('hidden', type !== 'selected');
        document.getElementById('customEmailsSection').classList.toggle('hidden', type !== 'custom');
        document.getElementById('specificPopulationSection').classList.toggle('hidden', type !== 'specific_population');
    }

    // Email form submission
    const emailForm = document.getElementById('emailForm');
    if (emailForm) {
        emailForm.addEventListener('submit', function (e) {
            document.getElementById('bodyInput').value = quill.root.innerHTML;

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

            if (!confirm(`📧 Confirmer l'envoi de la campagne ?`)) {
                e.preventDefault();
                return;
            }

            // Vérification du poids total des fichiers (Max 10Mo cumulés)
            const files = attachmentsInput ? attachmentsInput.files : [];
            let totalSize = 0;
            for (let i = 0; i < files.length; i++) {
                totalSize += files[i].size;
            }
            if (totalSize > 10 * 1024 * 1024) { // 10Mo
                e.preventDefault();
                alert(`⚠️ Le poids total des pièces jointes (${(totalSize / (1024 * 1024)).toFixed(2)} Mo) dépasse la limite autorisée de 10 Mo. Veuillez réduire la taille des fichiers.`);
                return;
            }

            if (type === 'selected') {
                const recipientsInput = document.createElement('input');
                recipientsInput.type = 'hidden';
                recipientsInput.name = 'recipients';
                recipientsInput.value = JSON.stringify(selected);
                this.appendChild(recipientsInput);
            }
        });
    }
</script>
@endsection