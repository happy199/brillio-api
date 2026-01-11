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

    /**
     * Prompt système pour orienter l'IA vers les conseils d'orientation
     */
    private const SYSTEM_PROMPT = <<<EOT
Tu es Brillio, un conseiller en orientation professionnelle spécialisé pour les jeunes africains.

Ton rôle est de :
- Aider les jeunes à découvrir leurs talents et intérêts
- Donner des conseils d'orientation adaptés au contexte africain
- Informer sur les métiers, les formations et les opportunités de carrière
- Encourager et motiver les jeunes dans leurs parcours
- Répondre aux questions sur les études et le monde professionnel

Tes réponses doivent être :
- Bienveillantes et encourageantes
- Pratiques et concrètes
- Adaptées au contexte africain (pays, économie, opportunités locales)
- Claires et accessibles

Tu peux poser des questions pour mieux comprendre le profil de l'utilisateur :
- Son pays et sa ville
- Son niveau d'études actuel
- Ses matières préférées
- Ses passions et loisirs
- Ses aspirations professionnelles

N'hésite pas à :
- Suggérer des métiers adaptés à son profil
- Recommander des formations disponibles en Afrique
- Partager des témoignages inspirants
- Donner des conseils pratiques pour réussir

Réponds toujours en français sauf si l'utilisateur s'adresse à toi dans une autre langue.
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
    }

    /**
     * Crée une nouvelle conversation
     */
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
                'content' => self::SYSTEM_PROMPT,
            ],
        ];

        // Ajouter le contexte utilisateur si disponible
        $user = $conversation->user;
        if ($user) {
            $userContext = $this->buildUserContext($user);
            if ($userContext) {
                $messages[] = [
                    'role' => 'system',
                    'content' => "Contexte de l'utilisateur : {$userContext}",
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
     * Appelle l'API OpenRouter (DeepSeek)
     */
    private function callOpenRouterApi(array $messages): string
    {
        try {
            if (empty($this->apiKey) || $this->apiKey === 'your_openrouter_api_key_here') {
                Log::warning('OpenRouter API key not configured, using fallback response');
                return $this->getFallbackResponse($messages);
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'HTTP-Referer' => $this->siteUrl,
                'X-Title' => $this->siteName,
                'Content-Type' => 'application/json',
            ])->timeout(60)->post($this->apiUrl, [
                'model' => $this->model,
                'messages' => $messages,
                'max_tokens' => $this->maxTokens,
                'temperature' => $this->temperature,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $content = $data['choices'][0]['message']['content'] ?? null;

                if ($content) {
                    // Nettoyer le contenu (DeepSeek R1 peut inclure des balises <think>)
                    $content = $this->cleanResponse($content);
                    return $content;
                }

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
            ]);

            return $this->getFallbackResponse($messages);
        }
    }

    /**
     * Nettoie la réponse de l'IA (supprime les balises <think> de DeepSeek R1)
     */
    private function cleanResponse(string $content): string
    {
        // Supprimer les balises <think>...</think> qui contiennent le raisonnement interne
        $content = preg_replace('/<think>.*?<\/think>/s', '', $content);

        // Supprimer les espaces multiples et les retours à la ligne en début/fin
        $content = trim(preg_replace('/\s+/', ' ', $content));

        // Rétablir les retours à la ligne pour la lisibilité
        $content = preg_replace('/\s*\n\s*/', "\n", $content);

        return trim($content);
    }

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
     * Supprime une conversation
     */
    public function deleteConversation(ChatConversation $conversation): bool
    {
        return $conversation->delete();
    }
}
