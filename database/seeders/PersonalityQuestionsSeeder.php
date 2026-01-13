<?php

namespace Database\Seeders;

use App\Models\PersonalityQuestion;
use Illuminate\Database\Seeder;

class PersonalityQuestionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $questions = [
            ['id' => 1, 'dimension' => 'JP', 'left_en' => 'Makes lists', 'left_fr' => 'Fait des listes', 'right_en' => 'Relies on memory', 'right_fr' => 'Se fie à sa mémoire'],
            ['id' => 2, 'dimension' => 'TF', 'left_en' => 'Wants to believe', 'left_fr' => 'Veut croire', 'right_en' => 'Skeptical', 'right_fr' => 'Sceptique'],
            ['id' => 3, 'dimension' => 'EI', 'left_en' => 'Bored by time alone', 'left_fr' => 'S\'ennuie seul(e)', 'right_en' => 'Needs time alone', 'right_fr' => 'A besoin de temps seul(e)'],
            ['id' => 4, 'dimension' => 'SN', 'left_en' => 'Accepts things as they are', 'left_fr' => 'Accepte les choses telles qu\'elles sont', 'right_en' => 'Unsatisfied with the way things are', 'right_fr' => 'Insatisfait(e) de l\'état des choses'],
            ['id' => 5, 'dimension' => 'JP', 'left_en' => 'Keeps a clean room', 'left_fr' => 'Garde une chambre propre', 'right_en' => 'Just puts stuff wherever', 'right_fr' => 'Range les choses n\'importe où'],
            ['id' => 6, 'dimension' => 'TF', 'left_en' => 'Thinks "robotic" is an insult', 'left_fr' => 'Pense que "robotique" est une insulte', 'right_en' => 'Strives to have a mechanical mind', 'right_fr' => 'S\'efforce d\'avoir un esprit mécanique'],
            ['id' => 7, 'dimension' => 'EI', 'left_en' => 'Energetic', 'left_fr' => 'Énergique', 'right_en' => 'Mellow', 'right_fr' => 'Posé(e)'],
            ['id' => 8, 'dimension' => 'SN', 'left_en' => 'Prefers multiple choice test', 'left_fr' => 'Préfère les QCM', 'right_en' => 'Prefers essay answers', 'right_fr' => 'Préfère les réponses rédigées'],
            ['id' => 9, 'dimension' => 'JP', 'left_en' => 'Organized', 'left_fr' => 'Organisé(e)', 'right_en' => 'Chaotic', 'right_fr' => 'Chaotique'],
            ['id' => 10, 'dimension' => 'TF', 'left_en' => 'Easily hurt', 'left_fr' => 'Facilement blessé(e)', 'right_en' => 'Thick-skinned', 'right_fr' => 'Insensible aux critiques'],
            ['id' => 11, 'dimension' => 'EI', 'left_en' => 'Works best in groups', 'left_fr' => 'Travaille mieux en groupe', 'right_en' => 'Works best alone', 'right_fr' => 'Travaille mieux seul(e)'],
            ['id' => 12, 'dimension' => 'SN', 'left_en' => 'Focused on the past', 'left_fr' => 'Concentré(e) sur le passé', 'right_en' => 'Focused on the future', 'right_fr' => 'Concentré(e) sur l\'avenir'],
            ['id' => 13, 'dimension' => 'JP', 'left_en' => 'Plans far ahead', 'left_fr' => 'Planifie longtemps à l\'avance', 'right_en' => 'Plans at the last minute', 'right_fr' => 'Planifie à la dernière minute'],
            ['id' => 14, 'dimension' => 'TF', 'left_en' => 'Wants people\'s love', 'left_fr' => 'Veut l\'amour des gens', 'right_en' => 'Wants people\'s respect', 'right_fr' => 'Veut le respect des gens'],
            ['id' => 15, 'dimension' => 'EI', 'left_en' => 'Gets fired up by parties', 'left_fr' => 'S\'enthousiasme pour les fêtes', 'right_en' => 'Gets worn out by parties', 'right_fr' => 'Est épuisé(e) par les fêtes'],
            ['id' => 16, 'dimension' => 'SN', 'left_en' => 'Fits in', 'left_fr' => 'S\'intègre facilement', 'right_en' => 'Stands out', 'right_fr' => 'Se démarque'],
            ['id' => 17, 'dimension' => 'JP', 'left_en' => 'Commits', 'left_fr' => 'S\'engage', 'right_en' => 'Keeps options open', 'right_fr' => 'Garde ses options ouvertes'],
            ['id' => 18, 'dimension' => 'TF', 'left_en' => 'Wants to be good at fixing people', 'left_fr' => 'Veut aider les gens', 'right_en' => 'Wants to be good at fixing things', 'right_fr' => 'Veut réparer les choses'],
            ['id' => 19, 'dimension' => 'EI', 'left_en' => 'Talks more', 'left_fr' => 'Parle plus', 'right_en' => 'Listens more', 'right_fr' => 'Écoute plus'],
            ['id' => 20, 'dimension' => 'SN', 'left_en' => 'Describes what happened', 'left_fr' => 'Décrit ce qui s\'est passé', 'right_en' => 'Describes what it meant', 'right_fr' => 'Décrit ce que cela signifie'],
            ['id' => 21, 'dimension' => 'JP', 'left_en' => 'Gets work done right away', 'left_fr' => 'Fait le travail immédiatement', 'right_en' => 'Procrastinates', 'right_fr' => 'Procrastine'],
            ['id' => 22, 'dimension' => 'TF', 'left_en' => 'Follows the heart', 'left_fr' => 'Suit son cœur', 'right_en' => 'Follows the head', 'right_fr' => 'Suit sa raison'],
            ['id' => 23, 'dimension' => 'EI', 'left_en' => 'Goes out on the town', 'left_fr' => 'Sort en ville', 'right_en' => 'Stays at home', 'right_fr' => 'Reste à la maison'],
            ['id' => 24, 'dimension' => 'SN', 'left_en' => 'Wants the details', 'left_fr' => 'Veut les détails', 'right_en' => 'Wants the big picture', 'right_fr' => 'Veut la vue d\'ensemble'],
            ['id' => 25, 'dimension' => 'JP', 'left_en' => 'Prepares', 'left_fr' => 'Se prépare', 'right_en' => 'Improvises', 'right_fr' => 'Improvise'],
            ['id' => 26, 'dimension' => 'TF', 'left_en' => 'Bases morality on compassion', 'left_fr' => 'Base la moralité sur la compassion', 'right_en' => 'Bases morality on justice', 'right_fr' => 'Base la moralité sur la justice'],
            ['id' => 27, 'dimension' => 'EI', 'left_en' => 'Yelling comes naturally', 'left_fr' => 'Crier vient naturellement', 'right_en' => 'Finds it difficult to yell loudly', 'right_fr' => 'Trouve difficile de crier fort'],
            ['id' => 28, 'dimension' => 'SN', 'left_en' => 'Empirical', 'left_fr' => 'Empirique', 'right_en' => 'Theoretical', 'right_fr' => 'Théorique'],
            ['id' => 29, 'dimension' => 'JP', 'left_en' => 'Works hard', 'left_fr' => 'Travaille dur', 'right_en' => 'Plays hard', 'right_fr' => 'S\'amuse beaucoup'],
            ['id' => 30, 'dimension' => 'TF', 'left_en' => 'Values emotions', 'left_fr' => 'Valorise les émotions', 'right_en' => 'Uncomfortable with emotions', 'right_fr' => 'Mal à l\'aise avec les émotions'],
            ['id' => 31, 'dimension' => 'EI', 'left_en' => 'Likes to perform', 'left_fr' => 'Aime se produire en public', 'right_en' => 'Avoids public speaking', 'right_fr' => 'Évite de parler en public'],
            ['id' => 32, 'dimension' => 'SN', 'left_en' => 'Likes to know "who/what/when"', 'left_fr' => 'Aime savoir "qui/quoi/quand"', 'right_en' => 'Likes to know "why"', 'right_fr' => 'Aime savoir "pourquoi"'],
        ];

        foreach ($questions as $question) {
            PersonalityQuestion::updateOrCreate(
                ['openmbti_id' => $question['id']],
                [
                    'dimension' => $question['dimension'],
                    'left_trait_en' => $question['left_en'],
                    'left_trait_fr' => $question['left_fr'],
                    'right_trait_en' => $question['right_en'],
                    'right_trait_fr' => $question['right_fr'],
                ]
            );
        }

        $this->command->info('32 personality questions seeded successfully!');
    }
}
