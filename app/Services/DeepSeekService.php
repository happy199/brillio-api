<?php

namespace App\Services;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service pour l'intégration du chatbot DeepSeek via OpenRouter
 *
 * Ce service gère :
 * - L'envoi de messages à l'API OpenRouter (DeepSeek R1)
 * - La construction du contexte avec l'historique des messages
 * - Le stockage des messages utilisateur et assistant
 *
 * @see https://openrouter.ai/docs
 */
class DeepSeekService
{
    private string $apiKey;
    private string $apiUrl;
    private string $model;
    private int $maxTokens;
    private float $temperature;
    private string $siteUrl;
    private string $siteName;
    private string $systemPrompt;

    /**
     * Prompt système pour orienter l'IA vers les conseils d'orientation
     */
    private const SYSTEM_PROMPT = <<<EOT
Tu es Brillio, un conseiller en orientation professionnelle specialise pour les jeunes africains.

REGLES IMPORTANTES DE COMMUNICATION:
- Tu DOIS TOUJOURS tutoyer l'utilisateur (jamais de "vous", uniquement "tu")
- Tu DOIS utiliser le prenom de l'utilisateur regulierement dans tes reponses pour personnaliser l'echange
- Sois chaleureux, amical et proche comme un grand frere ou une grande soeur bienveillant(e)

Ton role est de :
- Aider les jeunes a decouvrir leurs talents et interets
- Donner des conseils d'orientation adaptes au contexte africain
- Informer sur les metiers, les formations et les opportunites de carriere
- Encourager et motiver les jeunes dans leurs parcours
- Repondre aux questions sur les etudes et le monde professionnel

Tes reponses doivent etre :
- Personnalisees (utilise le prenom!)
- Bienveillantes et encourageantes
- Pratiques et concretes
- Adaptees au contexte africain (pays, economie, opportunites locales)
- Claires et accessibles

Tu peux poser des questions pour mieux comprendre le profil de l'utilisateur :
- Son pays et sa ville
- Son niveau d'etudes actuel
- Ses matieres preferees
- Ses passions et loisirs
- Ses aspirations professionnelles

N'hesite pas a :
- Suggerer des metiers adaptes a son profil
- Recommander des formations disponibles en Afrique
- Partager des temoignages inspirants
- Donner des conseils pratiques pour reussir

Reponds toujours en francais sauf si l'utilisateur s'adresse a toi dans une autre langue.
EOT;

    public function __construct()
    {
        $this->apiKey = config('services.openrouter.api_key', env('OPENROUTER_API_KEY'));
        $this->apiUrl = config('services.openrouter.api_url', 'https://openrouter.ai/api/v1/chat/completions');
        $this->model = config('services.openrouter.model', 'deepseek/deepseek-r1:free');
        $this->maxTokens = (int) config('services.openrouter.max_tokens', 2000);
        $this->temperature = (float) config('services.openrouter.temperature', 0.7);
        $this->siteUrl = config('services.openrouter.site_url', 'https://www.brillio.africa');
        $this->siteName = config('services.openrouter.site_name', 'Brillio');

        // Initialisation du prompt système avec l'instruction de formatage pour séparer pensée et réponse
        $this->systemPrompt = self::SYSTEM_PROMPT . "\n\nIMPORTANT: Pour chaque reponse, tu dois D'ABORD reflechir (tu peux afficher ta reflexion), puis IMPERATIVEMENT ecrire ta reponse finale au destinataire entre les balises <answer> et </answer>. Exemple: <answer>Bonjour Tidjani...</answer>";
    }

    // ... (createConversation unchange)

    /**
     * Nettoie la réponse de l'IA (supprime les balises <think> de DeepSeek R1 et extrait <answer>)
     */
    private function cleanResponse(string $content): string
    {
        // 1. Essayer d'extraire le contenu entre <answer>...</answer>
        if (preg_match('/<answer>(.*?)<\/answer>/s', $content, $matches)) {
            $content = $matches[1];
        }
        // 2. Si pas de balises <answer>, essayer de nettoyer les balises <think> (comportement standard R1)
        else {
            $content = preg_replace('/<think>.*?<\/think>/s', '', $content);
        }

        // Remplacer les ### par des doubles retours à la ligne pour séparer les sections
        $content = preg_replace('/###\s*/', "\n\n", $content);

        // Remplacer les ## par des doubles retours à la ligne
        $content = preg_replace('/##\s*/', "\n\n", $content);

        // Remplacer les listes numérotées pour ajouter un retour à la ligne avant
        $content = preg_replace('/(\d+\.\s)/u', "\n$1", $content);

        // Remplacer les listes à puces pour ajouter un retour à la ligne avant
        $content = preg_replace('/([•\-✔️👉])\s/u', "\n$1 ", $content);

        // Nettoyer les retours à la ligne multiples (max 2)
        $content = preg_replace('/\n{3,}/', "\n\n", $content);

        // Trim final
        $content = trim($content);

        return $content;
    }
    public function createConversation(User $user, ?string $title = null): ChatConversation
    {
        return ChatConversation::create([
            'user_id' => $user->id,
            'title' => $title ?? 'Nouvelle conversation',
        ]);
    }

