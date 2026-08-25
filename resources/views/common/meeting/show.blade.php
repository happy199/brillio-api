<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Séance : {{ $session->title }} - Brillio</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

@php
    $exitUrl = $isMentor ? route('mentor.mentorship.sessions.show', $session) : route('jeune.sessions.show', $session);
    if ($user && $user->isOrganization()) {
        $exitUrl = route('organization.sessions.show', $session);
    }
@endphp

<body class="bg-gray-900 h-screen flex flex-col overflow-hidden">
    <!-- Header Sécurisé -->
    <header class="bg-black/50 text-white p-3 flex items-center justify-between border-b border-gray-700">
        <div class="flex items-center gap-3">
            <span class="font-bold text-lg tracking-tight">{{ isset($current_organization) ? $current_organization->name : 'Brillio' }}<span class="text-indigo-500">Live</span></span>
            <div class="h-4 w-px bg-gray-600"></div>
            <div>
                <h1 class="font-bold text-sm leading-tight">{{ $session->title }}</h1>
                <p class="text-xs text-gray-400">Avec {{ $session->mentor->name }} et
                    {{ $session->mentees->pluck('name')->join(', ') }}
                </p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <div
                class="hidden md:flex items-center gap-2 text-xs text-yellow-500 bg-yellow-400/10 px-3 py-1.5 rounded-full border border-yellow-400/20">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                    </path>
                </svg>
                Ne partagez pas l'URL de cette page.
            </div>

            <button id="recording-btn" type="button" onclick="toggleVideoRecording()"
                class="bg-indigo-600 hover:bg-indigo-700 text-white px-3.5 py-2 rounded-lg text-sm font-bold transition flex items-center gap-2 cursor-pointer shadow">
                <span class="w-2.5 h-2.5 rounded-full bg-red-500 animate-pulse"></span>
                <span id="recording-btn-text">Démarrer l'enregistrement</span>
            </button>

            <button id="exit-btn" type="button" onclick="finishCallAndExit()"
                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-bold transition flex items-center gap-2 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                    </path>
                </svg>
                <span id="exit-btn-text">Quitter la séance</span>
            </button>
        </div>
    </header>

    <!-- Jitsi Container -->
    <main class="flex-1 relative w-full h-full bg-black">
        <div id="meet" class="w-full h-full"></div>
    </main>

    <!-- Jitsi External API -->
    <script id="jitsi-api" nonce="{{ request()->attributes->get('csp_nonce') }}" src="https://8x8.vc/{{ $appId }}/external_api.js" async></script>
    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        let isExiting = false;
        let mediaRecorder = null;
        let recordedChunks = [];
        let recordingStartTime = null;
        let recordingTimerInterval = null;
        let isUploadingRecording = false;

        async function toggleVideoRecording() {
            if (mediaRecorder && mediaRecorder.state === 'recording') {
                stopAndUploadVideoRecording();
            } else {
                startVideoRecording();
            }
        }

        async function startVideoRecording() {
            try {
                let stream;
                try {
                    stream = await navigator.mediaDevices.getDisplayMedia({
                        video: { mediaSource: 'screen' },
                        audio: true
                    });
                } catch (e) {
                    stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
                }

                recordedChunks = [];
                mediaRecorder = new MediaRecorder(stream, { mimeType: 'video/webm;codecs=vp8,opus' });

                mediaRecorder.ondataavailable = (event) => {
                    if (event.data && event.data.size > 0) {
                        recordedChunks.push(event.data);
                    }
                };

                mediaRecorder.onstop = async () => {
                    stream.getTracks().forEach(track => track.stop());
                    if (recordedChunks.length > 0) {
                        await uploadRecordedVideo();
                    }
                };

                mediaRecorder.start(1000);
                recordingStartTime = Date.now();
                updateRecordingButton(true);

                recordingTimerInterval = setInterval(() => {
                    const elapsedSeconds = Math.floor((Date.now() - recordingStartTime) / 1000);
                    const mins = String(Math.floor(elapsedSeconds / 60)).padStart(2, '0');
                    const secs = String(elapsedSeconds % 60).padStart(2, '0');
                    const btnText = document.getElementById('recording-btn-text');
                    if (btnText) {
                        btnText.innerText = `Arrêter l'enregistrement (${mins}:${secs})`;
                    }
                }, 1000);

            } catch (err) {
                console.error('Erreur démarrage enregistrement:', err);
                alert("Impossible d'accéder au flux vidéo pour l'enregistrement. Vérifiez les autorisations de votre navigateur.");
            }
        }

        function stopAndUploadVideoRecording() {
            if (mediaRecorder && mediaRecorder.state !== 'inactive') {
                clearInterval(recordingTimerInterval);
                mediaRecorder.stop();
                updateRecordingButton(false);
            }
        }

        function updateRecordingButton(isRecording) {
            const btn = document.getElementById('recording-btn');
            const btnText = document.getElementById('recording-btn-text');
            if (!btn || !btnText) return;

            if (isRecording) {
                btn.classList.remove('bg-indigo-600', 'hover:bg-indigo-700');
                btn.classList.add('bg-amber-600', 'hover:bg-amber-700');
                btnText.innerText = "Arrêter l'enregistrement (00:00)";
            } else {
                btn.classList.remove('bg-amber-600', 'hover:bg-amber-700');
                btn.classList.add('bg-indigo-600', 'hover:bg-indigo-700');
                btnText.innerText = "Démarrer l'enregistrement";
            }
        }

        async function uploadRecordedVideo() {
            if (isUploadingRecording || recordedChunks.length === 0) return;
            isUploadingRecording = true;

            const blob = new Blob(recordedChunks, { type: 'video/webm' });
            const formData = new FormData();
            formData.append('video', blob, 'recording_{{ $session->id }}.webm');

            try {
                const response = await fetch('{{ route("meeting.upload-recording", $session) }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                });
                const data = await response.json();
                console.log('Enregistrement vidéo envoyé:', data);
            } catch (err) {
                console.error('Erreur lors du téléversement vidéo:', err);
            } finally {
                isUploadingRecording = false;
            }
        }

        function finishCallAndExit() {
            if (isExiting) return;
            isExiting = true;

            const exitBtnText = document.getElementById('exit-btn-text');
            if (exitBtnText) {
                exitBtnText.innerText = "Fermeture...";
            }

            if (mediaRecorder && mediaRecorder.state !== 'inactive') {
                try {
                    stopAndUploadVideoRecording();
                } catch (e) {}
            }

            if (window.jitsiApi) {
                try {
                    window.jitsiApi.executeCommand('hangup');
                } catch (e) {}
            }

            setTimeout(() => {
                window.location.href = "{{ $exitUrl }}";
            }, 400);
        }

        (function() {
            const jitsiScript = document.getElementById('jitsi-api');
            if (window.JitsiMeetExternalAPI) {
                initJitsi();
            } else {
                jitsiScript.addEventListener('load', initJitsi);
            }
        })();

        function initJitsi() {
            const domain = '8x8.vc';
            const options = {
                roomName: "{{ $appId }}/{{ $roomName }}",
                width: '100%',
                height: '100%',
                lang: 'fr',
                parentNode: document.querySelector('#meet'),
                jwt: "{{ $jwt }}",
                userInfo: {
                    displayName: "{{ $user->name }}",
                    email: "{{ $user->email }}"
                },
                configOverwrite: {
                    startWithAudioMuted: false,
                    startWithVideoMuted: false,
                    prejoinPageEnabled: false,
                },
                interfaceConfigOverwrite: {
                    SHOW_JITSI_WATERMARK: false,
                    SHOW_WATERMARK_FOR_GUESTS: false,
                    TOOLBAR_BUTTONS: [
                        'microphone', 'camera', 'closedcaptions', 'desktop', 'fullscreen',
                        'fodeviceselection', 'hangup', 'profile', 'chat', 'recording',
                        'livestreaming', 'etherpad', 'sharedvideo', 'settings', 'raisehand',
                        'videoquality', 'filmstrip', 'invite', 'feedback', 'stats', 'shortcuts',
                        'tileview', 'videobackgroundblur', 'download', 'help', 'mute-everyone',
                        'e2ee'
                    ]
                }
            };

            const api = new JitsiMeetExternalAPI(domain, options);
            window.jitsiApi = api;

            // Handle Hangup
            api.addEventListeners({
                videoConferenceLeft: function () {
                    finishCallAndExit();
                },
                readyToClose: function () {
                    finishCallAndExit();
                }
            });

            // --- Système de Transcription Client-Side Gratuit ---
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

            if (SpeechRecognition) {
                // Créer un indicateur visuel discret
                const indicator = document.createElement('div');
                indicator.id = 'transcription-status';
                const wrapper = document.createElement('div');
                wrapper.className = 'flex items-center gap-2 bg-black/80 text-white px-3 py-1.5 rounded-full text-[10px] border border-green-500/30';
                const pulse = document.createElement('div');
                pulse.className = 'w-2 h-2 rounded-full bg-green-500 animate-pulse';
                wrapper.appendChild(pulse);
                wrapper.appendChild(document.createTextNode('Transcription active pour {{ $user->name }}'));
                indicator.appendChild(wrapper);
                indicator.className = 'absolute bottom-20 left-4 z-50 pointer-events-none opacity-50 hover:opacity-100 transition-opacity';
                document.body.appendChild(indicator);

                console.log('Transcription IA : Activée pour {{ $user->name }}');
                const recognition = new SpeechRecognition();
                recognition.lang = 'fr-FR';
                recognition.continuous = true;
                recognition.interimResults = false;

                recognition.onresult = (event) => {
                    const result = event.results[event.results.length - 1];
                    if (result.isFinal) {
                        const text = result[0].transcript.trim();
                        if (text && text.length > 2) {
                            // Vérifier si le micro est coupé dans Jitsi avant d'envoyer
                            // Cela évite de transcrire le son des enceintes capturé par le micro
                            api.isAudioMuted().then(muted => {
                                if (!muted) {
                                    sendTranscriptionFragment(text);
                                } else {
                                    console.log('Transcription bloquée : Utilisateur muet');
                                }
                            });
                        }
                    }
                };

                recognition.onerror = (event) => {
                    console.error('Erreur reconnaissance vocale:', event.error);
                    if (event.error === 'not-allowed') {
                        const wrapperErr = document.createElement('div');
                        wrapperErr.className = 'flex items-center gap-2 bg-black/80 text-white px-3 py-1.5 rounded-full text-[10px] border border-red-500/30';
                        const dot = document.createElement('div');
                        dot.className = 'w-2 h-2 rounded-full bg-red-500';
                        wrapperErr.appendChild(dot);
                        wrapperErr.appendChild(document.createTextNode('Micro bloqué par le navigateur'));
                        indicator.replaceChildren(wrapperErr);
                    }
                };

                recognition.onend = () => {
                    try {
                        recognition.start();
                    } catch(e) {
                        // Déjà démarré ou erreur fatale
                    }
                };

                function sendTranscriptionFragment(text) {
                    fetch('{{ route("meeting.append-transcription", $session) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            text: text,
                            speaker: {!! json_encode($user->name) !!},
                            timestamp: Math.floor(Date.now() / 1000)
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        // Feedback visuel rapide
                        indicator.classList.remove('opacity-50');
                        indicator.classList.add('opacity-100');
                        setTimeout(() => indicator.classList.add('opacity-50'), 2000);
                    })
                    .catch(err => console.error('Erreur envoi transcription:', err));
                }

                try {
                    recognition.start();
                } catch(e) {
                    console.error('Erreur démarrage transcription:', e);
                }
            } else {
                console.warn('SpeechRecognition non supporté par ce navigateur.');
                // Alerter l'utilisateur s'il n'est pas sur Chrome
                const indicator = document.createElement('div');
                const wrapperUnsupported = document.createElement('div');
                wrapperUnsupported.className = 'flex items-center gap-2 bg-black/80 text-white px-3 py-1.5 rounded-full text-[10px] border border-yellow-500/30';
                const yellowDot = document.createElement('div');
                yellowDot.className = 'w-2 h-2 rounded-full bg-yellow-500';
                wrapperUnsupported.appendChild(yellowDot);
                wrapperUnsupported.appendChild(document.createTextNode('Transcription non supportée (Utilisez Chrome)'));
                indicator.appendChild(wrapperUnsupported);
                indicator.className = 'absolute bottom-20 left-4 z-50 pointer-events-none';
                document.body.appendChild(indicator);
            }
        }
    </script>
</body>

</html>