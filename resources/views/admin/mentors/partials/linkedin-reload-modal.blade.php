<div x-data="linkedinReloader()" @open-linkedin-reload.window="openModal()" id="linkedinReloadModal"
    class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" style="display: none;">
    <div class="bg-white rounded-3xl max-w-xl w-full max-h-[90vh] overflow-y-auto shadow-2xl">
        <div class="p-6 border-b sticky top-0 bg-white rounded-t-3xl flex justify-between items-center">
            <h3 class="text-xl font-bold text-gray-900">Recharger le profil LinkedIn</h3>
            <button @click="closeModal()" class="p-2 hover:bg-gray-100 rounded-full transition">
                <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="p-6 space-y-6">
            <!-- Messages -->
            <div x-show="message" x-cloak
                :class="status === 'success' ? 'bg-green-50 text-green-700 border-green-200' : 'bg-red-50 text-red-700 border-red-200'"
                class="p-4 rounded-xl border text-sm">
                <p x-text="message"></p>
            </div>

            <!-- Mode: Confirmation de rechargement (Si PDF existe) -->
            <div x-show="mode === 'confirm'" class="space-y-4 text-center py-4">
                <div
                    class="w-16 h-16 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </div>
                <p class="text-gray-600">
                    Voulez-vous recharger les données à partir du PDF existant : <br>
                    <strong class="text-gray-900">{{ $mentor->linkedin_pdf_original_name ?? 'profil.pdf' }}</strong> ?
                </p>
                <p class="text-xs text-gray-400 italic">
                    Note : Les étapes du parcours seront remplacées. La biographie et les conseils actuels seront
                    préservés s'ils ne sont pas vides.
                </p>
                <div class="flex gap-3 pt-4">
                    <button @click="mode = 'upload'"
                        class="flex-1 py-2 px-4 border border-gray-300 rounded-xl text-gray-700 hover:bg-gray-50 transition font-medium">
                        Uploader un nouveau PDF
                    </button>
                    <button @click="reloadFromDisk()" :disabled="loading"
                        class="flex-1 py-2 px-4 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition font-bold disabled:opacity-50">
                        <span x-show="!loading">Recharger</span>
                        <span x-show="loading" class="flex items-center justify-center gap-2">
                            <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            Traitement...
                        </span>
                    </button>
                </div>
            </div>

            <!-- Mode: Upload (Fallback si PDF manquant ou désiré) -->
            <div x-show="mode === 'upload'" class="space-y-4">
                <div @dragover.prevent="isDragging = true" @dragleave.prevent="isDragging = false"
                    @drop.prevent="handleDrop($event)" @click="$refs.fileInput.click()"
                    :class="isDragging ? 'border-blue-500 bg-blue-50' : 'border-gray-300 hover:border-blue-400 hover:bg-gray-50'"
                    class="border-2 border-dashed rounded-2xl p-10 text-center cursor-pointer transition relative">

                    <input type="file" x-ref="fileInput" @change="handleFileSelect($event)" class="hidden"
                        accept=".pdf">

                    <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>

                    <p class="text-sm font-semibold text-gray-700">Cliquez ou glissez le nouveau PDF LinkedIn</p>
                    <p class="text-xs text-gray-400 mt-1">Format PDF uniquement (Max 10MB)</p>

                    <div x-show="loading"
                        class="absolute inset-0 bg-white/80 flex flex-col items-center justify-center rounded-2xl">
                        <svg class="animate-spin h-8 w-8 text-blue-600 mb-2" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        <span class="text-xs font-bold text-blue-800">Analyse en cours...</span>
                    </div>
                </div>

                <div class="flex justify-between items-center px-1">
                    <button @click="mode = '{{ $mentor->linkedin_pdf_path ? 'confirm' : 'upload' }}'"
                        x-show="{{ $mentor->linkedin_pdf_path ? 'true' : 'false' }}"
                        class="text-sm text-gray-500 hover:text-gray-700">
                        &larr; Retour au fichier existant
                    </button>
                    <div></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function linkedinReloader() {
        return {
            mode: '{{ $mentor->linkedin_pdf_path ? 'confirm' : 'upload' }}',
            loading: false,
            isDragging: false,
            message: '',
            status: '',

            openModal() {
                document.getElementById('linkedinReloadModal').classList.remove('hidden');
                document.getElementById('linkedinReloadModal').style.display = 'flex';
            },

            closeModal() {
                if (this.loading) return;
                document.getElementById('linkedinReloadModal').classList.add('hidden');
                document.getElementById('linkedinReloadModal').style.display = 'none';
                this.message = '';
            },

            async reloadFromDisk() {
                this.loading = true;
                this.message = '';

                try {
                    const response = await fetch('{{ route('admin.mentors.linkedin-reload', $mentor) }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.status = 'success';
                        this.message = data.message;
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        this.status = 'error';
                        this.message = data.error;
                        if (data.needs_upload) {
                            this.mode = 'upload';
                        }
                    }
                } catch (e) {
                    this.status = 'error';
                    this.message = "Une erreur réseau est survenue.";
                } finally {
                    this.loading = false;
                }
            },

            handleDrop(e) {
                this.isDragging = false;
                const file = e.dataTransfer.files[0];
                this.uploadFile(file);
            },

            handleFileSelect(e) {
                const file = e.target.files[0];
                this.uploadFile(file);
            },

            async uploadFile(file) {
                if (!file || file.type !== 'application/pdf') {
                    this.status = 'error';
                    this.message = "Veuillez sélectionner un fichier PDF valide.";
                    return;
                }

                this.loading = true;
                this.message = '';

                const formData = new FormData();
                formData.append('pdf', file);

                try {
                    const response = await fetch('{{ route('admin.mentors.linkedin-upload', $mentor) }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.status = 'success';
                        this.message = data.message;
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        this.status = 'error';
                        this.message = data.error;
                    }
                } catch (e) {
                    this.status = 'error';
                    this.message = "Une erreur est survenue lors de l'upload.";
                } finally {
                    this.loading = false;
                }
            }
        }
    }
</script>