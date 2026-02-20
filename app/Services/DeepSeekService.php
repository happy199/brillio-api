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
class DeepSeekService
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
    private const SYSTEM_PROMPT_TEXT = "Tu es Brillio, un conseiller en orientation professionnelle specialise pour les jeunes africains.\n\nREGLES IMPORTANTES DE COMMUNICATION:\n- Tu DOIS TOUJOURS tutoyer l'utilisateur (jamais de \"vous\", uniquement \"tu\")\n- Tu DOIS utiliser le prenom de l'utilisateur regulierement dans tes reponses pour personnaliser l'echange\n- Sois chaleureux, amical et proche comme un grand frere ou une grande soeur bienveillant(e)\n\nTon role est de :\n- Aider les jeunes a decouvrir leurs talents et interets\n- Donner des conseils d'orientation adaptes au contexte africain\n- Informer sur les metiers, les formations et les opportunites de carriere\n- Encourager et motiver les jeunes dans leurs parcours\n- Repondre aux questions sur les etudes et le monde professionnel\n\nTes reponses doivent etre :\n- Personnalisees (utilise le prenom!)\n- Bienveillantes et encourageantes\n- Pratiques et concretes\n- Adaptees au contexte africain (pays, economie, opportunites locales)\n- Claires et accessibles\n\nTu peux poser des questions pour mieux comprendre le profil de l'utilisateur :\n- Son pays et sa ville\n- Son niveau d'etudes actuel\n- Ses matieres preferees\n- Ses passions et loisirs\n- Ses aspirations professionnelles\n\nN'hesite pas a :\n- Suggerer des metiers adaptes a son profil\n- Recommander des formations disponibles en Afrique\n- Partager des temoignages inspirants\n- Donner des conseils pratiques pour reussir\n\nReponds toujours en francais sauf si l'utilisateur s'adresse a toi dans une autre langue.";

    public function __construct()
    {
        $this->apiKey = config('services.openrouter.api_key', env('OPENROUTER_API_KEY'));
        $this->apiUrl = config('services.openrouter.api_url', 'https://openrouter.ai/api/v1/chat/completions');
        $this->model = config('services.openrouter.model', 'deepseek/deepseek-r1:free');
        $this->maxTokens = (int)config('services.openrouter.max_tokens', 2000);
        $this->temperature = (float)config('services.openrouter.temperature', 0.7);
        $this->siteUrl = config('services.openrouter.site_url', 'https://www.brillio.africa');
        $this->siteName = config('services.openrouter.site_name', 'Brillio');

        // Initialisation du prompt systeme avec l'instruction de formatage pour separer pensee et reponse
        $this->systemPrompt = self::SYSTEM_PROMPT_TEXT . "\n\nIMPORTANT: Pour chaque reponse, tu dois D'ABORD reflechir (tu peux afficher ta reflexion), puis IMPERATIVEMENT ecrire ta reponse finale au destinataire entre les balises <answer> et </answer>. Exemple: <answer>Bonjour Tidjani...</answer>";
    }

    /**
     * Nettoie la reponse de l'IA (supprime les balises <think> de DeepSeek R1 et extrait <answer>)
     */
    private function cleanResponse($content, $formatting = true)
    {
        // 1. Essayer d'extraire le contenu entre <answer>...</answer>
        $matches = array();
        if (preg_match('/<answer>(.*?)<\/answer>/s', $content, $matches)) {
            $content = $matches[1];
        }
        // 2. Si pas de balises <answer>, essayer de nettoyer les balises <think> (comportement standard R1)
        else {
            $content = preg_replace('/<think>.*?<\/think>/s', '', $content);
        }

        if ($formatting) {
            // Remplacer les ### par des doubles retours a la ligne pour separer les sections
            $content = preg_replace('/###\s*/', "\n\n", $content);

            // Remplacer les ## par des doubles retours a la ligne
            $content = preg_replace('/##\s*/', "\n\n", $content);

            // Remplacer les listes numerotees pour ajouter un retour a la ligne avant
            $content = preg_replace('/(\d+\.\s)/u', "\n$1", $content);

            // Remplacer les listes a puces pour ajouter un retour a la ligne avant
            $content = preg_replace('/([•\-✔️👉])\s/u', "\n$1 ", $content);

            // Nettoyer les retours a la ligne multiples (max 2)
            $content = preg_replace('/\n{3,}/', "\n\n", $content);
        }

        // Trim final
        $content = trim($content);

        return $content;
    }

    public function createConversation(User $user, $title = null)
    {
        return ChatConversation::create(array(
            'user_id' => $user->id,
            'title' => ($title !== null) ? $title : 'Nouvelle conversation',
        ));
    }

    /**
     * Envoie un message et recupere la reponse de l'IA
     */
    public function sendMessage(ChatConversation $conversation, $userMessage)
    {
        // 1. Enregistrer le message utilisateur
        $userChatMessage = ChatMessage::create(array(
            'conversation_id' => $conversation->id,
            'role' => ChatMessage::ROLE_USER,
            'content' => $userMessage,
        ));

        // 2. Construire le contexte avec l'historique (max 10 derniers messages)
        $messages = $this->buildMessagesContext($conversation);

        // 3. Appeler l'API OpenRouter
        $aiResponse = $this->callOpenRouterApi($messages);

        // 4. Enregistrer la reponse de l'IA
        $assistantMessage = ChatMessage::create(array(
            'conversation_id' => $conversation->id,
            'role' => ChatMessage::ROLE_ASSISTANT,
            'content' => $aiResponse,
        ));

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
        $messages = array(
                array(
                'role' => 'system',
                'content' => $this->systemPrompt,
            ),
        );

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

                array_push($messages, array(
                    'role' => 'system',
                    'content' => "CONTEXTE UTILISATEUR - UTILISE CES INFORMATIONS POUR PERSONNALISER TES REPONSES:\n" . $userContext . "\n\nIMPORTANT: Tu parles a " . $firstName . ". Utilise son prenom dans tes reponses et tutoie-le/la toujours!",
                ));
            }
        }

        // Ajouter les 10 derniers messages de la conversation
        $historyMessages = $conversation->getLastMessages(10);
        foreach ($historyMessages as $message) {
            array_push($messages, array(
                'role' => $message->role,
                'content' => $message->content,
            ));
        }

        return $messages;
    }

    /**
     * Construit le contexte utilisateur pour personnaliser les reponses
     */
    private function buildUserContext(User $user)
    {
        $context = array();

        if (isset($user->name) && $user->name) {
            array_push($context, "Nom : " . $user->name);
        }

        if (isset($user->country) && $user->country) {
            array_push($context, "Pays : " . $user->country);
        }

        if (isset($user->city) && $user->city) {
            array_push($context, "Ville : " . $user->city);
        }

        if (isset($user->date_of_birth) && $user->date_of_birth) {
            $age = $user->date_of_birth->age;
            array_push($context, "Age : " . $age . " ans");
        }

        // Ajouter le type de personnalite si disponible
        $personalityTest = $user->personalityTest;
        if ($personalityTest && $personalityTest->isCompleted()) {
            array_push($context, "Type de personnalite : " . $personalityTest->personality_type . " (" . $personalityTest->personality_label . ")");
        }

        return implode(', ', $context);
    }

    /**
     * Verifie si la cle API est configuree
     */
    public function isApiKeyConfigured()
    {
        return !empty($this->apiKey) && $this->apiKey !== 'your_openrouter_api_key_here';
    }

    /**
     * Analyse un texte brut avec un prompt systeme specifique (sans historique de conversation)
     * Utile pour des taches uniques comme le parsing de documents
     */
    public function analyzeText($prompt, $systemPrompt = null)
    {
        $messages = array(
                array(
                'role' => 'system',
                'content' => ($systemPrompt !== null) ? $systemPrompt : $this->systemPrompt,
            ),
                array(
                'role' => 'user',
                'content' => $prompt,
            )
        );

        return $this->callOpenRouterApi($messages, false);
    }

    /**
     * Nettoie une reponse JSON (supprime les balises markdown ```json ... ```)
     */
    public function cleanJson($content)
    {
        // 1. Supprimer les balises markdown de code (```json ... ```)
        $content = preg_replace('/```(?:json)?\s*(.*?)\s*```/s', '$1', $content);

        // 2. Extraire la premiere structure JSON complete (entre { et } ou [ et ])
        $matches = array();
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
     * Appelle l'API OpenRouter (DeepSeek)
     */
    private function callOpenRouterApi($messages, $formatting = true)
    {
        $apiKeyVal = isset($this->apiKey) ? $this->apiKey : '';
        Log::info('=== APPEL API OPENROUTER ===', array(
            'api_url' => $this->apiUrl,
            'model' => $this->model,
            'messages_count' => count($messages),
            'api_key_configured' => $this->isApiKeyConfigured(),
            'api_key_preview' => substr($apiKeyVal, 0, 10) . '...',
        ));

        try {
            if (!$this->isApiKeyConfigured()) {
                Log::warning('OpenRouter API key not configured', array(
                    'api_key_value' => $this->apiKey,
                ));
                return "";
            }

            Log::info('Envoi requete OpenRouter', array(
                'url' => $this->apiUrl,
                'model' => $this->model,
            ));

            $response = Http::withHeaders(array(
                'Authorization' => 'Bearer ' . $this->apiKey,
                'HTTP-Referer' => $this->siteUrl,
                'X-Title' => $this->siteName,
                'Content-Type' => 'application/json',
            ))->timeout(300)->post($this->apiUrl, array(
                'model' => $this->model,
                'messages' => $messages,
                'max_tokens' => $this->maxTokens,
                'temperature' => $this->temperature,
            ));

            Log::info('Reponse OpenRouter recue', array(
                'status' => $response->status(),
                'successful' => $response->successful(),
                'body_length' => strlen($response->body()),
            ));

            if ($response->successful()) {
                $data = $response->json();

                // DEBUG: Loguer la reponse brute pour comprendre la structure
                Log::info('DEBUG OPENROUTER RESPONSE', array('data' => $data));

                $choices = array();
                if (isset($data['choices'])) {
                    $choices = $data['choices'];
                }

                $firstChoice = null;
                if (isset($choices[0])) {
                    $firstChoice = $choices[0];
                }

                $messageData = null;
                if (isset($firstChoice['message'])) {
                    $messageData = $firstChoice['message'];
                }

                $content = null;
                if (isset($messageData['content'])) {
                    $content = $messageData['content'];
                }

                $hasContent = !empty($content);
                $contentLength = 0;
                if ($content) {
                    $contentLength = strlen($content);
                }

                Log::info('Contenu extrait de la reponse', array(
                    'has_content' => $hasContent,
                    'content_length' => $contentLength,
                ));

                if ($content) {
                    $content = $this->cleanResponse($content, $formatting);
                    Log::info('=== REPONSE OPENROUTER OK ===');
                    return $content;
                }

                $errorMsg = 'Pas de contenu dans la reponse OpenRouter';
                if (isset($data['error']['message'])) {
                    $errorMsg .= ': ' . $data['error']['message'];
                }
                throw new \Exception($errorMsg);
            }

            $errorBody = $response->body();
            $decodedError = json_decode($errorBody, true);
            $errorMessage = 'API Error';
            if (isset($decodedError['error']['message'])) {
                $errorMessage = $decodedError['error']['message'];
            }

            Log::error('OpenRouter API error', array(
                'status' => $response->status(),
                'message' => $errorMessage,
                'body' => $errorBody,
            ));

            throw new \Exception("OpenRouter Error (" . $response->status() . "): " . $errorMessage);
        }
        catch (\Exception $e) {
            Log::error('OpenRouter API exception', array(
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ));

            throw $e;
        }
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
        $messages = array(
                array(
                'role' => 'user',
                'content' => $prompt,
            ),
        );

        return $this->callOpenRouterApi($messages);
    }

    /**
     * Supprime une conversation
     */
    public function deleteConversation(ChatConversation $conversation)
    {
        return $conversation->delete();
    }
}