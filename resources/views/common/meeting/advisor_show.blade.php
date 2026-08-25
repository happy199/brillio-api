<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entretien Vidéo Conseiller - Brillio</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

@php
    $exitUrl = $isCounselor ? route('admin.chat.show', $call->conversation_id) : route('jeune.chat');
@endphp

<body class="bg-gray-900 h-screen flex flex-col overflow-hidden">
    <!-- Header Sécurisé -->
    <header class="bg-black/50 text-white p-3 flex items-center justify-between border-b border-gray-700">
        <div class="flex items-center gap-3">
            <span class="font-bold text-lg tracking-tight">{{ isset($current_organization) ? $current_organization->name : 'Brillio' }}<span class="text-indigo-500">Visio</span></span>
            <div class="h-4 w-px bg-gray-600"></div>
            <div>
                <h1 class="font-bold text-sm leading-tight">Entretien d'Orientation Vidéo</h1>
                <p class="text-xs text-gray-400">
                    Conseiller : {{ $call->counselor?->name ?? 'Brillio' }} | Élève : {{ $call->user?->name }}
                </p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button onclick="finishCallAndExit()"
                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-bold transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                    </path>
                </svg>
                Quitter l'entretien
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
        function finishCallAndExit() {
            fetch('{{ route("advisor-meeting.finish", $call) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            }).finally(() => {
                window.location.href = "{{ $exitUrl }}";
            });
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
                }
            };

            const api = new JitsiMeetExternalAPI(domain, options);

            api.addEventListeners({
                videoConferenceLeft: function () {
                    finishCallAndExit();
                }
            });

            // --- Système de Transcription Client-Side Gratuit ---
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

            if (SpeechRecognition) {
                const indicator = document.createElement('div');
                indicator.id = 'transcription-status';
                const wrapper = document.createElement('div');
                wrapper.className = 'flex items-center gap-2 bg-black/80 text-white px-3 py-1.5 rounded-full text-[10px] border border-indigo-500/30';
                const pulse = document.createElement('div');
                pulse.className = 'w-2 h-2 rounded-full bg-indigo-500 animate-pulse';
                wrapper.appendChild(pulse);
                wrapper.appendChild(document.createTextNode('Transcription & Résumé IA actifs'));
                indicator.appendChild(wrapper);
                indicator.className = 'absolute bottom-20 left-4 z-50 pointer-events-none opacity-60 hover:opacity-100 transition-opacity';
                document.body.appendChild(indicator);

                const recognition = new SpeechRecognition();
                recognition.lang = 'fr-FR';
                recognition.continuous = true;
                recognition.interimResults = false;

                recognition.onresult = (event) => {
                    const result = event.results[event.results.length - 1];
                    if (result.isFinal) {
                        const text = result[0].transcript.trim();
                        if (text && text.length > 2) {
                            sendTranscriptionFragment(text);
                        }
                    }
                };

                recognition.onerror = (event) => {
                    console.error('Erreur reconnaissance vocale:', event.error);
                };

                recognition.onend = () => {
                    try {
                        recognition.start();
                    } catch(e) {}
                };

                function sendTranscriptionFragment(text) {
                    fetch('{{ route("advisor-meeting.transcribe", $call) }}', {
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
                    }).catch(err => console.error('Erreur envoi transcription:', err));
                }

                try {
                    recognition.start();
                } catch(e) {}
            }
        }
    </script>
</body>
</html>
