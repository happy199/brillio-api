<script>
    function creditDistribution(config) {
        return {
            showModal: false,
            showFeedbackModal: false,
            feedbackType: 'success',
            feedbackMessage: '',
            loading: false,
            targetType: 'all',
            targetUserId: null,
            targetUserName: '',
            amount: 5,

            // From config
            totalUsersAll: config.totalUsers,
            balance: config.balance,
            distributeUrl: config.distributeUrl,
            csrfToken: config.csrfToken,

            get actualUserCount() {
                return this.targetType === 'all' ? Number(this.totalUsersAll) : 1;
            },

            get totalCost() {
                const amt = parseInt(this.amount) || 0;
                return amt * this.actualUserCount;
            },

            get isInsufficient() {
                return this.totalCost > this.balance;
            },

            openModal(type, id = null, name = '') {
                this.targetType = type;
                this.targetUserId = id;
                this.targetUserName = name;
                this.amount = 5;
                this.showModal = true;
            },

            closeFeedback() {
                this.showFeedbackModal = false;
                if (this.feedbackType === 'success') {
                    window.location.reload();
                }
            },

            async submitDistribution() {
                if (this.isInsufficient || Number(this.amount) < 1) return;

                this.loading = true;

                try {
                    const response = await fetch(this.distributeUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.csrfToken
                        },
                        body: JSON.stringify({
                            amount: this.amount,
                            target: this.targetType,
                            user_ids: this.targetType === 'single' ? [this.targetUserId] : []
                        })
                    });

                    const data = await response.json();

                    this.loading = false;
                    this.showModal = false;

                    if (data.success) {
                        this.feedbackType = 'success';
                        this.feedbackMessage = data.message;
                        this.showFeedbackModal = true;
                    } else {
                        this.feedbackType = 'error';
                        this.feedbackMessage = data.message || 'Une erreur est survenue.';
                        this.showFeedbackModal = true;
                    }
                } catch (error) {
                    this.loading = false;
                    this.showModal = false;
                    this.feedbackType = 'error';
                    this.feedbackMessage = 'Erreur de connexion au serveur.';
                    this.showFeedbackModal = true;
                }
            }
        }
    }
</script>