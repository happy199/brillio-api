<?php

namespace App\Services;

/**
 * Service pour les metiers et secteurs lies aux types MBTI
 * Base de donnees statique de metiers modernes, durables et adaptes au contexte africain
 */
class MbtiCareersService
{
    /**
     * Secteurs d'activite avec leurs codes
     */
    public const SECTORS = [
        'tech' => [
            'name' => 'Technologie & IT',
            'icon' => 'computer',
            'color' => 'blue',
        ],
        'health' => [
            'name' => 'Sante & Bien-etre',
            'icon' => 'heart',
            'color' => 'red',
        ],
        'finance' => [
            'name' => 'Finance & Business',
            'icon' => 'chart',
            'color' => 'green',
        ],
        'creative' => [
            'name' => 'Arts & Creation',
            'icon' => 'palette',
            'color' => 'purple',
        ],
        'education' => [
            'name' => 'Education & Formation',
            'icon' => 'book',
            'color' => 'orange',
        ],
        'engineering' => [
            'name' => 'Ingenierie & Construction',
            'icon' => 'wrench',
            'color' => 'gray',
        ],
        'environment' => [
            'name' => 'Environnement & Energie',
            'icon' => 'leaf',
            'color' => 'emerald',
        ],
        'communication' => [
            'name' => 'Communication & Media',
            'icon' => 'megaphone',
            'color' => 'pink',
        ],
        'social' => [
            'name' => 'Social & Humanitaire',
            'icon' => 'users',
            'color' => 'cyan',
        ],
        'law' => [
            'name' => 'Droit & Juridique',
            'icon' => 'scale',
            'color' => 'indigo',
        ],
    ];

