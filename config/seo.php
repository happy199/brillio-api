<?php

return [
    /*
     |--------------------------------------------------------------------------
     | SEO Configuration - Brillio
     |--------------------------------------------------------------------------
     |
     | Configuration centralisée pour l'optimisation SEO du site Brillio.
     | Mots-clés, meta tags, et paramètres par page.
     |
     */

    'default' => [
        'title' => 'Brillio | Plateforme de Mentorat Professionnel en Afrique',
        'title_separator' => ' - ',
        'description' => 'Brillio connecte les jeunes Africains avec des mentors experts. Orientation carrière, test MBTI gratuit, coaching personnalisé au Bénin, Sénégal, Cameroun et toute l\'Afrique.',
        'keywords' => 'mentorat afrique, orientation professionnelle, coaching carrière, mentor bénin, développement professionnel, MBTI, conseiller orientation',
        'author' => 'Brillio',
        'image' => '/images/og-default.jpg',
        'locale' => 'fr_FR',
        'type' => 'website',
    ],

    'site' => [
        'name' => 'Brillio',
        'url' => env('APP_URL', 'https://brillio.africa'),
        'twitter' => '@brillioafrica',
    ],

    'organization' => [
        'name' => 'Brillio',
        'legal_name' => 'Brillio Africa',
        'foundation_date' => '2026-01-19',
        'email' => 'contact@brillio.africa',
        'phone' => '+229 01 66 30 17 36',
        'address' => [
            'street' => 'Cotonou',
            'city' => 'Cotonou',
            'region' => 'Littoral',
            'postal_code' => '',
            'country' => 'BJ',
        ],
    ],

    'social' => [
        'facebook' => 'https://www.facebook.com/share/1E5k4UqPqB',
        'instagram' => 'https://www.instagram.com/brillioafrica/',
        'linkedin' => 'https://www.linkedin.com/company/brillio-africa',
        'threads' => 'https://www.threads.com/@brillioafrica',
        'tiktok' => 'https://www.tiktok.com/@brillioafrica',
        'youtube' => 'https://www.youtube.com/channel/UCGUzyoVoVLXzNGxhRN8tXCg',
    ],

    /*
     |--------------------------------------------------------------------------
     | Pages SEO Configuration
     |--------------------------------------------------------------------------
     */

    'pages' => [
        'home' => [
            'title' => 'Brillio | Plateforme de Mentorat Professionnel en Afrique - Trouve Ton Mentor',
            'description' => 'Brillio connecte +500 mentors experts avec les jeunes Africains. Orientation carrière, test MBTI gratuit, coaching personnalisé. Bénin, Sénégal, Cameroun, Côte d\'Ivoire.',
            'keywords' => 'mentorat professionnel afrique, plateforme mentorat, orientation carrière jeunes africains, coaching professionnel, mentor bénin, MBTI afrique, développement carrière',
            'og_title' => 'Brillio - Plateforme de Mentorat #1 en Afrique',
            'og_description' => 'Trouve ton mentor expert parmi +500 professionnels. Développement web, business, médecine, ingénierie. Gratuit pour les jeunes.',
            'og_image' => '/images/og-image.png',
            'schema_type' => 'WebSite',
        ],

        'mentors' => [
            'title' => 'Trouvez Votre Mentor Professionnel | +500 Experts en Afrique | Brillio',
            'description' => 'Découvrez +500 mentors experts dans tous les domaines : développement web, business, médecine, ingénierie, marketing. Connexion gratuite avec des professionnels expérimentés.',
            'keywords' => 'trouver mentor afrique, mentors experts, coaching professionnel, orientation carrière, find mentor africa, professional guidance',
            'og_title' => 'Trouvez Votre Mentor Parmi +500 Experts Africains',
            'og_description' => 'Connectez avec des professionnels expérimentés dans votre domaine. Gratuit et accessible à tous les jeunes.',
            'og_image' => '/images/og-image.png',
        ],

        'contact' => [
            'title' => 'Contactez Brillio | Support & Partenariats Mentorat Afrique',
            'description' => 'Contactez l\'équipe Brillio pour toute question, partenariat ou support. Bureau à Cotonou, Bénin. Email: contact@brillio.africa, Tél: +229 01 66 30 17 36',
            'keywords' => 'contact brillio, support mentorat, partenariat afrique, brillio cotonou, contact mentor afrique',
            'og_title' => 'Contactez Brillio - Support & Partenariats',
            'og_description' => 'Questions, partenariats, support technique. Notre équipe à Cotonou répond à toutes vos demandes.',
            'og_image' => '/images/og-image.png',
        ],

        'about' => [
            'title' => 'À Propos de Brillio | Mission, Vision & Impact en Afrique',
            'description' => 'Brillio est la plateforme de mentorat professionnelle qui connecte jeunes talents et experts en Afrique. Notre mission : démocratiser l\'accès à l\'orientation de carrière.',
            'keywords' => 'à propos brillio, mission mentorat afrique, vision brillio, impact jeunes africains, plateforme orientation',
            'og_title' => 'Notre Mission : Démocratiser le Mentorat en Afrique',
            'og_description' => 'Depuis janvier 2026, nous connectons des milliers de jeunes avec des mentors experts pour construire l\'avenir de l\'Afrique.',
            'og_image' => '/images/og-image.png',
        ],

        'privacy' => [
            'title' => 'Politique de Confidentialité | Brillio - Protection des Données',
            'description' => 'Politique de confidentialité et protection des données personnelles sur Brillio. Conformité RGPD et transparence totale sur l\'utilisation de vos informations.',
            'keywords' => 'politique confidentialité, protection données, RGPD brillio, privacy policy africa',
            'og_image' => '/images/og-image.png',
        ],

        'terms' => [
            'title' => 'Conditions d\'Utilisation | Brillio - Règles & Engagement',
            'description' => 'Conditions générales d\'utilisation de la plateforme Brillio. Droits, devoirs et engagement pour une communauté de mentorat respectueuse et professionnelle.',
            'keywords' => 'conditions utilisation, CGU brillio, règles plateforme, terms of service',
            'og_image' => '/images/og-image.png',
        ],

        'login' => [
            'title' => 'Connexion | Brillio - Accédez à Votre Espace',
            'description' => 'Connectez-vous à Brillio pour accéder à votre espace personnel, échanger avec des mentors, consulter vos sessions et continuer votre parcours professionnel.',
            'keywords' => 'connexion brillio, login, se connecter, espace personnel, authentification',
            'og_title' => 'Connectez-vous à Brillio',
            'og_description' => 'Accédez à votre espace pour continuer votre parcours avec +500 mentors africains.',
            'og_image' => '/images/og-image.png',
        ],
    ],

    /*
     |--------------------------------------------------------------------------
     | Keywords Configuration
     |--------------------------------------------------------------------------
     */

    'keywords' => [
        'primary_fr' => [
            // Mentorat & Orientation
            'mentorat professionnel afrique',
            'orientation carrière afrique',
            'coaching jeunes africains',
            'plateforme mentorat en ligne',
            'conseiller orientation professionnelle',
            'développement carrière afrique',
            'mentor bénin',
            'trouver un mentor',
            'mentorat personnalisé',

            // Formation & Éducation
            'formation professionnelle afrique',
            'formation en ligne gratuite',
            'apprentissage professionnel',
            'éducation en afrique',
            'formation continue',
            'certification professionnelle',
            'cours en ligne afrique',
            'e-learning afrique',
            'plateforme éducative',
            'formation diplômante',

            // Emploi & Recrutement
            'recherche emploi afrique',
            'offre emploi jeunes',
            'recrutement afrique',
            'opportunités professionnelles',
            'insertion professionnelle',
            'premier emploi afrique',
            'stage professionnel',
            'emploi jeunes diplômés',

            // Compétences & Développement
            'compétences professionnelles',
            'soft skills',
            'développement personnel',
            'montée en compétences',
            'leadership afrique',
            'savoir-faire professionnel',
            'intelligence émotionnelle',

            // CV, Portfolio & Candidature
            'rédaction cv afrique',
            'lettre de motivation',
            'portfolio professionnel',
            'cv en ligne',
            'optimiser son cv',
            'candidature emploi',
            'personal branding',

            // Coaching & Accompagnement
            'coaching carrière',
            'accompagnement professionnel',
            'conseil carrière',
            'bilan de compétences',
            'coaching en ligne',
            'développement carrière',

            // Test & Personnalité
            'test MBTI gratuit afrique',
            'test personnalité professionnelle',
            'test orientation',
            'découvrir sa personnalité',
            'profil professionnel',

            // Technologie & Outils
            'visioconférence afrique',
            'meeting en ligne',
            'plateforme collaborative',
            'networking professionnel',
            'réseau professionnel afrique',

            // Secteurs spécifiques
            'école de commerce afrique',
            'formation technique',
            'entrepreneuriat afrique',
            'startup afrique',
            'innovation afrique',
        ],

        'primary_en' => [
            'career mentoring africa',
            'professional guidance africa',
            'youth empowerment africa',
            'online mentorship platform',
            'career counseling africa',
            'find mentor africa',
            'professional development africa',
            'career coaching online',
            'job search africa',
            'skills training africa',
            'online learning platform',
            'professional networking',
            'resume building',
            'interview preparation',
            'career advancement',
        ],

        'secondary_fr' => [
            // Parcours professionnel
            'reconversion professionnelle',
            'évolution de carrière',
            'promotion professionnelle',
            'mobilité professionnelle',
            'projet professionnel',

            // Secteurs d'activité
            'secteur privé afrique',
            'fonction publique',
            'ong afrique',
            'organisations internationales',
            'entreprises africaines',

            // Formation continue
            'formation adulte',
            'formation courte durée',
            'atelier professionnel',
            'webinaire gratuit',
            'masterclass',

            // Jeunesse & Insertion
            'emploi jeunes africains',
            'programme jeunes talents',
            'insertion jeunes diplômés',
            'jeunes entrepreneurs',
            'génération z emploi',

            // Outils professionnels
            'entretien embauche',
            'préparation entretien',
            'simulation entretien',
            'négociation salaire',
            'plan de carrière',
        ],

        'secondary_en' => [
            'professional skills training',
            'youth employment africa',
            'career development programs',
            'online career guidance',
            'mentorship programs africa',
            'professional certification',
            'job interview preparation',
            'career transition',
            'entrepreneurship training',
            'leadership development',
        ],

        'long_tail_fr' => [
            // Questions pratiques
            'comment trouver un mentor au bénin',
            'comment trouver un mentor au sénégal',
            'comment trouver un mentor en côte d\'ivoire',
            'où trouver un mentor professionnel gratuit',
            'plateforme gratuite mentorat afrique',

            // Orientation
            'orientation professionnelle en ligne gratuite',
            'quel métier choisir en afrique',
            'choisir sa carrière professionnelle',
            'comment bien s\'orienter professionnellement',

            // Tests & Évaluation
            'test de personnalité mbti gratuit afrique',
            'test orientation professionnelle gratuit',
            'évaluation compétences professionnelles',
            'découvrir ses talents',

            // Formation
            'formation gratuite en ligne avec certificat',
            'meilleure formation en ligne afrique',
            'cours gratuits développement personnel',
            'certification professionnelle reconnue',

            // Emploi
            'comment trouver un emploi rapidement afrique',
            'premier emploi après études',
            'stage professionnel rémunéré',
            'opportunités emploi jeunes diplômés',

            // CV & Candidature
            'comment faire un bon cv afrique',
            'exemple lettre de motivation',
            'créer portfolio professionnel gratuit',
            'rédiger cv sans expérience',

            // Développement
            'développement de carrière pour les jeunes',
            'meilleur plateforme de mentorat afrique',
            'coaching carrière en ligne gratuit',
            'apprendre nouvelles compétences gratuitement',

            // Domaines spécifiques
            'mentorat développement web afrique',
            'mentor business afrique',
            'coaching entrepreneuriat gratuit',
            'formation marketing digital afrique',
        ],

        'long_tail_en' => [
            'how to find a mentor in africa',
            'best career guidance platforms africa',
            'free personality test mbti',
            'professional mentorship online',
            'how to choose a career path',
            'free online courses with certificates',
            'career development for young professionals',
            'job search tips africa',
            'resume writing help',
            'interview skills training',
        ],
    ],


    /*
     |--------------------------------------------------------------------------
     | Robots & Crawling
     |--------------------------------------------------------------------------
     */

    'robots' => [
        'index' => true,
        'follow' => true,
        'max_snippet' => -1,
        'max_image_preview' => 'large',
        'max_video_preview' => -1,
    ],

    /*
     |--------------------------------------------------------------------------
     | Structured Data (Schema.org)
     |--------------------------------------------------------------------------
     */

    'schema' => [
        'enable_organization' => true,
        'enable_website' => true,
        'enable_breadcrumbs' => true,
        'enable_local_business' => true,
    ],

    /*
     |--------------------------------------------------------------------------
     | International SEO
     |--------------------------------------------------------------------------
     */

    'locales' => [
        'fr' => [
            'code' => 'fr',
            'hreflang' => 'fr',
            'name' => 'Français',
            'url_prefix' => '',
        ],
        'en' => [
            'code' => 'en',
            'hreflang' => 'en',
            'name' => 'English',
            'url_prefix' => '/en',
        ],
    ],

    'geo_targeting' => [
        'all_african_countries' => [
            // Afrique du Nord
            'DZ', // Algérie
            'EG', // Égypte
            'LY', // Libye
            'MA', // Maroc
            'TN', // Tunisie
            'EH', // Sahara occidental

            // Afrique de l'Ouest
            'BJ', // Bénin
            'BF', // Burkina Faso
            'CV', // Cap-Vert
            'CI', // Côte d'Ivoire
            'GM', // Gambie
            'GH', // Ghana
            'GN', // Guinée
            'GW', // Guinée-Bissau
            'LR', // Libéria
            'ML', // Mali
            'MR', // Mauritanie
            'NE', // Niger
            'NG', // Nigéria
            'SN', // Sénégal
            'SL', // Sierra Leone
            'TG', // Togo

            // Afrique Centrale
            'AO', // Angola
            'CM', // Cameroun
            'CF', // Centrafrique
            'TD', // Tchad
            'CG', // Congo-Brazzaville
            'CD', // RD Congo
            'GQ', // Guinée équatoriale
            'GA', // Gabon
            'ST', // São Tomé-et-Príncipe

            // Afrique de l'Est
            'BI', // Burundi
            'KM', // Comores
            'DJ', // Djibouti
            'ER', // Érythrée
            'ET', // Éthiopie
            'KE', // Kenya
            'MG', // Madagascar
            'MW', // Malawi
            'MU', // Maurice
            'MZ', // Mozambique
            'RW', // Rwanda
            'SC', // Seychelles
            'SO', // Somalie
            'SS', // Soudan du Sud
            'SD', // Soudan
            'TZ', // Tanzanie
            'UG', // Ouganda
            'ZM', // Zambie
            'ZW', // Zimbabwe

            // Afrique Australe
            'BW', // Botswana
            'LS', // Lesotho
            'NA', // Namibie
            'ZA', // Afrique du Sud
            'SZ', // Eswatini (Swaziland)
        ],
    ],

];