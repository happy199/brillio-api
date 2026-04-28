<?php

namespace App\Services;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service pour l'integration du chatbot DeepSeek via OpenRouter
 *
 * Ce service gere :
 * - L'envoi de messages a l'API OpenRouter (DeepSeek R1)
 * - La construction du contexte avec l'historique des messages
 * - Le stockage des messages utilisateur et assistant
 *
 * @see https://openrouter.ai/docs
 */
class BrillioIAService
{
    private $apiKey;

    private $apiUrl;

    private $model;

    private $maxTokens;

    private $temperature;

    private $siteUrl;

    private $siteName;

    private $systemPrompt;

    /**
     * Prompt systeme pour orienter l'IA vers les conseils d'orientation
     */
    private const SYSTEM_PROMPT_TEXT = "Tu es Brillio, un conseiller en orientation professionnelle spécialiste pour les jeunes africains.\n\nREGLES IMPORTANTES DE COMMUNICATION:\n- Tu DOIS TOUJOURS tutoyer l'utilisateur (jamais de \"vous\", uniquement \"tu\")\n- Tu DOIS utiliser le prénom de l'utilisateur régulièrement dans tes réponses pour personnaliser l'échange\n- Sois chaleureux, amical et proche comme un grand frère ou une grande sœur bienveillant(e)\n\nCONSIGNES DE FORMATAGE :\n- Utilise un Markdown propre, aéré et professionnel.\n- N'utilise JAMAIS de balises techniques (comme <answer>, <think>, etc.) dans ton texte final.\n- Les titres doivent être en gras ou utiliser des structures Markdown standards (ex: ### Titre).\n- Evite les suites de signes superflus (ex: Ne laisse pas d'astérisques isolés).\n\nTon rôle est de :\n- Aider les jeunes à découvrir leurs talents et intérêts\n- Donner des conseils d'orientation adaptés au contexte africain\n- Informer sur les métiers, les formations et les opportunités de carrière\n- Encourager et motiver les jeunes dans leurs parcours\n- Répondre aux questions sur les études et le monde professionnel\n\nTes réponses doivent être :\n- Personnalisées (utilise le prénom!)\n- Bienveillantes et encourageantes\n- Pratiques et concrètes\n- Adaptées au contexte africain (pays, économie, opportunités locales)\n- Claires et accessibles\n\nRéponds toujours en français sauf si l'utilisateur s'adresse à toi dans une autre langue.";

    public function __construct()
    {
        $this->apiKey = config('services.openrouter.api_key', env('OPENROUTER_API_KEY'));
        $this->apiUrl = config('services.openrouter.api_url', 'https://openrouter.ai/api/v1/chat/completions');
        $this->model = config('services.openrouter.model', 'deepseek/deepseek-r1:free');
        $this->maxTokens = (int) config('services.openrouter.max_tokens', 2000);
        $this->temperature = (float) config('services.openrouter.temperature', 0.7);
        $this->siteUrl = config('services.openrouter.site_url', 'https://www.brillio.africa');
        $this->siteName = config('services.openrouter.site_name', 'Brillio');

        // Initialisation du prompt systeme
        $this->systemPrompt = self::SYSTEM_PROMPT_TEXT;
    }

    /**
     * Nettoie la reponse de l'IA (supprime les balises <think> de DeepSeek R1 et extrait <answer>)
     */
    private function cleanResponse($content, $formatting = true)
    {
        // 1. Suppression des balises de réflexion standard (DeepSeek R1)
        $content = preg_replace('/<think>.*?<\/think>/s', '', $content);
        $content = str_replace(['<think>', '</think>'], '', $content);

        // 2. Suppression de toutes les occurrences de balises techniques XML/HTML (comme <answer>)
        // On le fait de manière permissive pour attraper les balises mal fermées ou répétées
        $content = preg_replace('/<answer>|<\/answer>/i', '', $content);
        $content = preg_replace('/<[a-z0-9_\-]+>.*<\/[a-z0-9_\-]+>/i', '', $content); // Supprime toute autre balise xml potentielle

        if ($formatting) {
            // Nettoyage des astérisques isolés (souvent des restes de titres mal formés)
            $content = preg_replace('/^\s*\*\*\s*$/m', '', $content);
            $content = preg_replace('/^\s*\*\s*$/m', '', $content);

            // Remplacer les listes numérotées collées pour assurer un retour à la ligne avant
            // Mais seulement si ce n'est pas déjà le cas
            $content = preg_replace('/([^\n])(\d+\.\s)/u', "$1\n$2", $content);

            // Nettoyer les retours à la ligne multiples (max 2)
            $content = preg_replace('/\n{3,}/', "\n\n", $content);
        }

        // Trim final
        $content = trim($content);

        return $content;
    }