    /**
     * Metiers par type MBTI avec secteurs associes
     * 10 metiers par type, modernes et durables (pertinents dans 20 ans)
     */
    public const CAREERS_BY_TYPE = [
        'INTJ' => [
            [
                'title' => 'Architecte Solutions Cloud',
                'description' => 'Concoit et supervise les infrastructures cloud des entreprises. Metier strategique et bien remunere.',
                'match_reason' => 'Ideal pour ta pensee strategique et ton gout pour les systemes complexes.',
                'sectors' => ['tech'],
            ],
            [
                'title' => 'Data Scientist',
                'description' => 'Analyse les donnees massives pour aider les entreprises a prendre des decisions. Tres demande en Afrique.',
                'match_reason' => 'Parfait pour ton esprit analytique et ta capacite a voir les patterns.',
                'sectors' => ['tech', 'finance'],
            ],
            [
                'title' => 'Consultant en Strategie',
                'description' => 'Conseille les entreprises sur leur strategie de developpement. Impact important sur les decisions.',
                'match_reason' => 'Correspond a ta vision long terme et ton expertise en planification.',
                'sectors' => ['finance'],
            ],
            [
                'title' => 'Ingenieur en Cybersecurite',
                'description' => 'Protege les systemes informatiques contre les attaques. Domaine en forte croissance.',
                'match_reason' => 'Ideal pour ta rigueur et ta capacite a anticiper les problemes.',
                'sectors' => ['tech'],
            ],
            [
                'title' => 'Directeur de Recherche',
                'description' => 'Dirige des equipes de recherche dans differents domaines scientifiques.',
                'match_reason' => 'Parfait pour combiner leadership et expertise technique approfondie.',
                'sectors' => ['health', 'tech', 'environment'],
            ],
            [
                'title' => 'Urbaniste',
                'description' => 'Planifie le developpement durable des villes africaines en pleine croissance.',
                'match_reason' => 'Correspond a ta vision systemique et ta planification long terme.',
                'sectors' => ['engineering', 'environment'],
            ],
            [
                'title' => 'Economiste',
                'description' => 'Analyse les tendances economiques et conseille gouvernements et entreprises.',
                'match_reason' => 'Ideal pour ta capacite d\'analyse et ta pensee macro.',
                'sectors' => ['finance', 'education'],
            ],
            [
                'title' => 'Ingenieur en Intelligence Artificielle',
                'description' => 'Developpe des systemes d\'IA pour automatiser et optimiser les processus.',
                'match_reason' => 'Parfait pour ton interet pour l\'innovation et les systemes complexes.',
                'sectors' => ['tech'],
            ],
            [
                'title' => 'Architecte (Batiment)',
                'description' => 'Concoit des batiments durables et adaptes aux defis climatiques africains.',
                'match_reason' => 'Correspond a ta creativite structuree et ta vision globale.',
                'sectors' => ['engineering', 'creative'],
            ],
            [
                'title' => 'Analyste en Investissement',
                'description' => 'Evalue les opportunites d\'investissement pour les fonds et banques.',
                'match_reason' => 'Ideal pour ta rigueur analytique et ta vision strategique.',
                'sectors' => ['finance'],
            ],
        ],
        'INTP' => [
            [
                'title' => 'Developpeur Backend Senior',
                'description' => 'Cree la logique et les algorithmes qui font fonctionner les applications.',
                'match_reason' => 'Parfait pour ta logique et ton gout pour resoudre des problemes complexes.',
                'sectors' => ['tech'],
            ],
            [
                'title' => 'Chercheur en IA',
                'description' => 'Fait avancer les frontieres de l\'intelligence artificielle par la recherche.',
                'match_reason' => 'Ideal pour ta curiosite intellectuelle et ta pensee abstraite.',
                'sectors' => ['tech', 'education'],
            ],
            [
                'title' => 'Mathematicien Applique',
                'description' => 'Utilise les mathematiques pour resoudre des problemes concrets en finance, tech, etc.',
                'match_reason' => 'Correspond parfaitement a ton esprit logique et analytique.',
                'sectors' => ['finance', 'tech', 'education'],
            ],
            [
                'title' => 'Ingenieur Blockchain',
                'description' => 'Developpe des solutions decentralisees pour la finance et autres secteurs.',
                'match_reason' => 'Parfait pour ton interet pour les systemes innovants et complexes.',
                'sectors' => ['tech', 'finance'],
            ],
            [
                'title' => 'Bioinformaticien',
                'description' => 'Combine informatique et biologie pour analyser les donnees genetiques.',
                'match_reason' => 'Ideal pour ta capacite a connecter differents domaines de connaissance.',
                'sectors' => ['tech', 'health'],
            ],
            [
                'title' => 'Philosophe / Ethicien Tech',
                'description' => 'Reflechit aux implications ethiques des nouvelles technologies.',
                'match_reason' => 'Correspond a ta reflexion profonde et ton questionnement constant.',
                'sectors' => ['education', 'tech'],
            ],
            [
                'title' => 'Analyste Quantitatif',
                'description' => 'Cree des modeles mathematiques pour les marches financiers.',
                'match_reason' => 'Parfait pour ta capacite a modeliser des systemes abstraits.',
                'sectors' => ['finance'],
            ],
            [
                'title' => 'Ingenieur Logiciel Systemes',
                'description' => 'Developpe les logiciels bas niveau qui font fonctionner les machines.',
                'match_reason' => 'Ideal pour ta comprehension profonde des systemes.',
                'sectors' => ['tech'],
            ],
            [
                'title' => 'Physicien',
                'description' => 'Etudie les lois fondamentales de l\'univers et leurs applications.',
                'match_reason' => 'Correspond a ta curiosite pour comprendre le fonctionnement des choses.',
                'sectors' => ['education', 'engineering'],
            ],
            [
                'title' => 'Architecte Logiciel',
                'description' => 'Concoit la structure globale des systemes informatiques complexes.',
                'match_reason' => 'Parfait pour ta vision d\'ensemble et ta pensee systemique.',
                'sectors' => ['tech'],
            ],
        ],
        'ENTJ' => [
            [
                'title' => 'Directeur General (CEO)',
                'description' => 'Dirige une entreprise et definit sa strategie globale.',
                'match_reason' => 'Ideal pour ton leadership naturel et ta vision strategique.',
                'sectors' => ['finance'],
            ],
            [
                'title' => 'Entrepreneur Tech',
                'description' => 'Cree et developpe des startups technologiques innovantes.',
                'match_reason' => 'Parfait pour ton ambition et ta capacite a concretiser des visions.',
                'sectors' => ['tech', 'finance'],
            ],
            [
                'title' => 'Directeur des Operations',
                'description' => 'Supervise toutes les operations d\'une entreprise pour optimiser la performance.',
                'match_reason' => 'Correspond a ton efficacite et ta capacite de coordination.',
                'sectors' => ['finance'],
            ],
            [
                'title' => 'Avocat d\'Affaires',
                'description' => 'Conseille les entreprises sur les aspects juridiques de leurs activites.',
                'match_reason' => 'Ideal pour ton esprit de competition et ta maitrise des regles.',
                'sectors' => ['law', 'finance'],
            ],
            [
                'title' => 'Banquier d\'Investissement',
                'description' => 'Conseille les entreprises sur les fusions, acquisitions et levees de fonds.',
                'match_reason' => 'Parfait pour ton ambition et ta capacite a gerer des projets complexes.',
                'sectors' => ['finance'],
            ],
            [
                'title' => 'Directeur Commercial',
                'description' => 'Dirige la strategie commerciale et les equipes de vente.',
                'match_reason' => 'Correspond a ta capacite a motiver et atteindre des objectifs.',
                'sectors' => ['finance', 'communication'],
            ],
            [
                'title' => 'Consultant en Management',
                'description' => 'Aide les organisations a ameliorer leur performance globale.',
                'match_reason' => 'Ideal pour ta capacite d\'analyse et de proposition de solutions.',
                'sectors' => ['finance'],
            ],
            [
                'title' => 'Directeur de Programme',
                'description' => 'Coordonne plusieurs projets pour atteindre des objectifs strategiques.',
                'match_reason' => 'Parfait pour ta vision d\'ensemble et ta capacite de coordination.',
                'sectors' => ['tech', 'engineering'],
            ],
            [
                'title' => 'Venture Capitalist',
                'description' => 'Investit dans des startups prometteuses et les accompagne dans leur croissance.',
                'match_reason' => 'Correspond a ton flair pour identifier les opportunites.',
                'sectors' => ['finance', 'tech'],
            ],
            [
                'title' => 'Directeur de Transformation Digitale',
                'description' => 'Pilote la modernisation numerique des grandes organisations.',
                'match_reason' => 'Ideal pour conduire le changement et innover a grande echelle.',
                'sectors' => ['tech', 'finance'],
            ],
        ],
        'ENTP' => [
            [
                'title' => 'Product Manager',
                'description' => 'Definit la vision et la strategie des produits numeriques.',
                'match_reason' => 'Parfait pour ta creativite et ta capacite a voir le potentiel.',
                'sectors' => ['tech'],
            ],
            [
                'title' => 'Entrepreneur Social',
                'description' => 'Cree des entreprises qui resolvent des problemes sociaux tout en etant rentables.',
                'match_reason' => 'Ideal pour ton innovation et ton desir d\'impact positif.',
                'sectors' => ['social', 'finance'],
            ],
            [
                'title' => 'Growth Hacker',
                'description' => 'Trouve des moyens creatifs et rapides de faire croitre les entreprises.',
                'match_reason' => 'Correspond a ton esprit experimentateur et ta creativite.',
                'sectors' => ['tech', 'communication'],
            ],
            [
                'title' => 'Journaliste d\'Investigation',
                'description' => 'Enquete sur des sujets importants pour informer le public.',
                'match_reason' => 'Parfait pour ta curiosite et ton gout du challenge.',
                'sectors' => ['communication'],
            ],
            [
                'title' => 'Avocat en Propriete Intellectuelle',
                'description' => 'Protege les innovations et creations dans l\'economie du savoir.',
                'match_reason' => 'Ideal pour debattre et defendre des idees complexes.',
                'sectors' => ['law', 'tech'],
            ],
            [
                'title' => 'Consultant en Innovation',
                'description' => 'Aide les entreprises a innover et rester competitives.',
                'match_reason' => 'Correspond a ta capacite a generer des idees nouvelles.',
                'sectors' => ['finance', 'tech'],
            ],
            [
                'title' => 'Createur de Contenu / Influenceur',
                'description' => 'Produit du contenu engageant sur les reseaux sociaux.',
                'match_reason' => 'Parfait pour ton charisme et ta capacite a communiquer.',
                'sectors' => ['communication', 'creative'],
            ],
            [
                'title' => 'Designer UX/UI',
                'description' => 'Concoit des interfaces utilisateur intuitives et attractives.',
                'match_reason' => 'Ideal pour ta creativite et ta comprehension des utilisateurs.',
                'sectors' => ['tech', 'creative'],
            ],
            [
                'title' => 'Directeur Marketing',
                'description' => 'Definit et execute la strategie marketing des entreprises.',
                'match_reason' => 'Correspond a ton charisme et ta capacite de persuasion.',
                'sectors' => ['communication', 'finance'],
            ],
            [
                'title' => 'Fondateur de Startup',
                'description' => 'Lance de nouvelles entreprises innovantes dans divers secteurs.',
                'match_reason' => 'Parfait pour ton esprit entrepreneurial et ta tolerance au risque.',
                'sectors' => ['tech', 'finance'],
            ],
        ],
        'INFJ' => [
            [
                'title' => 'Psychologue',
                'description' => 'Accompagne les personnes dans leur developpement personnel et leurs difficultes.',
                'match_reason' => 'Ideal pour ton empathie profonde et ta comprehension des autres.',
                'sectors' => ['health', 'social'],
            ],
            [
                'title' => 'Conseiller en Orientation',
                'description' => 'Guide les jeunes dans leurs choix d\'etudes et de carriere.',
                'match_reason' => 'Parfait pour aider les autres a trouver leur voie.',
                'sectors' => ['education', 'social'],
            ],
            [
                'title' => 'Ecrivain / Auteur',
                'description' => 'Cree des oeuvres litteraires qui touchent et inspirent.',
                'match_reason' => 'Correspond a ta profondeur et ta capacite d\'expression.',
                'sectors' => ['creative', 'communication'],
            ],
            [
                'title' => 'Responsable RSE',
                'description' => 'Developpe la strategie de responsabilite sociale des entreprises.',
                'match_reason' => 'Ideal pour allier sens des affaires et impact positif.',
                'sectors' => ['finance', 'environment', 'social'],
            ],
            [
                'title' => 'Mediateur',
                'description' => 'Facilite la resolution des conflits entre parties.',
                'match_reason' => 'Parfait pour ta capacite a comprendre differents points de vue.',
                'sectors' => ['law', 'social'],
            ],
            [
                'title' => 'Coach de Vie',
                'description' => 'Accompagne les individus vers l\'atteinte de leurs objectifs personnels.',
                'match_reason' => 'Correspond a ton desir d\'aider les autres a s\'epanouir.',
                'sectors' => ['health', 'education'],
            ],
            [
                'title' => 'Directeur d\'ONG',
                'description' => 'Dirige des organisations a but non lucratif pour des causes importantes.',
                'match_reason' => 'Ideal pour combiner leadership et engagement social.',
                'sectors' => ['social'],
            ],
            [
                'title' => 'UX Researcher',
                'description' => 'Etudie les besoins des utilisateurs pour ameliorer les produits.',
                'match_reason' => 'Parfait pour ta capacite a comprendre les gens en profondeur.',
                'sectors' => ['tech', 'creative'],
            ],
            [
                'title' => 'Therapeute Art',
                'description' => 'Utilise l\'art comme outil therapeutique pour aider les patients.',
                'match_reason' => 'Correspond a ta sensibilite artistique et ton empathie.',
                'sectors' => ['health', 'creative'],
            ],
            [
                'title' => 'Consultant en Developpement Durable',
                'description' => 'Conseille les organisations sur leur transition ecologique.',
                'match_reason' => 'Ideal pour ton engagement pour un monde meilleur.',
                'sectors' => ['environment', 'finance'],
            ],
        ],
        'INFP' => [
            [
                'title' => 'Designer Graphique',
                'description' => 'Cree des visuels et identites de marque pour les entreprises.',
                'match_reason' => 'Parfait pour exprimer ta creativite et tes valeurs.',
                'sectors' => ['creative', 'communication'],
            ],
            [
                'title' => 'Redacteur Web / Copywriter',
                'description' => 'Ecrit des contenus engageants pour le web et la publicite.',
                'match_reason' => 'Ideal pour ta maitrise de l\'ecriture et ta sensibilite.',
                'sectors' => ['communication', 'creative'],
            ],
            [
                'title' => 'Animateur Socio-culturel',
                'description' => 'Organise des activites pour les communautes et groupes.',
                'match_reason' => 'Correspond a ton desir de creer du lien et d\'aider.',
                'sectors' => ['social', 'education'],
            ],
            [
                'title' => 'Musicien / Compositeur',
                'description' => 'Cree et interprete de la musique dans differents styles.',
                'match_reason' => 'Parfait pour exprimer tes emotions a travers l\'art.',
                'sectors' => ['creative'],
            ],
            [
                'title' => 'Bibliothecaire / Documentaliste',
                'description' => 'Gere et partage les ressources documentaires et culturelles.',
                'match_reason' => 'Ideal pour ton amour des livres et du savoir.',
                'sectors' => ['education', 'creative'],
            ],
            [
                'title' => 'Community Manager',
                'description' => 'Anime et federe les communautes en ligne des marques.',
                'match_reason' => 'Correspond a ta capacite a creer des liens authentiques.',
                'sectors' => ['communication', 'tech'],
            ],
            [
                'title' => 'Travailleur Social',
                'description' => 'Accompagne les personnes en difficulte vers l\'autonomie.',
                'match_reason' => 'Parfait pour ton empathie et ton desir d\'aider.',
                'sectors' => ['social'],
            ],
            [
                'title' => 'Illustrateur',
                'description' => 'Cree des illustrations pour livres, jeux, publicites.',
                'match_reason' => 'Ideal pour ta creativite et ton sens artistique.',
                'sectors' => ['creative'],
            ],
            [
                'title' => 'Formateur / Enseignant',
                'description' => 'Transmet des connaissances et forme des apprenants.',
                'match_reason' => 'Correspond a ton desir de partager et d\'accompagner.',
                'sectors' => ['education'],
            ],
            [
                'title' => 'Photographe',
                'description' => 'Capture des moments et raconte des histoires en images.',
                'match_reason' => 'Parfait pour ton oeil artistique et ta sensibilite.',
                'sectors' => ['creative', 'communication'],
            ],
        ],
        'ENFJ' => [
            [
                'title' => 'Directeur des Ressources Humaines',
                'description' => 'Gere le capital humain et la culture d\'entreprise.',
                'match_reason' => 'Ideal pour ton leadership bienveillant et ton sens des autres.',
                'sectors' => ['finance'],
            ],
            [
                'title' => 'Responsable Formation',
                'description' => 'Concoit et deploie les programmes de formation en entreprise.',
                'match_reason' => 'Parfait pour ta capacite a developper les talents.',
                'sectors' => ['education', 'finance'],
            ],
            [
                'title' => 'Coach Professionnel',
                'description' => 'Accompagne les professionnels dans leur developpement de carriere.',
                'match_reason' => 'Correspond a ton talent pour inspirer et guider.',
                'sectors' => ['education', 'finance'],
            ],
            [
                'title' => 'Diplomate',
                'description' => 'Represente son pays et negocie des accords internationaux.',
                'match_reason' => 'Ideal pour tes competences relationnelles et ta vision.',
                'sectors' => ['law', 'social'],
            ],
            [
                'title' => 'Directeur d\'Ecole',
                'description' => 'Dirige un etablissement scolaire et sa communaute educative.',
                'match_reason' => 'Parfait pour ton leadership et ton engagement educatif.',
                'sectors' => ['education'],
            ],
            [
                'title' => 'Charge de Communication',
                'description' => 'Gere l\'image et la communication d\'une organisation.',
                'match_reason' => 'Correspond a ton charisme et ta capacite de persuasion.',
                'sectors' => ['communication'],
            ],
            [
                'title' => 'Responsable Partenariats',
                'description' => 'Developpe et gere les relations avec les partenaires strategiques.',
                'match_reason' => 'Ideal pour ta capacite a creer des liens durables.',
                'sectors' => ['finance', 'communication'],
            ],
            [
                'title' => 'Mediateur Familial',
                'description' => 'Aide les familles a resoudre leurs conflits de maniere constructive.',
                'match_reason' => 'Parfait pour ton empathie et ton sens de la justice.',
                'sectors' => ['social', 'law'],
            ],
            [
                'title' => 'Organisateur d\'Evenements',
                'description' => 'Planifie et coordonne des evenements de toutes tailles.',
                'match_reason' => 'Correspond a ton energie et ta capacite d\'organisation.',
                'sectors' => ['communication', 'creative'],
            ],
            [
                'title' => 'Directeur de Fondation',
                'description' => 'Dirige une fondation philanthropique et ses programmes.',
                'match_reason' => 'Ideal pour allier leadership et engagement social.',
                'sectors' => ['social', 'finance'],
            ],
        ],
        'ENFP' => [
            [
                'title' => 'Responsable Marketing Digital',
                'description' => 'Developpe des strategies marketing innovantes sur le digital.',
                'match_reason' => 'Parfait pour ta creativite et ton energie communicative.',
                'sectors' => ['communication', 'tech'],
            ],
            [
                'title' => 'Journaliste',
                'description' => 'Informe le public sur l\'actualite et les sujets importants.',
                'match_reason' => 'Ideal pour ta curiosite et ton talent de storytelling.',
                'sectors' => ['communication'],
            ],
            [
                'title' => 'Comedien / Acteur',
                'description' => 'Interprete des roles au theatre, cinema ou television.',
                'match_reason' => 'Correspond a ton expressivite et ton charisme naturel.',
                'sectors' => ['creative'],
            ],
            [
                'title' => 'Entrepreneur Creatif',
                'description' => 'Lance des projets innovants dans les industries creatives.',
                'match_reason' => 'Parfait pour ton originalite et ton esprit d\'initiative.',
                'sectors' => ['creative', 'finance'],
            ],
            [
                'title' => 'Conseiller en Reconversion',
                'description' => 'Accompagne les professionnels dans leur changement de carriere.',
                'match_reason' => 'Ideal pour ton optimisme et ta comprehension des autres.',
                'sectors' => ['education', 'social'],
            ],
            [
                'title' => 'Directeur Artistique',
                'description' => 'Definit la direction creative des projets et campagnes.',
                'match_reason' => 'Correspond a ta vision artistique et ton leadership.',
                'sectors' => ['creative', 'communication'],
            ],
            [
                'title' => 'Animateur Radio/TV',
                'description' => 'Anime des emissions pour divertir et informer le public.',
                'match_reason' => 'Parfait pour ton energie et ton talent de communication.',
                'sectors' => ['communication'],
            ],
            [
                'title' => 'Responsable Experience Client',
                'description' => 'Ameliore l\'experience des clients a tous les points de contact.',
                'match_reason' => 'Ideal pour ton empathie et ta creativite.',
                'sectors' => ['finance', 'tech'],
            ],
            [
                'title' => 'Scenariste',
                'description' => 'Ecrit des histoires pour le cinema, la TV ou les jeux video.',
                'match_reason' => 'Correspond a ton imagination et ta capacite narrative.',
                'sectors' => ['creative'],
            ],
            [
                'title' => 'Fundraiser / Charge de Collecte',
                'description' => 'Mobilise des fonds pour des causes ou organisations.',
                'match_reason' => 'Parfait pour ton charisme et ton engagement.',
                'sectors' => ['social', 'communication'],
            ],
        ],
        'ISTJ' => [
            [
                'title' => 'Comptable / Expert-Comptable',
                'description' => 'Gere la comptabilite et conseille les entreprises sur leurs finances.',
                'match_reason' => 'Ideal pour ta rigueur et ton attention aux details.',
                'sectors' => ['finance'],
            ],
            [
                'title' => 'Auditeur Financier',
                'description' => 'Verifie la conformite des comptes et processus financiers.',
                'match_reason' => 'Parfait pour ta methodologie et ton sens de l\'ordre.',
                'sectors' => ['finance'],
            ],
            [
                'title' => 'Administrateur Systemes',
                'description' => 'Gere et maintient les infrastructures informatiques.',
                'match_reason' => 'Correspond a ta fiabilite et ton sens des responsabilites.',
                'sectors' => ['tech'],
            ],
            [
                'title' => 'Notaire',
                'description' => 'Authentifie les actes juridiques et conseille les particuliers.',
                'match_reason' => 'Ideal pour ta rigueur et ton respect des procedures.',
                'sectors' => ['law'],
            ],
            [
                'title' => 'Responsable Qualite',
                'description' => 'Assure le respect des normes et l\'amelioration continue.',
                'match_reason' => 'Parfait pour ton attention aux details et ta methodologie.',
                'sectors' => ['engineering', 'health'],
            ],
            [
                'title' => 'Ingenieur Civil',
                'description' => 'Concoit et supervise la construction d\'infrastructures.',
                'match_reason' => 'Correspond a ta rigueur et ton sens pratique.',
                'sectors' => ['engineering'],
            ],
            [
                'title' => 'Responsable Logistique',
                'description' => 'Optimise les flux de marchandises et la chaine d\'approvisionnement.',
                'match_reason' => 'Ideal pour tes capacites d\'organisation et de planification.',
                'sectors' => ['finance', 'engineering'],
            ],
            [
                'title' => 'Archiviste',
                'description' => 'Preserve et organise le patrimoine documentaire.',
                'match_reason' => 'Parfait pour ton sens de l\'ordre et ta methodologie.',
                'sectors' => ['education', 'law'],
            ],
            [
                'title' => 'Controleur de Gestion',
                'description' => 'Analyse les performances et aide au pilotage de l\'entreprise.',
                'match_reason' => 'Correspond a ta rigueur analytique et ton sens des chiffres.',
                'sectors' => ['finance'],
            ],
            [
                'title' => 'Inspecteur (Impots, Travail, etc.)',
                'description' => 'Verifie la conformite aux reglementations en vigueur.',
                'match_reason' => 'Ideal pour ton sens du devoir et ta rigueur.',
                'sectors' => ['law', 'finance'],
            ],
        ],
        'ISFJ' => [
            [
                'title' => 'Infirmier / Infirmiere',
                'description' => 'Prodigue des soins aux patients dans differents contextes.',
                'match_reason' => 'Parfait pour ton devouement et ton attention aux autres.',
                'sectors' => ['health'],
            ],
            [
                'title' => 'Assistant Social',
                'description' => 'Accompagne les personnes en difficulte dans leurs demarches.',
                'match_reason' => 'Ideal pour ton empathie et ton sens du service.',
                'sectors' => ['social'],
            ],
            [
                'title' => 'Enseignant Primaire',
                'description' => 'Eduque et accompagne les enfants dans leurs apprentissages.',
                'match_reason' => 'Correspond a ta patience et ton devouement.',
                'sectors' => ['education'],
            ],
            [
                'title' => 'Gestionnaire de Patrimoine',
                'description' => 'Conseille les clients sur la gestion de leurs actifs.',
                'match_reason' => 'Parfait pour ta fiabilite et ton sens du detail.',
                'sectors' => ['finance'],
            ],
            [
                'title' => 'Secretaire Medical',
                'description' => 'Assure la gestion administrative d\'un cabinet ou service medical.',
                'match_reason' => 'Ideal pour ton organisation et ton sens du service.',
                'sectors' => ['health'],
            ],
            [
                'title' => 'Dieteticien',
                'description' => 'Conseille sur l\'alimentation et la nutrition saine.',
                'match_reason' => 'Correspond a ton souci du bien-etre des autres.',
                'sectors' => ['health'],
            ],
            [
                'title' => 'Auxiliaire de Vie',
                'description' => 'Accompagne les personnes dependantes au quotidien.',
                'match_reason' => 'Parfait pour ta bienveillance et ta patience.',
                'sectors' => ['health', 'social'],
            ],
            [
                'title' => 'Gestionnaire RH',
                'description' => 'Gere les aspects administratifs des ressources humaines.',
                'match_reason' => 'Ideal pour ton organisation et ton sens du service.',
                'sectors' => ['finance'],
            ],
            [
                'title' => 'Pharmacien',
                'description' => 'Delivre les medicaments et conseille les patients.',
                'match_reason' => 'Correspond a ta rigueur et ton sens des responsabilites.',
                'sectors' => ['health'],
            ],
            [
                'title' => 'Responsable Service Client',
                'description' => 'Supervise la qualite du service rendu aux clients.',
                'match_reason' => 'Parfait pour ton attention aux autres et ta patience.',
                'sectors' => ['finance', 'communication'],
            ],
        ],
        'ESTJ' => [
            [
                'title' => 'Directeur des Operations',
                'description' => 'Supervise l\'ensemble des operations d\'une organisation.',
                'match_reason' => 'Ideal pour ton efficacite et ton sens de l\'organisation.',
                'sectors' => ['finance'],
            ],
            [
                'title' => 'Directeur de Production',
                'description' => 'Gere les processus de production et les equipes.',
                'match_reason' => 'Parfait pour ta capacite a organiser et diriger.',
                'sectors' => ['engineering'],
            ],
            [
                'title' => 'Officier Militaire',
                'description' => 'Dirige des unites et operations militaires.',
                'match_reason' => 'Correspond a ton leadership et ton sens du devoir.',
                'sectors' => ['law'],
            ],
            [
                'title' => 'Juge',
                'description' => 'Rend la justice et applique la loi de maniere equitable.',
                'match_reason' => 'Ideal pour ton sens de la justice et ta rigueur.',
                'sectors' => ['law'],
            ],
            [
                'title' => 'Chef de Projet Construction',
                'description' => 'Coordonne les projets de construction de A a Z.',
                'match_reason' => 'Parfait pour ton sens de l\'organisation et ta determination.',
                'sectors' => ['engineering'],
            ],
            [
                'title' => 'Directeur Commercial',
                'description' => 'Dirige la strategie et les equipes commerciales.',
                'match_reason' => 'Correspond a ton ambition et ton leadership.',
                'sectors' => ['finance'],
            ],
            [
                'title' => 'Proviseur / Principal',
                'description' => 'Dirige un etablissement scolaire et son equipe.',
                'match_reason' => 'Ideal pour ton autorite naturelle et ton sens des responsabilites.',
                'sectors' => ['education'],
            ],
            [
                'title' => 'Directeur Financier (CFO)',
                'description' => 'Supervise la strategie financiere d\'une organisation.',
                'match_reason' => 'Parfait pour ta rigueur et ton sens des affaires.',
                'sectors' => ['finance'],
            ],
            [
                'title' => 'Responsable Achats',
                'description' => 'Negocie et gere les approvisionnements de l\'entreprise.',
                'match_reason' => 'Correspond a ton sens de la negociation et ta rigueur.',
                'sectors' => ['finance', 'engineering'],
            ],
            [
                'title' => 'Directeur d\'Agence Bancaire',
                'description' => 'Dirige une agence bancaire et son equipe.',
                'match_reason' => 'Ideal pour ton leadership et ton sens commercial.',
                'sectors' => ['finance'],
            ],
        ],
        'ESFJ' => [
            [
                'title' => 'Medecin Generaliste',
                'description' => 'Soigne les patients et les oriente vers les specialistes.',
                'match_reason' => 'Parfait pour ton empathie et ton sens du service.',
                'sectors' => ['health'],
            ],
            [
                'title' => 'Responsable Evenementiel',
                'description' => 'Organise des evenements pour entreprises et particuliers.',
                'match_reason' => 'Ideal pour ton sens de l\'organisation et ta sociabilite.',
                'sectors' => ['communication', 'creative'],
            ],
            [
                'title' => 'Directeur d\'Hotel',
                'description' => 'Gere un etablissement hotelier et ses equipes.',
                'match_reason' => 'Correspond a ton sens de l\'accueil et du service.',
                'sectors' => ['finance'],
            ],
            [
                'title' => 'Conseiller en Assurance',
                'description' => 'Aide les clients a proteger leurs biens et leur famille.',
                'match_reason' => 'Parfait pour ta bienveillance et ton sens commercial.',
                'sectors' => ['finance'],
            ],
            [
                'title' => 'Responsable Relations Publiques',
                'description' => 'Gere l\'image et les relations avec les parties prenantes.',
                'match_reason' => 'Ideal pour tes competences relationnelles.',
                'sectors' => ['communication'],
            ],
            [
                'title' => 'Coordinateur de Soins',
                'description' => 'Organise la prise en charge des patients entre services.',
                'match_reason' => 'Correspond a ton sens de l\'organisation et ton empathie.',
                'sectors' => ['health'],
            ],
            [
                'title' => 'Wedding Planner',
                'description' => 'Organise des mariages et evenements familiaux.',
                'match_reason' => 'Parfait pour ta creativite et ton sens du detail.',
                'sectors' => ['creative', 'communication'],
            ],
            [
                'title' => 'Responsable de Communaute Religieuse',
                'description' => 'Anime et federe une communaute autour de valeurs partagees.',
                'match_reason' => 'Ideal pour ton devouement et ton sens de la communaute.',
                'sectors' => ['social'],
            ],
            [
                'title' => 'Agent Immobilier',
                'description' => 'Accompagne les clients dans leurs projets immobiliers.',
                'match_reason' => 'Correspond a ton sens commercial et relationnel.',
                'sectors' => ['finance'],
            ],
            [
                'title' => 'Responsable de Creche',
                'description' => 'Dirige une structure d\'accueil pour jeunes enfants.',
                'match_reason' => 'Parfait pour ta bienveillance et ton sens de l\'organisation.',
                'sectors' => ['education', 'social'],
            ],
        ],
        'ISTP' => [
            [
                'title' => 'Developpeur Full Stack',
                'description' => 'Developpe des applications web completes, front et back.',
                'match_reason' => 'Parfait pour ta logique et ton gout pour la technique.',
                'sectors' => ['tech'],
            ],
            [
                'title' => 'Ingenieur Mecanique',
                'description' => 'Concoit et ameliore des systemes mecaniques.',
                'match_reason' => 'Ideal pour ton sens pratique et ta comprehension des systemes.',
                'sectors' => ['engineering'],
            ],
            [
                'title' => 'Pilote de Drone',
                'description' => 'Opere des drones pour diverses applications professionnelles.',
                'match_reason' => 'Correspond a ton gout pour la technique et l\'action.',
                'sectors' => ['tech', 'engineering'],
            ],
            [
                'title' => 'Technicien Energies Renouvelables',
                'description' => 'Installe et maintient les systemes d\'energie verte.',
                'match_reason' => 'Parfait pour ton sens pratique et ton interet technique.',
                'sectors' => ['environment', 'engineering'],
            ],
            [
                'title' => 'Chirurgien',
                'description' => 'Realise des interventions chirurgicales de precision.',
                'match_reason' => 'Ideal pour ta precision et ton calme sous pression.',
                'sectors' => ['health'],
            ],
            [
                'title' => 'Analyste Forensique',
                'description' => 'Analyse les preuves numeriques pour les enquetes.',
                'match_reason' => 'Correspond a ta logique et ton attention aux details.',
                'sectors' => ['tech', 'law'],
            ],
            [
                'title' => 'Electricien Industriel',
                'description' => 'Installe et maintient les systemes electriques industriels.',
                'match_reason' => 'Parfait pour ton sens pratique et ta maitrise technique.',
                'sectors' => ['engineering'],
            ],
            [
                'title' => 'Mecanicien Aeronautique',
                'description' => 'Entretient et repare les aeronefs.',
                'match_reason' => 'Ideal pour ta precision et ton interet pour les machines.',
                'sectors' => ['engineering'],
            ],
            [
                'title' => 'Ingenieur Son',
                'description' => 'Gere les aspects techniques de la production sonore.',
                'match_reason' => 'Correspond a ta maitrise technique et ton sens artistique.',
                'sectors' => ['creative', 'tech'],
            ],
            [
                'title' => 'Pompier',
                'description' => 'Intervient pour secourir les personnes et combattre les incendies.',
                'match_reason' => 'Parfait pour ton calme et ta capacite d\'action rapide.',
                'sectors' => ['social'],
            ],
        ],
        'ISFP' => [
            [
                'title' => 'Artiste Peintre',
                'description' => 'Cree des oeuvres picturales exprimant sa vision du monde.',
                'match_reason' => 'Parfait pour ta sensibilite artistique et ton authenticite.',
                'sectors' => ['creative'],
            ],
            [
                'title' => 'Veterinaire',
                'description' => 'Soigne les animaux et conseille leurs proprietaires.',
                'match_reason' => 'Ideal pour ta connexion avec la nature et les etres vivants.',
                'sectors' => ['health'],
            ],
            [
                'title' => 'Styliste / Designer Mode',
                'description' => 'Cree des vetements et accessoires de mode.',
                'match_reason' => 'Correspond a ta creativite et ton sens esthetique.',
                'sectors' => ['creative'],
            ],
            [
                'title' => 'Chef Cuisinier',
                'description' => 'Cree des plats et dirige une cuisine.',
                'match_reason' => 'Parfait pour ta creativite et ton sens artistique.',
                'sectors' => ['creative'],
            ],
            [
                'title' => 'Kinesitherapeute',
                'description' => 'Aide les patients a retrouver leur mobilite.',
                'match_reason' => 'Ideal pour ton sens du toucher et ton empathie.',
                'sectors' => ['health'],
            ],
            [
                'title' => 'Fleuriste',
                'description' => 'Compose des arrangements floraux artistiques.',
                'match_reason' => 'Correspond a ton amour de la beaute naturelle.',
                'sectors' => ['creative'],
            ],
            [
                'title' => 'Monteur Video',
                'description' => 'Assemble et edite des contenus video.',
                'match_reason' => 'Parfait pour ta sensibilite visuelle et ta creativite.',
                'sectors' => ['creative', 'tech'],
            ],
            [
                'title' => 'Decorateur d\'Interieur',
                'description' => 'Amenage des espaces de vie esthetiques et fonctionnels.',
                'match_reason' => 'Ideal pour ton sens esthetique et pratique.',
                'sectors' => ['creative'],
            ],
            [
                'title' => 'Masseur Bien-etre',
                'description' => 'Procure des soins de relaxation et bien-etre.',
                'match_reason' => 'Correspond a ta sensibilite et ton attention aux autres.',
                'sectors' => ['health'],
            ],
            [
                'title' => 'Tatoueur',
                'description' => 'Cree des oeuvres d\'art sur la peau.',
                'match_reason' => 'Parfait pour ta creativite et ta precision.',
                'sectors' => ['creative'],
            ],
        ],
        'ESTP' => [
            [
                'title' => 'Commercial Terrain',
                'description' => 'Prospecte et vend des produits/services directement aux clients.',
                'match_reason' => 'Parfait pour ton charisme et ton energie.',
                'sectors' => ['finance'],
            ],
            [
                'title' => 'Entrepreneur',
                'description' => 'Lance et developpe ses propres projets d\'entreprise.',
                'match_reason' => 'Ideal pour ton gout du risque et ta determination.',
                'sectors' => ['finance'],
            ],
            [
                'title' => 'Trader',
                'description' => 'Achete et vend des actifs financiers sur les marches.',
                'match_reason' => 'Correspond a ton gout du risque et ta reactivite.',
                'sectors' => ['finance'],
            ],
            [
                'title' => 'Coach Sportif',
                'description' => 'Entraine et motive des sportifs ou particuliers.',
                'match_reason' => 'Parfait pour ton energie et ton charisme.',
                'sectors' => ['health'],
            ],
            [
                'title' => 'Agent Immobilier de Luxe',
                'description' => 'Vend des proprietes haut de gamme.',
                'match_reason' => 'Ideal pour ton sens commercial et ta persuasion.',
                'sectors' => ['finance'],
            ],
            [
                'title' => 'Cascadeur',
                'description' => 'Realise des figures dangereuses pour le cinema.',
                'match_reason' => 'Correspond a ton gout du risque et de l\'action.',
                'sectors' => ['creative'],
            ],
            [
                'title' => 'Paramedic / Urgentiste',
                'description' => 'Intervient en premiere ligne sur les urgences medicales.',
                'match_reason' => 'Parfait pour ta reactivite et ton calme sous pression.',
                'sectors' => ['health'],
            ],
            [
                'title' => 'Responsable de Bar/Restaurant',
                'description' => 'Gere un etablissement de restauration.',
                'match_reason' => 'Ideal pour ton energie et ton sens des affaires.',
                'sectors' => ['finance'],
            ],
            [
                'title' => 'Sportif Professionnel',
                'description' => 'Compete au plus haut niveau dans sa discipline.',
                'match_reason' => 'Correspond a ton esprit de competition et ta determination.',
                'sectors' => ['creative'],
            ],
            [
                'title' => 'Negociateur Commercial',
                'description' => 'Negocie des contrats importants pour les entreprises.',
                'match_reason' => 'Parfait pour ta persuasion et ta confiance.',
                'sectors' => ['finance'],
            ],
        ],
        'ESFP' => [
            [
                'title' => 'Animateur TV / Radio',
                'description' => 'Presente des emissions de divertissement.',
                'match_reason' => 'Parfait pour ton charisme et ton energie communicative.',
                'sectors' => ['communication'],
            ],
            [
                'title' => 'Danseur Professionnel',
                'description' => 'Performe dans des spectacles et productions.',
                'match_reason' => 'Ideal pour ton expressivite et ton sens du rythme.',
                'sectors' => ['creative'],
            ],
            [
                'title' => 'Guide Touristique',
                'description' => 'Fait decouvrir les richesses d\'un lieu aux visiteurs.',
                'match_reason' => 'Correspond a ton enthousiasme et ta sociabilite.',
                'sectors' => ['communication'],
            ],
            [
                'title' => 'Steward / Hotesse de l\'Air',
                'description' => 'Assure le confort et la securite des passagers aeriens.',
                'match_reason' => 'Parfait pour ton sens du service et ta presentation.',
                'sectors' => ['finance'],
            ],
            [
                'title' => 'DJ Professionnel',
                'description' => 'Anime des soirees et evenements musicaux.',
                'match_reason' => 'Ideal pour ton sens de la fete et ta creativite.',
                'sectors' => ['creative'],
            ],
            [
                'title' => 'Vendeur de Luxe',
                'description' => 'Vend des produits haut de gamme dans des boutiques selectes.',
                'match_reason' => 'Correspond a ton charme et ton sens commercial.',
                'sectors' => ['finance'],
            ],
            [
                'title' => 'Mannequin',
                'description' => 'Presente des vetements et produits pour les marques.',
                'match_reason' => 'Parfait pour ta presence et ton aisance.',
                'sectors' => ['creative'],
            ],
            [
                'title' => 'Estheticienne',
                'description' => 'Prodigue des soins de beaute et bien-etre.',
                'match_reason' => 'Ideal pour ton sens esthetique et ton contact humain.',
                'sectors' => ['health'],
            ],
            [
                'title' => 'Responsable Animation',
                'description' => 'Organise les activites dans les clubs et resorts.',
                'match_reason' => 'Correspond a ton energie et ta joie de vivre.',
                'sectors' => ['communication'],
            ],
            [
                'title' => 'Comique / Humoriste',
                'description' => 'Fait rire le public avec des spectacles d\'humour.',
                'match_reason' => 'Parfait pour ton sens de la scene et ta spontaneite.',
                'sectors' => ['creative'],
            ],
        ],
    ];

