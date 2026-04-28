<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    function recommendationsSystem() {
        return {
            loading: true,
            establishments: [],
            mbtiType: '',
            userHasPhone: {{ auth()->user()->phone ? 'true' : 'false' }},
            activePhoneInput: null,
            tempPhone: '',
            sidebarOpen: false,
            panelReady: false,
            estDetails: null,
            formData: {},

            formatYoutubeUrl(url) {
                if (!url) return '';
                if (url.includes('youtu.be/')) {
                    return url.replace('youtu.be/', 'youtube.com/embed/');
                }
                if (url.includes('watch?v=')) {
                    return url.replace('watch?v=', 'embed/');
                }
                return url;
            },

            init() {
                if ('{{ $theme ?? "jeune" }}' !== 'jeune') return;

                this.$watch('sidebarOpen', value => {
                    if (!value) {
                        this.panelReady = false;
                        // On vide les détails après la fin de l'animation de fermeture
                        setTimeout(() => { if (!this.sidebarOpen) this.estDetails = null; }, 550);
                    }
                });
                
                fetch('{{ route("jeune.establishments.recommended") }}')
                    .then(res => res.json())
                    .then(data => {
                        this.establishments = data.establishments;
                        this.mbtiType = data.mbti_type;
                        this.loading = false;
                    });
            },

            scrollCarousel(ref, dir) {
                this.$refs[ref].scrollBy({ left: dir * 320, behavior: 'smooth' });
            },

            handleInterest(est) {
                if (est.user_has_interest) return;

                if (!this.userHasPhone) {
                    if (this.activePhoneInput === est.id) {
                        if (!this.tempPhone || this.tempPhone.length < 8) {
                            alert('Veuillez entrer un numéro de téléphone valide.');
                            return;
                        }
                        this.submitInterest(est, this.tempPhone);
                    } else {
                        this.activePhoneInput = est.id;
                    }
                    return;
                }

                this.submitInterest(est);
            },

            submitInterest(est, phone = null) {
                let payload = {};
                if (phone) payload.phone = phone;

                fetch(`/espace-jeune/establishments/${est.id}/interest-quick`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(payload)
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        est.user_has_interest = true;
                        this.userHasPhone = true;
                        this.activePhoneInput = null;
                        
                        // Dispatch toast event
                        window.dispatchEvent(new CustomEvent('toast', { 
                            detail: { message: data.message, type: 'success' } 
                        }));
                    } else {
                        alert(data.message);
                    }
                });
            },

            openDetails(est) {
                this.estDetails = est;
                this.formData = {};
                this.sidebarOpen = true;
                this.panelReady = false;
                setTimeout(() => {
                    this.panelReady = true;
                }, 750); // wait for 700ms animation
            },

            submitPreciseInterest() {
                if (!this.userHasPhone && (!this.tempPhone || this.tempPhone.length < 8)) {
                    alert('Veuillez entrer un numéro de téléphone valide.');
                    return;
                }

                fetch(`/espace-jeune/establishments/${this.estDetails.id}/interest-precise`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ 
                        form_data: this.formData,
                        phone: this.tempPhone 
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        this.estDetails.user_has_interest = true;
                        this.userHasPhone = true;
                        this.sidebarOpen = false;
                        window.dispatchEvent(new CustomEvent('toast', { 
                            detail: { message: data.message, type: 'success' } 
                        }));
                    }
                });
            }
        }
    }
</script>