@extends('layouts.public')

{{-- SEO Met a Tags --}}
<x-seo-meta page="privacy" />

@section('content')
<!-- Hero Section -->
<section class="gradient-hero pt-32 pb-16 relative">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="max-w-3xl mx-auto text-center text-white">
            <h1 class="text-4xl font-bold mb-4">Politique de confidentialité</h1>
            <p class="text-white/80">Dernière mise à jour : {{ date('d/m/Y') }}</p>
        </div>
    </div>
</section>

<!-- Content -->
<section class="py-16 bg-white">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto prose prose-lg">
            <!-- TODO: Personnaliser la politique de confidentialité avec un avocat -->

            <h2>1. Introduction</h2>
            <p>
                Bienvenue sur Brillio. Nous accordons une importance capitale à la protection de vos données personnelles.
                Cette politique de confidentialité est établie conformément à la <strong>Loi n° 2017-20 du 20 avril 2018 portant Code du numérique en République du Bénin</strong> (Livre Cinquième) et aux standards de la <strong>CEDEAO</strong> (Acte Additionnel A/SA.1/01/10).
            </p>
            <p>
                Elle explique comment nous collectons, utilisons, stockons et protégeons vos informations lorsque vous utilisez notre plateforme.
            </p>

            <h2>2. Données collectées</h2>
            <p>Nous collectons les types de données suivants :</p>

            <h3>2.1 Données que vous nous fournissez</h3>
            <ul>
                <li><strong>Informations d'identification :</strong> nom, prénom, adresse email, mot de passe, date de naissance, pays, ville.</li>
                <li><strong>Données de profil :</strong> photo de profil, parcours académique, centres d'intérêt.</li>
                <li><strong>Données Sensibles (Psychotechniques) :</strong> vos réponses au test de personnalité MBTI et les profils psychologiques qui en découlent. <em>Conformément à l'Article 402 du Code du Numérique, ces données font l'objet d'une protection renforcée.</em></li>
                <li><strong>Interactions IA :</strong> messages échangés avec notre assistant d'orientation intelligent.</li>
                <li><strong>Documents :</strong> copies de diplômes ou bulletins que vous téléchargez pour votre dossier d'orientation.</li>
            </ul>

            <h3>2.2 Données collectées automatiquement</h3>
            <p>Lors de votre navigation, nous collectons : l'adresse IP, le type d'appareil, le système d'exploitation et les données d'usage (clics, temps passé).</p>

            <h2>3. Finalités du traitement</h2>
            <p>Vos données sont traitées pour des finalités spécifiques et légitimes :</p>
            <ul>
                <li>Analyse de votre profil pour une orientation scolaire et professionnelle personnalisée.</li>
                <li>Mise en relation avec des mentors adaptés à votre type de personnalité.</li>
                <li>Amélioration de nos algorithmes de recommandation via l'intelligence artificielle.</li>
                <li>Sécurisation de votre espace personnel.</li>
                <li>Communication relative à l'évolution de nos services.</li>
            </ul>

            <h2>4. Partage et Transfert des données</h2>
            <p><strong>Nous ne commercialisons pas vos données personnelles.</strong></p>
            <p>Vos données peuvent être transmises à :</p>
            <ul>
                <li><strong>Sous-traitants techniques :</strong> hébergeurs cloud sécurisés et services d'IA (DeepSeek/OpenRouter).</li>
                <li><strong>Organisations partenaires :</strong> uniquement si vous êtes lié à une organisation (école, association) via un code d'invitation spécifique.</li>
            </ul>
            <p>
                <strong>Transfert international :</strong> Nos serveurs peuvent être situés hors du territoire béninois. En utilisant Brillio, vous consentez au transfert de vos données vers ces serveurs, pour lesquels nous garantissons un niveau de sécurité conforme aux standards internationaux.
            </p>

            <h2>5. Vos Droits et Recours</h2>
            <p>Conformément au Code du Numérique, vous disposez des droits suivants :</p>
            <ul>
                <li><strong>Accès et Rectification :</strong> modifier vos informations à tout moment.</li>
                <li><strong>Oubli et Suppression :</strong> supprimer définitivement votre compte et toutes les données associées.</li>
                <li><strong>Opposition :</strong> refuser certains traitements de données.</li>
                <li><strong>Portabilité :</strong> récupérer vos données dans un format structuré.</li>
            </ul>
            <p>
                Pour toute réclamation, vous avez le droit de saisir l'<strong>Autorité de Protection des Données à Caractère Personnel (APDP)</strong> du Bénin (<a href="https://www.apdp.bj" target="_blank">www.apdp.bj</a>).
            </p>

            <h2>6. Sécurité</h2>
            <p>
                Nous mettons en œuvre des mesures techniques (chiffrement TLS, hachage des mots de passe) et organisationnelles (accès restreints) pour prévenir toute faille de sécurité. En cas de violation de données, nous nous engageons à informer l'APDP et les utilisateurs concernés dans les délais légaux.
            </p>

            <h2>7. Contact</h2>
            <p>
                Pour toute question ou pour exercer vos droits, contactez notre responsable de la protection des données :
            </p>
            <ul>
                <li>Email : <a href="mailto:contact@brillio.africa">contact@brillio.africa</a></li>
                <li>Adresse : Cotonou, République du Bénin</li>
            </ul>
        </div>
    </div>
</section>
@endsection