    /**
     * Recupere les metiers pour un type MBTI
     */
    public static function getCareersForType(string $type): array
    {
        return self::CAREERS_BY_TYPE[strtoupper($type)] ?? [];
    }

    /**
     * Recupere les secteurs pour un type MBTI
     */
    public static function getSectorsForType(string $type): array
    {
        $careers = self::getCareersForType($type);
        $sectors = [];

        foreach ($careers as $career) {
            foreach ($career['sectors'] ?? [] as $sectorCode) {
                if (! isset($sectors[$sectorCode])) {
                    $sectors[$sectorCode] = self::SECTORS[$sectorCode] ?? ['name' => $sectorCode];
                }
            }
        }

        return $sectors;
    }

    /**
     * Recupere tous les secteurs
     */
    public static function getAllSectors(): array
    {
        return self::SECTORS;
    }

    /**
     * Trouve les types MBTI associes a un secteur
     */
    public static function getTypesForSector(string $sectorCode): array
    {
        $types = [];

        foreach (self::CAREERS_BY_TYPE as $type => $careers) {
            foreach ($careers as $career) {
                if (in_array($sectorCode, $career['sectors'] ?? [])) {
                    if (! in_array($type, $types)) {
                        $types[] = $type;
                    }
                    break;
                }
            }
        }

        return $types;
    }

    /**
     * Trouve les metiers dans un secteur pour un type MBTI
     */
    public static function getCareersInSectorForType(string $type, string $sectorCode): array
    {
        $careers = self::getCareersForType($type);

        return array_filter($careers, function ($career) use ($sectorCode) {
            return in_array($sectorCode, $career['sectors'] ?? []);
        });
    }
}
