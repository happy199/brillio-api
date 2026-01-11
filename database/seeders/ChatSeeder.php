<?php

namespace Database\Seeders;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Database\Seeder;

class ChatSeeder extends Seeder
{
    /**
     * Crée des conversations de chat de démonstration
     */
    public function run(): void
    {
        $conversations = [
            [
                'title' => 'Conseils sur les études en informatique',
                'messages' => [
                    ['role' => 'user', 'content' => 'Bonjour ! Je suis en Terminale S et j\'aimerais faire de l\'informatique. Par où commencer ?'],
                    ['role' => 'assistant', 'content' => 'Bonjour ! C\'est super que tu t\'intéresses à l\'informatique ! C\'est un domaine passionnant avec beaucoup d\'opportunités. Pour bien commencer, voici quelques conseils :

1. **Formations disponibles** : Tu peux opter pour une licence en informatique à l\'université ou une école d\'ingénieurs. Au Sénégal, l\'ESP et l\'UCAD proposent d\'excellents programmes.

2. **Auto-apprentissage** : Commence dès maintenant à coder ! Des plateformes comme FreeCodeCamp ou OpenClassrooms offrent des cours gratuits en français.

3. **Spécialisations** : L\'informatique est vaste - développement web, mobile, data science, cybersécurité... Explore pour trouver ce qui te passionne.

Tu as une idée de ce qui t\'attire le plus dans l\'informatique ?'],
                    ['role' => 'user', 'content' => 'Je pense que le développement d\'applications mobiles m\'intéresse beaucoup !'],
                    ['role' => 'assistant', 'content' => 'Excellent choix ! Le développement mobile est très demandé, surtout en Afrique où l\'usage du smartphone explose.

**Pour te lancer :**

📱 **Technologies à apprendre :**
- Flutter (recommandé pour débuter, une seule base de code pour iOS et Android)
- React Native (si tu connais déjà JavaScript)
- Kotlin pour Android natif
- Swift pour iOS natif

📚 **Ressources gratuites :**
- Codelabs Flutter (officiel Google)
- Cours Udemy en promotion régulièrement
- YouTube : The Net Ninja, Academind

💡 **Conseils pratiques :**
- Crée ton premier projet simple (une app de liste de tâches par exemple)
- Rejoins des communautés : GDG (Google Developer Groups) en Afrique
- Participe à des hackathons locaux

Tu peux commencer dès maintenant, même avant d\'entrer à l\'université. As-tu déjà un ordinateur pour coder ?'],
                ],
            ],
            [
                'title' => 'Orientation après le Bac',
                'messages' => [
                    ['role' => 'user', 'content' => 'Je ne sais pas quoi faire après mon bac, j\'aime beaucoup les langues et voyager'],
                    ['role' => 'assistant', 'content' => 'C\'est tout à fait normal de se poser cette question ! Aimer les langues et les voyages ouvre de nombreuses portes professionnelles. Laisse-moi t\'en présenter quelques-unes :

🌍 **Métiers combinant langues et voyages :**

1. **Tourisme & Hôtellerie**
   - Guide touristique
   - Réceptionniste international
   - Event planner

2. **Commerce International**
   - Import-export
   - Négociant international
   - Responsable achats

3. **Diplomatie & Organisations internationales**
   - Carrière diplomatique
   - Travail dans les ONG (ONU, OMS, UNESCO...)

4. **Communication & Médias**
   - Journaliste international
   - Community manager multilingue

5. **Traduction & Interprétariat**
   - Traducteur freelance
   - Interprète de conférence

📚 **Formations possibles :**
- BTS Tourisme
- Licence LEA (Langues Étrangères Appliquées)
- École de commerce avec spécialisation internationale
- Sciences Po

Quelles langues parles-tu actuellement ? Et dans quel pays te trouves-tu ?'],
                    ['role' => 'user', 'content' => 'Je parle français, wolof et j\'apprends l\'anglais. Je suis au Sénégal.'],
                    ['role' => 'assistant', 'content' => 'Parfait ! Tu as déjà un bon bagage linguistique. Le wolof est un atout précieux pour travailler dans le tourisme ou les ONG au Sénégal et en Afrique de l\'Ouest.

🇸🇳 **Formations au Sénégal :**

- **ISM (Institut Supérieur de Management)** : BTS Tourisme, Bachelor Commerce International
- **UCAD** : Licence LEA (anglais + autre langue)
- **CESAG** : MBA Commerce International
- **École Supérieure de Tourisme de Dakar**

💡 **Conseils pour toi :**

1. **Renforce ton anglais** : C\'est indispensable. Vise le niveau B2/C1. Utilise des apps comme Duolingo, BBC Learning English.

2. **Apprends une 3ème langue** : L\'espagnol ou le portugais (beaucoup de touristes lusophones et hispanophones en Afrique).

3. **Gagne de l\'expérience** : Stages dans les hôtels (Radisson Blu, Terrou-Bi), agences de voyage, ou volontariat dans des ONG.

4. **Développe ton réseau** : Rejoins des associations comme l\'ATPS (Association des Professionnels du Tourisme du Sénégal).

Le secteur du tourisme au Sénégal est en pleine croissance, c\'est le moment idéal pour s\'y former ! Tu veux plus d\'infos sur une formation en particulier ?'],
                ],
            ],
            [
                'title' => 'Question sur la médecine',
                'messages' => [
                    ['role' => 'user', 'content' => 'Est-ce que les études de médecine sont vraiment difficiles ?'],
                    ['role' => 'assistant', 'content' => 'C\'est une question importante à se poser avant de s\'engager dans cette voie ! Je vais te répondre honnêtement :

**Oui, les études de médecine sont exigeantes**, mais elles sont tout à fait réalisables avec de la motivation et une bonne organisation.

📊 **Ce qui est difficile :**

1. **La durée** : 7 à 11 ans d\'études selon la spécialisation
2. **La charge de travail** : Beaucoup à mémoriser, surtout les premières années
3. **Le concours** : En France et dans certains pays, la première année est très sélective
4. **Les gardes** : Pendant l\'internat, tu travailleras parfois la nuit et les week-ends

✅ **Ce qui aide à réussir :**

1. **La passion** : Si tu veux vraiment aider les gens et que le corps humain te fascine
2. **L\'organisation** : Travailler régulièrement plutôt que tout réviser à la dernière minute
3. **Le soutien** : Étudier en groupe, avoir des mentors
4. **La résilience** : Accepter que certaines périodes seront dures

🌍 **En Afrique :**

Les études de médecine sont structurées différemment selon les pays. Au Sénégal (UCAD, FMPO), au Maroc, en Tunisie, ou en Côte d\'Ivoire, les programmes sont reconnus et de qualité.

Tu es au lycée actuellement ? Quelles sont tes matières fortes ?'],
                ],
            ],
        ];

        // Récupérer quelques jeunes pour leur attribuer des conversations
        $jeunes = User::where('user_type', 'jeune')->take(3)->get();

        foreach ($conversations as $index => $convData) {
            if (!isset($jeunes[$index])) {
                break;
            }

            $conversation = ChatConversation::create([
                'user_id' => $jeunes[$index]->id,
                'title' => $convData['title'],
            ]);

            foreach ($convData['messages'] as $msgData) {
                ChatMessage::create([
                    'conversation_id' => $conversation->id,
                    'role' => $msgData['role'],
                    'content' => $msgData['content'],
                ]);
            }
        }

        $this->command->info('Conversations de chat de démonstration créées');
    }
}