    public function createConversation(User $user, $title = null)
    {
        return ChatConversation::create([
            'user_id' => $user->id,
            'title' => ($title !== null) ? $title : 'Nouvelle conversation',
        ]);
    }

    /**
     * Envoie un message et recupere la reponse de l'IA
     */
    public function sendMessage(ChatConversation $conversation, $userMessage)
    {
        // 1. Enregistrer le message utilisateur
        $userChatMessage = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'role' => ChatMessage::ROLE_USER,
            'content' => $userMessage,
        ]);

        // 2. Construire le contexte avec l'historique (max 10 derniers messages)
        $messages = $this->buildMessagesContext($conversation);

        // 3. Appeler l'API OpenRouter
        $aiResponse = $this->callOpenRouterApi($messages);

        // 4. Enregistrer la reponse de l'IA
        $assistantMessage = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'role' => ChatMessage::ROLE_ASSISTANT,
            'content' => $aiResponse,
        ]);

        // 5. Mettre a jour le titre si c'est le premier message
        if ($conversation->messages()->count() <= 2) {
            $conversation->generateTitle();
        }

        return $assistantMessage;
    }

    /**
     * Construit le tableau de messages pour l'API avec contexte
     */
    private function buildMessagesContext(ChatConversation $conversation)
    {
        $messages = [
            [
                'role' => 'system',
                'content' => $this->systemPrompt,
            ],
        ];

        // Ajouter le contexte utilisateur si disponible
        $user = $conversation->user;
        if ($user) {
            $userContext = $this->buildUserContext($user);
            if ($userContext) {
                $userName = isset($user->name) ? $user->name : '';
                $nameParts = explode(' ', $userName);
                $firstName = isset($nameParts[0]) ? $nameParts[0] : 'ami(e)';
                if (empty($firstName)) {
                    $firstName = 'ami(e)';
                }

                array_push($messages, [
                    'role' => 'system',
                    'content' => "CONTEXTE UTILISATEUR - UTILISE CES INFORMATIONS POUR PERSONNALISER TES REPONSES:\n".$userContext."\n\nIMPORTANT: Tu parles a ".$firstName.'. Utilise son prenom dans tes reponses et tutoie-le/la toujours!',
                ]);
            }
        }

        // Ajouter les 10 derniers messages de la conversation
        $historyMessages = $conversation->getLastMessages(10);
        foreach ($historyMessages as $message) {
            array_push($messages, [
                'role' => $message->role,
                'content' => $message->content,
            ]);
        }

        return $messages;
    }

    /**
     * Construit le contexte utilisateur pour personnaliser les reponses
     */
    private function buildUserContext(User $user)
    {
        $context = [];

        if (isset($user->name) && $user->name) {
            array_push($context, 'Nom : '.$user->name);
        }

        if (isset($user->country) && $user->country) {
            array_push($context, 'Pays : '.$user->country);
        }

        if (isset($user->city) && $user->city) {
            array_push($context, 'Ville : '.$user->city);
        }

        if (isset($user->date_of_birth) && $user->date_of_birth) {
            $age = $user->date_of_birth->age;
            array_push($context, 'Age : '.$age.' ans');
        }

        // Ajouter le type de personnalite si disponible
        $personalityTest = $user->personalityTest;
        if ($personalityTest && $personalityTest->isCompleted()) {
            array_push($context, 'Type de personnalite : '.$personalityTest->personality_type.' ('.$personalityTest->personality_label.')');
        }

        return implode(', ', $context);
    }

    /**
     * Verifie si la cle API est configuree
     */
    public function isApiKeyConfigured()
    {
        return ! empty($this->apiKey) && $this->apiKey !== 'your_openrouter_api_key_here';
    }

    /**
     * Analyse un texte brut avec un prompt systeme specifique (sans historique de conversation)
     * Utile pour des taches uniques comme le parsing de documents
     */
    public function analyzeText($prompt, $systemPrompt = null, $overrideModel = null)
    {
        $messages = [
            [
                'role' => 'system',
                'content' => ($systemPrompt !== null) ? $systemPrompt : $this->systemPrompt,
            ],
            [
                'role' => 'user',
                'content' => $prompt,
            ],
        ];

        return $this->callOpenRouterApi($messages, false, $overrideModel);
    }

    /**
     * Nettoie une reponse JSON (supprime les balises markdown ```json ... ```)
     */
    public function cleanJson($content)
    {
        // 1. Supprimer les balises markdown de code (```json ... ```)
        $content = preg_replace('/```(?:json)?\s*(.*?)\s*```/s', '$1', $content);

        // 2. Extraire la premiere structure JSON complete (entre { et } ou [ et ])
        $matches = [];
        if (preg_match('/(\{.*\}|\[.*\])/s', $content, $matches)) {
            $content = $matches[0];
        }

        // 3. Supprimer les commentaires JS style (// ...) UNIQUEMENT s'ils sont en début de ligne
        // pour ne pas casser les URLs (http://...)
        $content = preg_replace('/^\s*\/\/.*/m', '', $content);

        // 4. Supprimer les virgules traignantes (trailing commas) avant ] ou }
        $content = preg_replace('/,\s*([\]\}])/', '$1', $content);

        return trim($content);
    }

    /**
     * Appelle l'API OpenRouter (avec gestion de resilience et fallback)
     */
    private function callOpenRouterApi($messages, $formatting = true, $attemptedModel = null)
    {
        $currentModel = $attemptedModel ?? $this->model;
        $result = '';

        Log::info('=== APPEL API OPENROUTER ===', [
            'api_url' => $this->apiUrl,
            'model' => $currentModel,
            'attempt' => $attemptedModel ? 'fallback' : 'primary',
            'api_key_configured' => $this->isApiKeyConfigured(),
        ]);

        try {
            if (! $this->isApiKeyConfigured()) {
                Log::warning('OpenRouter API key not configured');
                $result = $this->getFallbackResponse($messages);
            } else {
                // 1. Tentative avec Retry pour les erreurs reseau/timeout
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer '.$this->apiKey,
                    'HTTP-Referer' => $this->siteUrl,
                    'X-Title' => $this->siteName,
                    'Content-Type' => 'application/json',
                ])
                    ->timeout(60)
                    ->retry(2, 500)
                    ->post($this->apiUrl, [
                        'model' => $currentModel,
                        'messages' => $messages,
                        'max_tokens' => $this->maxTokens,
                        'temperature' => $this->temperature,
                    ]);

                // 2. Gestion des erreurs et fallbacks
                if ($response->status() === 429 || $response->status() >= 500) {
                    if (! $attemptedModel) {
                        $fallbackModel = 'google/gemini-flash-1.5-8b';
                        Log::warning("OpenRouter saturé sur {$currentModel}. Basculement sur {$fallbackModel}");
                        $result = $this->callOpenRouterApi($messages, $formatting, $fallbackModel);
                    } else {
                        Log::error('Echec critique OpenRouter sur modèle de secours.');
                        $result = $this->getFallbackResponse($messages);
                    }
                } elseif ($response->successful()) {
                    $data = $response->json();
                    $content = $data['choices'][0]['message']['content'] ?? null;
                    if ($content) {
                        $result = $this->cleanResponse($content, $formatting);
                    } else {
                        throw new \App\Exceptions\OpenRouterException('Réponse API sans contenu', $response->status());
                    }
                } else {
                    $error = json_decode($response->body(), true)['error']['message'] ?? 'Erreur inconnue';
                    throw new \App\Exceptions\OpenRouterException("OpenRouter Error: {$error}", $response->status());
                }
            }
        } catch (\Exception $e) {
            Log::error('OpenRouter exception: '.$e->getMessage());
            $result = $result ?: $this->getFallbackResponse($messages);
        }

        return $result;
    }

    /**
     * Retourne une reponse de secours si l'API est indisponible
     */
    private function getFallbackResponse($messages)
    {
        $lastUserMessage = '';
        foreach (array_reverse($messages) as $message) {
            if ($message['role'] === 'user') {
                $lastUserMessage = strtolower($message['content']);
                break;
            }
        }

        // Reponses de base selon les mots-cles
        if (str_contains($lastUserMessage, 'bonjour') || str_contains($lastUserMessage, 'salut')) {
            return "Bonjour ! Je suis Brillio, ton conseiller en orientation. Je suis la pour t'aider a decouvrir les metiers et formations qui correspondent a ton profil. Comment puis-je t'aider aujourd'hui ?";
        }

        if (str_contains($lastUserMessage, 'metier') || str_contains($lastUserMessage, 'travail')) {
            return "C'est une excellente question ! Pour te conseiller au mieux sur les metiers, j'aurais besoin de mieux te connaitre. Quelles sont tes matieres preferees a l'ecole ? Et qu'est-ce qui te passionne en dehors des etudes ?";
        }

        if (str_contains($lastUserMessage, 'formation') || str_contains($lastUserMessage, 'etude')) {
            return "Les formations sont nombreuses en Afrique ! Pour t'orienter, dis-moi : quel est ton niveau d'etudes actuel ? Et dans quel pays te trouves-tu ?";
        }

        if (str_contains($lastUserMessage, 'informatique') || str_contains($lastUserMessage, 'tech')) {
            return "L'informatique est un domaine passionnant avec beaucoup d'opportunites en Afrique ! Tu peux te former en developpement web, mobile, cybersecurite, data science ou intelligence artificielle. Des universites comme l'ESP (Senegal), AIMS (plusieurs pays), ou des bootcamps comme Orange Digital Center proposent d'excellentes formations. Qu'est-ce qui t'attire le plus dans ce domaine ?";
        }

        if (str_contains($lastUserMessage, 'merci')) {
            return "Je t'en prie ! N'hesite pas a revenir vers moi si tu as d'autres questions sur ton orientation. Je suis la pour t'accompagner dans ton parcours. Bonne continuation !";
        }

        return "Merci pour ta question ! Je suis la pour t'aider dans ton orientation professionnelle. Pour te donner les meilleurs conseils, peux-tu me parler un peu de toi ? Par exemple, quelles matieres aimes-tu a l'ecole, ou quels sont tes centres d'interet ?";
    }

    /**
     * Recupere l'historique des conversations d'un utilisateur
     */
    public function getUserConversations(User $user, $limit = 20)
    {
        return $user->chatConversations()
            ->orderBy('updated_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Recupere les messages d'une conversation
     */
    public function getConversationMessages(ChatConversation $conversation, $limit = 50)
    {
        return $conversation->messages()
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get();
    }

    /**
     * Traduit un texte en utilisant DeepSeek
     */
    public function translate($prompt)
    {
        $messages = [
            [
                'role' => 'user',
                'content' => $prompt,
            ],
        ];

        return $this->callOpenRouterApi($messages);
    }

    /**
     * Supprime une conversation
     */
    public function deleteConversation(ChatConversation $conversation)
    {
        return $conversation->delete();
    }

    /**
     * Summarize a meeting transcription into the three report fields.
     */
    public function summarizeTranscription($transcriptionData, $mentorName = 'Le mentor', $menteeNames = 'Le jeune')
    {
        // Conversion de la transcription en texte structuré si c'est un tableau de segments
        if (is_array($transcriptionData)) {
            $transcriptionText = '';
            foreach ($transcriptionData as $segment) {
                if (is_array($segment) && isset($segment['speaker'], $segment['text'])) {
                    $transcriptionText .= '['.($segment['speaker'] ?? 'Inconnu').'] : '.$segment['text']."\n";
                } elseif (is_string($segment)) {
                    $transcriptionText .= $segment."\n";
                }
            }
        } else {
            $transcriptionText = (string) $transcriptionData;
        }

        $systemPrompt = "Tu es un assistant qui analyse des transcriptions de séances de mentorat.\n".
            "Ta mission est d'extraire les informations clés pour remplir un compte rendu de séance selon trois axes :\n".
            "1. PROGRES : Ce qui a été accompli durant la séance.\n".
            "2. OBSTACLES : Les difficultés rencontrées par le jeune.\n".
            "3. OBJECTIFS SMART : Les prochaines étapes concrètes fixées.\n\n".
            "CONTEXTE DES PARTICIPANTS :\n".
            "- Mentor : {$mentorName}\n".
            "- Jeune(s) / Menté(s) : {$menteeNames}\n\n".
            "CONSIGNES IMPORTANTES :\n".
            "- Rédige à la troisième personne.\n".
            "- UTILISE EXCLUSIVEMENT LES NOMS REELS DES PARTICIPANTS fournis ci-dessus pour désigner les personnes (ex: '{$menteeNames} a expliqué...', '{$mentorName} a conseillé...').\n".
            "- Interdiction d'utiliser des termes génériques comme 'Le jeune', 'L'étudiant' ou 'Le mentor'.\n".
            "- Réponds UNIQUEMENT sous forme d'un objet JSON avec les clés suivantes : 'progress', 'obstacles', 'smart_goals'.\n".
            '- Le texte doit être concis et professionnel.';

        // Limiter la taille de la transcription pour éviter de dépasser le contexte
        $truncatedTranscription = \Illuminate\Support\Str::limit($transcriptionText, 15000);

        $prompt = "Voici la transcription de la séance :\n\n".$truncatedTranscription;

        try {
            $response = $this->analyzeText($prompt, $systemPrompt);
            $json = $this->cleanJson($response);

            return json_decode($json, true);
        } catch (\Exception $e) {
            Log::error('Summarize Transcription Error: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Genere 4 metiers supplementaires pour un type MBTI specifique
     */
    public function generateCareers(string $mbtiType, array $existingGlobalTitles, array $currentlySelectedTitles)
    {
        $mbtiType = strtoupper($mbtiType);

        $systemPrompt = "Tu es un expert en orientation professionnelle pour la jeunesse africaine.\n".
            "Ta mission est de proposer 4 nouveaux métiers qui correspondent parfaitement au profil MBTI : {$mbtiType}.\n\n".
            "REGLES :\n".
            '1. Ne propose AUCUN métier présent dans cette liste de titres existants : '.implode(', ', $existingGlobalTitles).".\n".
            '2. Ne propose AUCUN métier présent dans cette liste de titres déjà sélectionnés pour ce test : '.implode(', ', $currentlySelectedTitles).".\n".
            "3. Chaque métier doit être pertinent au contexte africain.\n".
            "4. Tu dois retourner un objet JSON valide.\n\n".
            "FORMAT JSON ATTENDU :\n".
            "{\n".
            "  \"has_new_proposals\": true,\n".
            "  \"careers\": [\n".
            "    {\n".
            "      \"title\": \"Titre du métier\",\n".
            "      \"description\": \"Description courte et inspirante\",\n".
            "      \"african_context\": \"Pourquoi ce métier est une opportunité en Afrique aujourd'hui\",\n".
            "      \"future_prospects\": \"Perspectives d'avenir (ex: Forte croissance, Transformation digitale)\",\n".
            "      \"ai_impact_level\": \"low|medium|high\",\n".
            "      \"match_reason\": \"Pourquoi ce métier convient spécifiquement à un profil {$mbtiType}\",\n".
            "      \"sectors\": [\"tech\", \"business\", \"creative\", etc.]\n".
            "    }\n".
            "  ]\n".
            '}';

        $prompt = "Peux-tu me proposer 4 métiers originaux et porteurs pour un jeune de profil {$mbtiType} en Afrique ?";

        try {
            $response = $this->analyzeText($prompt, $systemPrompt);
            $json = $this->cleanJson($response);
            $data = json_decode($json, true);

            if (isset($data['has_new_proposals']) && $data['has_new_proposals'] === true && isset($data['careers']) && count($data['careers']) >= 4) {
                return $data;
            }

            return ['has_new_proposals' => false, 'careers' => []];
        } catch (\Exception $e) {
            Log::error('Generate Careers Error: '.$e->getMessage());

            return ['has_new_proposals' => false, 'careers' => []];
        }
    }

    /**
     * Reformule les questions du test de personnalité en fonction du contexte utilisateur
     */
    public function reformulatePersonalityQuestions(array $questions, string $userContext)
    {
        $systemPrompt = "Tu es un expert en psychologie et en orientation pour la jeunesse africaine.\n".
            "Ta mission est de reformuler les traits (options gauche et droite) d'un test de personnalité MBTI pour qu'ils soient parfaitement adaptés au contexte de l'utilisateur suivant : {$userContext}.\n\n".
            "REGLES DE REFORMULATION :\n".
            "1. EMPATHIE ET CONTEXTE : Utilise des phrases naturelles, fluides et pleines d'empathie. Propose des situations concrètes qui parlent à cet utilisateur (ex: vie scolaire, loisirs ou ambitions pour un jeune, vie professionnelle pour un actif).\n".
            "2. ADAPTATION : Évite à tout prix les réponses robotiques ou limitées à un seul mot. Développe suffisamment pour que le sens soit clair et humain.\n".
            "3. CONCISION : Reste concis (une phrase courte ou un groupe de mots), mais ne sacrifie jamais la compréhension ou l'humanité de la réponse pour la brièveté.\n".
            "4. FIDELITE : Ne change SURTOUT PAS le sens profond du trait original (modèle MBTI). L'utilisateur doit pouvoir répondre sans ambiguïté.\n".
            "5. TON : Adopte une posture de 'grand frère' ou 'grande sœur' bienveillant(e), direct(e) et encourageant(e).\n".
            "6. FORMAT : Tu dois retourner UNIQUEMENT un objet JSON contenant le tableau des questions reformulées.\n\n".
            "FORMAT JSON ATTENDU :\n".
            "{\n".
            "  \"questions\": [\n".
            "    {\n".
            "      \"id\": 1,\n".
            "      \"left_trait\": \"Reformulation empathique et contextualisée gauche\",\n".
            "      \"right_trait\": \"Reformulation empathique et contextualisée droite\"\n".
            "    }\n".
            "  ]\n".
            '}';

        $prompt = "Voici les 32 questions originales à reformuler pour un(e) {$userContext} :\n\n".json_encode($questions, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        try {
            $response = $this->analyzeText($prompt, $systemPrompt);
            $json = $this->cleanJson($response);
            $data = json_decode($json, true);

            if (isset($data['questions']) && is_array($data['questions']) && count($data['questions']) > 0) {
                // Fusionner les reformulations avec les questions originales pour garder les autres champs (dimension, etc.)
                $reformulatedMap = [];
                foreach ($data['questions'] as $rq) {
                    $reformulatedMap[$rq['id']] = $rq;
                }

                $finalQuestions = [];
                foreach ($questions as $q) {
                    $id = $q['id'];
                    if (isset($reformulatedMap[$id])) {
                        $q['left_trait'] = $reformulatedMap[$id]['left_trait'];
                        $q['right_trait'] = $reformulatedMap[$id]['right_trait'];
                        $q['text'] = "{$q['left_trait']} ou {$q['right_trait']} ?";
                    }
                    $finalQuestions[] = $q;
                }

                return $finalQuestions;
            }

            return $questions; // Fallback si JSON invalide
        } catch (\Exception $e) {
            Log::error('Reformulate Personality Questions Error: '.$e->getMessage());

            return $questions; // Fallback
        }
    }

    /**
     * Genere des suggestions d'etablissements reels bases sur un profil MBTI
     */
    public function generateEstablishments(array $existingNames = [])
    {
        $mbtiProfiles = array_keys(\App\Models\PersonalityTest::PERSONALITY_TYPES);
        $mbtiProfilesStr = implode(', ', $mbtiProfiles);

        $systemPrompt = "Tu es un expert en enseignement supérieur et en formation professionnelle en Afrique.\n".
            "Ta mission est de découvrir 3 universités, écoles ou centres de formation RÉELS et de les cartographier avec les profils MBTI correspondants.\n\n".
            "PRIORITÉS GÉOGRAPHIQUES ET FALLBACK DYNAMIQUE :\n".
            "1. Débute systématiquement ta recherche par le BÉNIN.\n".
            "2. Si tu ne trouves plus de nouveaux établissements pertinents au Bénin (qui ne soient pas dans la liste des existants), tu DOIS immédiatement chercher dans d'autres pays francophones d'Afrique, en suivant cet ordre : Togo, Sénégal, Côte d'Ivoire, Maroc, Algérie, etc.\n".
            "3. Il est interdit de renvoyer 0 établissement sous prétexte d'avoir épuisé un pays. Passe au pays suivant.\n\n".
            "RÈGLES DE VÉRACITÉ ET DE RECHERCHE CRITIQUES :\n".
            "1. Ne propose QUE des établissements qui existent RÉELLEMENT physiquement.\n".
            '2. Ne propose AUCUN établissement présent dans cette liste : '.implode(', ', $existingNames).".\n".
            "3. Tu dois retourner exactement 3 établissements différents.\n".
            "4. EXTRACTION DE CONTACTS OBLIGATOIRE : Tu dois impérativement chercher partout sur internet le numéro de téléphone et l'adresse email officiels de chaque établissement. Ne laisse ces champs vides que si tu as fouillé et que c'est objectivement impossible à trouver. Mieux vaut le standard général de l'université plutôt qu'un blanc.\n".
            "5. EXTRACTION GOOGLE MAPS : Cherche le lien Google Maps pointant vers l'établissement et insère-le dans \"google_maps_url\".\n".
            "6. DESCRIPTION COMPLÈTE : La description ne doit plus faire référence à un profil MBTI, mais doit être une description détaillée tirée de ce qu'on trouve sur internet : présentation, atouts, et diplômes majeurs offerts.\n".
            "7. MAPPING MBTI : Identifie quels profils MBTI, parmi la liste officielle ({$mbtiProfilesStr}), correspondent le mieux à cet établissement (ex: une école polytechnique matchera avec INTJ, INTP, ISTJ. Une école d'art matchera avec ENFP, ISFP). Tu renverras un tableau de ces profils.\n".
            "8. Tu dois retourner un objet JSON valide.\n\n".
            "FORMAT JSON ATTENDU :\n".
            "{\n".
            "  \"establishments\": [\n".
            "    {\n".
            "      \"name\": \"Nom complet de l'établissement\",\n".
            "      \"type\": \"university|training_center\",\n".
            "      \"country\": \"Nom du pays (ex: Bénin, Togo...)\",\n".
            "      \"city\": \"Ville\",\n".
            "      \"description\": \"Description détaillée trouvée sur le web (présentation, atouts, diplômes)\",\n".
            "      \"address\": \"Adresse physique précise\",\n".
            "      \"phone\": \"Contact téléphonique officiel (obligatoire, cherche fort)\",\n".
            "      \"email\": \"Email officiel\",\n".
            "      \"website_url\": \"URL officielle\",\n".
            "      \"google_maps_url\": \"Lien Google Maps public\",\n".
            "      \"tuition_min\": 250000,\n".
            "      \"tuition_max\": 1500000,\n".
            "      \"sectors\": [\"Tech\", \"Management\", \"Santé\", etc.],\n".
            "      \"mbti_types\": [\"INTJ\", \"ENTJ\", \"INTP\"], \n".
            "      \"social_links\": {\n".
            "        \"linkedin\": \"...\",\n".
            "        \"facebook\": \"...\"\n".
            "      }\n".
            "    }\n".
            "  ]\n".
            '}';

        $prompt = 'Peux-tu fouiller le web et me générer 3 nouveaux établissements d\'enseignement de qualité en Afrique francophone (focus initial sur le Bénin, puis au-delà) avec leurs descriptions complètes, tous leurs contacts (tel/email) et me dire à quels profils MBTI ils sont destinés ?';

        try {
            // Utilisation d'un modèle Perplexity optimisé pour la recherche Web en direct
            $response = $this->analyzeText($prompt, $systemPrompt, 'perplexity/sonar');
            $json = $this->cleanJson($response);
            $data = json_decode($json, true);

            if (isset($data['establishments']) && is_array($data['establishments'])) {
                return $data['establishments'];
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Generate Establishments Error: '.$e->getMessage());

            return [];
        }
    }
}
