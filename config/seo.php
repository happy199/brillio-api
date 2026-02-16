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
            'schema_type' => 'WebSite',
        ],

        'mentors' => [
            'title' => 'Trouvez Votre Mentor Professionnel | +500 Experts en Afrique | Brillio',
            'description' => 'Découvrez +500 mentors experts dans tous les domaines : développement web, business, médecine, ingénierie, marketing. Connexion gratuite avec des professionnels expérimentés.',
            'keywords' => 'trouver mentor afrique, mentors experts, coaching professionnel, orientation carrière, find mentor africa, professional guidance',
            'og_title' => 'Trouvez Votre Mentor Parmi +500 Experts Africains',
            'og_description' => 'Connectez avec des professionnels expérimentés dans votre domaine. Gratuit et accessible à tous les jeunes.',
        ],

        'contact' => [
            'title' => 'Contactez Brillio | Support & Partenariats Mentorat Afrique',
            'description' => 'Contactez l\'équipe Brillio pour toute question, partenariat ou support. Bureau à Cotonou, Bénin. Email: contact@brillio.africa, Tél: +229 01 66 30 17 36',
            'keywords' => 'contact brillio, support mentorat, partenariat afrique, brillio cotonou, contact mentor afrique',
            'og_title' => 'Contactez Brillio - Support & Partenariats',
            'og_description' => 'Questions, partenariats, support technique. Notre équipe à Cotonou répond à toutes vos demandes.',
        ],

        'about' => [
            'title' => 'À Propos de Brillio | Mission, Vision & Impact en Afrique',
            'description' => 'Brillio est la plateforme de mentorat professionnelle qui connecte jeunes talents et experts en Afrique. Notre mission : démocratiser l\'accès à l\'orientation de carrière.',
            'keywords' => 'à propos brillio, mission mentorat afrique, vision brillio, impact jeunes africains, plateforme orientation',
            'og_title' => 'Notre Mission : Démocratiser le Mentorat en Afrique',
            'og_description' => 'Depuis 2024, nous connectons des milliers de jeunes avec des mentors experts pour construire l\'avenir de l\'Afrique.',
        ],

        'privacy' => [
            'title' => 'Politique de Confidentialité | Brillio - Protection des Données',
            'description' => 'Politique de confidentialité et protection des données personnelles sur Brillio. Conformité RGPD et transparence totale sur l\'utilisation de vos informations.',
            'keywords' => 'politique confidentialité, protection données, RGPD brillio, privacy policy africa',
        ],

        'terms' => [
            'title' => 'Conditions d\'Utilisation | Brillio - Règles & Engagement',
            'description' => 'Conditions générales d\'utilisation de la plateforme Brillio. Droits, devoirs et engagement pour une communauté de mentorat respectueuse et professionnelle.',
            'keywords' => 'conditions utilisation, CGU brillio, règles plateforme, terms of service',
        ],
    ],

    /*
     |--------------------------------------------------------------------------
     | Keywords Configuration
     |--------------------------------------------------------------------------
     */

    'keywords' => [
        'primary_fr' => [
            'mentorat professionnel afrique',
            'orientation carrière afrique',
            'coaching jeunes africains',
            'plateforme mentorat en ligne',
            'conseiller orientation professionnelle',
            'développement carrière afrique',
            'mentor bénin',
            'test MBTI gratuit afrique',
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
        ],

        'secondary_fr' => [
            'formation professionnelle',
            'secteur privé afrique',
            'emploi jeunes africains',
            'compétences professionnelles',
            'réorientation professionnelle',
            'coaching carrière en ligne',
        ],

        'secondary_en' => [
            'professional skills training',
            'youth employment africa',
            'career development programs',
            'online career guidance',
            'mentorship programs africa',
        ],

        'long_tail_fr' => [
            'comment trouver un mentor au bénin',
            'orientation professionnelle en ligne gratuite',
            'test de personnalité mbti gratuit afrique',
            'développement de carrière pour les jeunes',
            'meilleur plateforme de mentorat afrique',
        ],

        'long_tail_en' => [
            'how to find a mentor in africa',
            'best career guidance platforms africa',
            'free personality test mbti',
            'professional mentorship online',
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