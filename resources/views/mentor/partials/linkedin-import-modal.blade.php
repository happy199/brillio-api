<!-- Modal Import LinkedIn -->
<div id="linkedinImportModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
    style="display: none;" x-data="linkedInImporter()">
    <div class="bg-white rounded-3xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b sticky top-0 bg-white rounded-t-3xl">
            <div class="flex items-center justify-between">
                <h3 class="text-2xl font-bold text-gray-900">Importer depuis LinkedIn</h3>
                <button @click="closeModal()" class="p-2 hover:bg-gray-100 rounded-full">
                    <svg class="w-6 h-6" width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <div class="p-6 space-y-6">
            <!-- Messages d'erreur/succès -->
            <div x-show="errorMessage" x-cloak class="bg-red-50 border border-red-200 rounded-xl p-4">
                <div class="flex gap-3">
                    <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" width="20" height="20" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <p class="font-medium text-red-900">Erreur</p>
                        <p class="text-sm text-red-700 mt-1" x-text="errorMessage"></p>
                    </div>
                </div>
            </div>

            <div x-show="successMessage" x-cloak class="bg-green-50 border border-green-200 rounded-xl p-4">
                <div class="flex gap-3">
                    <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" width="20" height="20" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <p class="font-medium text-green-900">Succès</p>
                        <p class="text-sm text-green-700 mt-1" x-text="successMessage"></p>
                    </div>
                </div>
            </div>

            <!-- Instructions -->
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-6">
                <h4 class="font-bold text-blue-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Comment exporter votre profil LinkedIn
                </h4>
                <ol class="list-decimal list-inside space-y-2 text-blue-800 text-sm">
                    <li>Connectez-vous à <strong>LinkedIn</strong></li>
                    <li>Cliquez sur votre <strong>photo de profil</strong> (en haut à droite)</li>
                    <li>Sélectionnez <strong>"Voir le profil"</strong></li>
                    <li>Cliquez sur <strong>"Plus"</strong> (bouton avec 3 points)</li>
                    <li>Choisissez <strong>"Enregistrer au format PDF"</strong></li>
                    <li>Le téléchargement démarre automatiquement</li>
                    <li>Revenez ici et uploadez le fichier PDF ci-dessous</li>
                </ol>
            </div>

            <!-- Zone d'upload -->
            <div x-show="!uploading && !parsedData">
                <input type="file" accept=".pdf" @change="handleFileUpload($event)" class="hidden" x-ref="fileInput">
                <div @click="$refs.fileInput.click()"
                    class="border-2 border-dashed border-gray-300 rounded-xl p-12 text-center cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition">
                    <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" width="64" height="64" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    <p class="text-lg font-semibold text-gray-700">Cliquez pour choisir votre PDF LinkedIn</p>
                    <p class="text-sm text-gray-500 mt-2">ou glissez-déposez le fichier ici (max 5MB)</p>
                </div>
            </div>

            <!-- Loader -->
            <div x-show="uploading" class="text-center py-12">
                <div
                    class="inline-block animate-spin rounded-full h-16 w-16 border-4 border-blue-500 border-t-transparent">
                </div>
                <p class="mt-4 text-gray-600 font-medium">Analyse du PDF en cours...</p>
            </div>

            <!-- Prévisualisation -->
            <div x-show="parsedData && !uploading" class="space-y-4">
                <div class="bg-gray-50 rounded-xl p-4 space-y-3">
                    <div x-show="parsedData?.name">
                        <p class="text-sm text-gray-500">Nom</p>
                        <p class="font-semibold" x-text="parsedData?.name"></p>
                    </div>
                    <div x-show="parsedData?.headline">
                        <p class="text-sm text-gray-500">Titre</p>
                        <p class="font-semibold" x-text="parsedData?.headline"></p>
                    </div>
                    <div x-show="parsedData?.experience_count">
                        <p class="text-sm text-gray-500">Expériences trouvées</p>
                        <p class="font-semibold" x-text="parsedData?.experience_count + ' expérience(s)'"></p>
                    </div>
                    <div x-show="parsedData?.skills_count">
                        <p class="text-sm text-gray-500">Compétences trouvées</p>
                        <p class="font-semibold" x-text="parsedData?.skills_count + ' compétence(s)'"></p>
                    </div>
                </div>

                <!-- Suggestions pour données manquantes -->
                <div x-show="parsedData?.suggestions" x-cloak
                    class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                    <div class="flex gap-3">
                        <svg class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5" width="20" height="20" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="flex-1">
                            <p class="font-medium text-yellow-900" x-text="parsedData?.suggestions?.message"></p>
                            <ul class="mt-2 space-y-1">
                                <template x-for="action in parsedData?.suggestions?.actions" :key="action">
                                    <li class="text-sm text-yellow-700 flex items-start gap-2">
                                        <span class="mt-0.5">•</span>
                                        <span x-text="action"></span>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="flex gap-3">
                    <button @click="reset()" class="flex-1 py-3 border rounded-xl font-medium hover:bg-gray-50">
                        Annuler
                    </button>
                    <button @click="confirmImport()"
                        class="flex-1 py-3 bg-gradient-to-r from-orange-500 to-red-500 text-white font-semibold rounded-xl hover:shadow-lg">
                        Confirmer l'import
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function linkedInImporter() {
        return {
            uploading: false,
            parsedData: null,
            pdfFile: null,
            errorMessage: '',
            successMessage: '',

            async handleFileUpload(event) {
                const file = event.target.files[0];
                if (!file) return;

                // Réinitialiser les messages
                this.errorMessage = '';
                this.successMessage = '';

                if (!file.name.endsWith('.pdf')) {
                    this.errorMessage = 'Veuillez sélectionner un fichier PDF';
                    return;
                }

                // Vérifier la taille (max 5MB)
                if (file.size > 5 * 1024 * 1024) {
                    this.errorMessage = 'Le fichier est trop volumineux (maximum 5MB)';
                    return;
                }

                this.pdfFile = file;
                this.uploading = true;

                try {
                    // Créer FormData pour envoyer le PDF
                    const formData = new FormData();
                    formData.append('pdf', file);

                    // Envoyer au backend pour parsing
                    const response = await fetch('/espace-mentor/profil/linkedin-import', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    const result = await response.json();

                    if (result.success) {
                        this.parsedData = result.data;
                        this.successMessage = result.message || 'PDF analysé avec succès !';
                    } else {
                        this.errorMessage = result.error || 'Une erreur est survenue lors de l\'analyse du PDF';
                        this.reset();
                    }
                } catch (error) {
                    this.errorMessage = 'Erreur de connexion au serveur. Veuillez réessayer.';
                    console.error('Upload error:', error);
                    this.reset();
                }

                this.uploading = false;
            },

            async confirmImport() {
                // Les données sont déjà sauvegardées, on recharge juste la page
                this.successMessage = 'Profil importé avec succès ! Rechargement...';
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            },

            reset() {
                this.parsedData = null;
                this.uploading = false;
                this.pdfFile = null;
                if (this.$refs.fileInput) {
                    this.$refs.fileInput.value = '';
                }
            },

            closeModal() {
                document.getElementById('linkedinImportModal').classList.add('hidden');
                this.errorMessage = '';
                this.successMessage = '';
                this.reset();
            }
        }
    }
</script>