    /**
     * Envoie un message et récupère la réponse de l'IA
     */
    public function sendMessage(ChatConversation $conversation, string $userMessage): ChatMessage
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

        // 4. Enregistrer la réponse de l'IA
        $assistantMessage = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'role' => ChatMessage::ROLE_ASSISTANT,
            'content' => $aiResponse,
        ]);

        // 5. Mettre à jour le titre si c'est le premier message
        if ($conversation->messages()->count() <= 2) {
            $conversation->generateTitle();
        }

        return $assistantMessage;
    }

    /**
     * Construit le tableau de messages pour l'API avec contexte
     */
    private function buildMessagesContext(ChatConversation $conversation): array
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
                $firstName = explode(' ', $user->name ?? '')[0] ?? 'ami(e)';
                $messages[] = [
                    'role' => 'system',
                    'content' => "CONTEXTE UTILISATEUR - UTILISE CES INFORMATIONS POUR PERSONNALISER TES REPONSES:\n{$userContext}\n\nIMPORTANT: Tu parles a {$firstName}. Utilise son prenom dans tes reponses et tutoie-le/la toujours!",
                ];
            }
        }

        // Ajouter les 10 derniers messages de la conversation
        $historyMessages = $conversation->getLastMessages(10);
        foreach ($historyMessages as $message) {
            $messages[] = [
                'role' => $message->role,
                'content' => $message->content,
            ];
        }

        return $messages;
    }

    /**
     * Construit le contexte utilisateur pour personnaliser les réponses
     */
    private function buildUserContext(User $user): string
    {
        $context = [];

        if ($user->name) {
            $context[] = "Nom : {$user->name}";
        }

        if ($user->country) {
            $context[] = "Pays : {$user->country}";
        }

        if ($user->city) {
            $context[] = "Ville : {$user->city}";
        }

        if ($user->date_of_birth) {
            $age = $user->date_of_birth->age;
            $context[] = "Âge : {$age} ans";
        }

        // Ajouter le type de personnalité si disponible
        $personalityTest = $user->personalityTest;
        if ($personalityTest && $personalityTest->isCompleted()) {
            $context[] = "Type de personnalité : {$personalityTest->personality_type} ({$personalityTest->personality_label})";
        }

        return implode(', ', $context);
    }

    /**
     * Vérifie si la clé API est configurée
     */
    public function isApiKeyConfigured(): bool
    {
        return !empty($this->apiKey) && $this->apiKey !== 'your_openrouter_api_key_here';
    }

    /**
     * Appelle l'API OpenRouter (DeepSeek)
     */
    private function callOpenRouterApi(array $messages): string
    {
        Log::info('=== APPEL API OPENROUTER ===', [
            'api_url' => $this->apiUrl,
            'model' => $this->model,
            'messages_count' => count($messages),
            'api_key_configured' => $this->isApiKeyConfigured(),
            'api_key_preview' => substr($this->apiKey ?? '', 0, 10) . '...',
        ]);

        try {
            if (!$this->isApiKeyConfigured()) {
                Log::warning('OpenRouter API key not configured', [
                    'api_key_value' => $this->apiKey,
                    'env_key' => env('OPENROUTER_API_KEY'),
                ]);
                return $this->getFallbackResponse($messages);
            }

            Log::info('Envoi requete OpenRouter', [
                'url' => $this->apiUrl,
                'model' => $this->model,
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'HTTP-Referer' => $this->siteUrl,
                'X-Title' => $this->siteName,
                'Content-Type' => 'application/json',
            ])->timeout(300)->post($this->apiUrl, [
                        'model' => $this->model,
                        'messages' => $messages,
                        'max_tokens' => $this->maxTokens,
                        'temperature' => $this->temperature,
                    ]);

            Log::info('Reponse OpenRouter recue', [
                'status' => $response->status(),
                'successful' => $response->successful(),
                'body_length' => strlen($response->body()),
            ]);

            if ($response->successful()) {
                $data = $response->json();

                // DEBUG: Loguer la réponse brute pour comprendre la structure
                Log::info('DEBUG OPENROUTER RESPONSE', ['data' => $data]);

                $content = $data['choices'][0]['message']['content'] ?? null;

                Log::info('Contenu extrait de la reponse', [
                    'has_content' => !empty($content),
                    'content_length' => strlen($content ?? ''),
                ]);

                if ($content) {
                    $content = $this->cleanResponse($content);
                    Log::info('=== REPONSE OPENROUTER OK ===');
                    return $content;
                }

                Log::warning('Pas de contenu dans la reponse OpenRouter');
                return $this->getFallbackResponse($messages);
            }

            Log::error('OpenRouter API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return $this->getFallbackResponse($messages);
        } catch (\Exception $e) {
            Log::error('OpenRouter API exception', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return $this->getFallbackResponse($messages);
        }
    }

    /**
     * Nettoie la réponse de l'IA (supprime les balises <think> de DeepSeek R1)
     */

    /**
     * Retourne une réponse de secours si l'API est indisponible
     */
    private function getFallbackResponse(array $messages): string
    {
        $lastUserMessage = '';
        foreach (array_reverse($messages) as $message) {
            if ($message['role'] === 'user') {
                $lastUserMessage = strtolower($message['content']);
                break;
            }
        }

        // Réponses de base selon les mots-clés
        if (str_contains($lastUserMessage, 'bonjour') || str_contains($lastUserMessage, 'salut')) {
            return "Bonjour ! Je suis Brillio, ton conseiller en orientation. Je suis là pour t'aider à découvrir les métiers et formations qui correspondent à ton profil. Comment puis-je t'aider aujourd'hui ?";
        }

        if (str_contains($lastUserMessage, 'métier') || str_contains($lastUserMessage, 'travail')) {
            return "C'est une excellente question ! Pour te conseiller au mieux sur les métiers, j'aurais besoin de mieux te connaître. Quelles sont tes matières préférées à l'école ? Et qu'est-ce qui te passionne en dehors des études ?";
        }

        if (str_contains($lastUserMessage, 'formation') || str_contains($lastUserMessage, 'étude')) {
            return "Les formations sont nombreuses en Afrique ! Pour t'orienter, dis-moi : quel est ton niveau d'études actuel ? Et dans quel pays te trouves-tu ?";
        }

        if (str_contains($lastUserMessage, 'informatique') || str_contains($lastUserMessage, 'tech')) {
            return "L'informatique est un domaine passionnant avec beaucoup d'opportunités en Afrique ! Tu peux te former en développement web, mobile, cybersécurité, data science ou intelligence artificielle. Des universités comme l'ESP (Sénégal), AIMS (plusieurs pays), ou des bootcamps comme Orange Digital Center proposent d'excellentes formations. Qu'est-ce qui t'attire le plus dans ce domaine ?";
        }

        if (str_contains($lastUserMessage, 'merci')) {
            return "Je t'en prie ! N'hésite pas à revenir vers moi si tu as d'autres questions sur ton orientation. Je suis là pour t'accompagner dans ton parcours. Bonne continuation !";
        }

        return "Merci pour ta question ! Je suis là pour t'aider dans ton orientation professionnelle. Pour te donner les meilleurs conseils, peux-tu me parler un peu de toi ? Par exemple, quelles matières aimes-tu à l'école, ou quels sont tes centres d'intérêt ?";
    }

    /**
     * Récupère l'historique des conversations d'un utilisateur
     */
    public function getUserConversations(User $user, int $limit = 20): \Illuminate\Database\Eloquent\Collection
    {
        return $user->chatConversations()
            ->orderBy('updated_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Récupère les messages d'une conversation
     */
    public function getConversationMessages(ChatConversation $conversation, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return $conversation->messages()
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get();
    }

    /**
     * Traduit un texte en utilisant DeepSeek
     */
    public function translate(string $prompt): string
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
    public function deleteConversation(ChatConversation $conversation): bool
    {
        return $conversation->delete();
    }
}
