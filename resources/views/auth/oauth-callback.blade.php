<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Connexion en cours - Brillio</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .loader {
            width: 48px;
            height: 48px;
            border: 5px solid #FFF;
            border-bottom-color: transparent;
            border-radius: 50%;
            display: inline-block;
            box-sizing: border-box;
            animation: rotation 1s linear infinite;
        }

        @keyframes rotation {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body
    class="min-h-screen bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500 flex items-center justify-center">
    <div class="text-center text-white max-w-md px-4">
        <div class="loader mx-auto mb-6"></div>
        <h1 class="text-2xl font-bold mb-2">Connexion en cours...</h1>
        <p class="text-white/80" id="status-message">Verification de vos informations</p>
        <div class="mt-6 p-4 bg-white/10 backdrop-blur-sm rounded-xl border border-white/20 text-white text-sm"
            id="error-container" style="display: none;">
            <p id="error-message" class="break-words"></p>
        </div>
    </div>

    <script>
        (function  () {
            const processUrl = "{{ $processUrl }}";
            const errorUrl = "{{ $errorUrl }}";
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

            // Fonction pour parser les parametres du hash
            function parseHash(hash) {
                const params = {};
                if (hash && hash.length > 1) {
                    const hashString = hash.substring(1);
                    const pairs = hashString.split('&');
                    for (const pair of pairs) {
                        const [key, value] = pair.split('=');
                        if (key) {
                            params[decodeURIComponent(key)] = decodeURIComponent(value || '');
                        }
                    }
                }
                return params;
            }

            // Fonction pour parser les query params
            function parseQuery(search) {
                const params = {};
                if (search && search.length > 1) {
                    const queryString = search.substring(1);
                    const pairs = queryString.split('&');
                    for (const pair of pairs) {
                        const [key, value] = pair.split('=');
                        if (key) {
                            params[decodeURIComponent(key)] = decodeURIComponent(value || '');
                        }
                    }
                }
                return params;
            }

            async function processAuth() {
                try {
                    let hashParams = parseHash(window.location.hash);
                    let queryParams = parseQuery(window.location.search);

                    console.log('Hash params:', hashParams);
                    console.log('Query params:', queryParams);

                    // Verifier si on a un access_token dans le hash
                    if (hashParams.access_token) {
                        document.getElementById('status-message').textContent = 'Token trouve, authentification...';

                        const response = await fetch(processUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                access_token: hashParams.access_token,
                                refresh_token: hashParams.refresh_token || null,
                                token_type: hashParams.token_type || 'bearer',
                                expires_in: hashParams.expires_in || null,
                                expires_at: hashParams.expires_at || null
                            })
                        });

                        // Verifier le status HTTP
                        if (!response.ok) {
                            const text = await response.text();
                            console.error('Server error response:', text);
                            try {
                                const data = JSON.parse(text);
                                throw new Error(data.error || data.message || 'Erreur serveur');
                            } catch (e) {
                                if (e.message && e.message !== 'Unexpected token' && !e.message.startsWith('Erreur serveur')) {
                                    throw e;
                                }
                                throw new Error('Une erreur est survenue lors de l\'authentification');
                            }
                        }

                        const data = await response.json();

                        if (data.success && data.redirect) {
                            document.getElementById('status-message').textContent = 'Redirection...';
                            window.location.href = data.redirect;
                        } else {
                            throw new Error(data.error || 'Erreur lors de l\'authentification');
                        }
                    }
                    // Verifier si on a un code dans les query params (Authorization Code flow)
                    else if (queryParams.code) {
                        document.getElementById('status-message').textContent = 'Code trouve, echange en cours...';

                        const response = await fetch(processUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                code: queryParams.code
                            })
                        });

                        if (!response.ok) {
                            const text = await response.text();
                            console.error('Server error response:', text);
                            try {
                                const data = JSON.parse(text);
                                throw new Error(data.error || data.message || 'Erreur serveur');
                            } catch (e) {
                                if (e.message && e.message !== 'Unexpected token' && !e.message.startsWith('Erreur serveur')) {
                                    throw e;
                                }
                                throw new Error('Une erreur est survenue lors de l\'authentification');
                            }
                        }

                        const data = await response.json();

                        if (data.success && data.redirect) {
                            document.getElementById('status-message').textContent = 'Redirection...';
                            window.location.href = data.redirect;
                        } else {
                            throw new Error(data.error || 'Erreur lors de l\'authentification');
                        }
                    }
                    // Verifier si on a une erreur
                    else if (hashParams.error || queryParams.error) {
                        const errorDesc = hashParams.error_description || queryParams.error_description || 'Erreur inconnue';
                        throw new Error(errorDesc);
                    }
                    else {
                        // Aucun token ni code trouve, attendre un peu et reessayer
                        setTimeout(() => {
                            hashParams = parseHash(window.location.hash);
                            if (hashParams.access_token) {
                                processAuth();
                            } else {
                                const err = new Error('Aucune information d\'authentification trouvee');
                                showError(err.message);
                            }
                        }, 500);
                        return;
                    }
                } catch (error) {
                    console.error('Auth error:', error);
                    showError(error.message);
                }
            }

            function showError(message) {
                document.getElementById('status-message').textContent = 'Une erreur est survenue';
                // Utiliser innerHTML pour afficher le HTML (br, strong, etc.)
                document.getElementById('error-message').innerHTML = message;
                document.getElementById('error-container').style.display = 'block';

                // Masquer le loader
                document.querySelector('.loader').style.display = 'none';

                // Rediriger vers la connexion jeune apres 15 secondes
                setTimeout(() => {
                    window.location.href = '/jeune/connexion';
                }, 15000);
            }

            // Lancer le traitement au chargement
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', processAuth);
            } else {
                processAuth();
            }
        })();
    </script>
</body>

</html>