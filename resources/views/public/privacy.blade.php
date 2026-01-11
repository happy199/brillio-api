@extends('layouts.public')

@section('title', 'Politique de confidentialité - Brillio')
@section('meta_description', 'Politique de confidentialité de Brillio. Découvrez comment nous collectons, utilisons et protégeons vos données personnelles.')

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
                    Bienvenue sur Brillio. Nous accordons une grande importance à la protection de vos données personnelles.
                    Cette politique de confidentialité explique comment nous collectons, utilisons, stockons et protégeons
                    vos informations lorsque vous utilisez notre application mobile et notre site web.
                </p>

                <h2>2. Données collectées</h2>
                <p>Nous collectons les types de données suivants :</p>

                <h3>2.1 Données que vous nous fournissez</h3>
                <ul>
                    <li><strong>Informations de compte :</strong> nom, prénom, adresse email, mot de passe, date de naissance, pays, ville</li>
                    <li><strong>Données de profil :</strong> photo de profil, informations académiques</li>
                    <li><strong>Résultats du test de personnalité :</strong> réponses au questionnaire MBTI et résultats calculés</li>
                    <li><strong>Conversations avec le chatbot :</strong> messages échangés avec notre assistant IA</li>
                    <li><strong>Documents académiques :</strong> bulletins, relevés de notes, diplômes que vous choisissez de télécharger</li>
                </ul>

                <h3>2.2 Données collectées automatiquement</h3>
                <ul>
                    <li>Identifiants de l'appareil</li>
                    <li>Type d'appareil et système d'exploitation</li>
                    <li>Données d'utilisation de l'application</li>
                    <li>Adresse IP</li>
                </ul>

                <h2>3. Utilisation des données</h2>
                <p>Nous utilisons vos données pour :</p>
                <ul>
                    <li>Fournir et améliorer nos services d'orientation</li>
                    <li>Personnaliser votre expérience et les recommandations de l'IA</li>
                    <li>Vous permettre de passer le test de personnalité et consulter vos résultats</li>
                    <li>Stocker vos conversations avec le chatbot</li>
                    <li>Vous mettre en relation avec des profils de mentors pertinents</li>
                    <li>Vous envoyer des communications importantes sur le service</li>
                    <li>Analyser l'utilisation de l'application de manière agrégée</li>
                </ul>

                <h2>4. Partage des données</h2>
                <p>
                    <strong>Nous ne vendons jamais vos données personnelles.</strong>
                </p>
                <p>Nous pouvons partager vos données avec :</p>
                <ul>
                    <li><strong>Prestataires de services :</strong> hébergement (serveurs sécurisés), services d'IA (OpenRouter/DeepSeek pour le chatbot)</li>
                    <li><strong>Autorités légales :</strong> si requis par la loi</li>
                </ul>
                <p>
                    Les données envoyées au service d'IA pour le chatbot sont traitées de manière confidentielle et ne sont
                    pas utilisées pour entraîner des modèles tiers.
                </p>

                <h2>5. Stockage et sécurité</h2>
                <p>
                    Vos données sont stockées sur des serveurs sécurisés. Nous utilisons des mesures de sécurité
                    techniques et organisationnelles appropriées pour protéger vos données contre l'accès non autorisé,
                    la perte ou la destruction, notamment :
                </p>
                <ul>
                    <li>Chiffrement des données en transit (HTTPS/TLS)</li>
                    <li>Chiffrement des mots de passe (bcrypt)</li>
                    <li>Authentification par token sécurisé</li>
                    <li>Accès restreint aux données</li>
                </ul>

                <h2>6. Conservation des données</h2>
                <p>
                    Nous conservons vos données aussi longtemps que votre compte est actif ou que nécessaire pour
                    vous fournir nos services. Vous pouvez demander la suppression de votre compte et de vos données
                    à tout moment.
                </p>

                <h2>7. Vos droits</h2>
                <p>Conformément aux réglementations applicables, vous disposez des droits suivants :</p>
                <ul>
                    <li><strong>Droit d'accès :</strong> demander une copie de vos données personnelles</li>
                    <li><strong>Droit de rectification :</strong> corriger des données inexactes</li>
                    <li><strong>Droit à l'effacement :</strong> demander la suppression de vos données</li>
                    <li><strong>Droit à la portabilité :</strong> recevoir vos données dans un format structuré</li>
                    <li><strong>Droit d'opposition :</strong> vous opposer au traitement de vos données</li>
                </ul>
                <p>
                    Pour exercer ces droits, contactez-nous à <a href="mailto:privacy@brillio.africa">privacy@brillio.africa</a>.
                </p>

                <h2>8. Utilisation par les mineurs</h2>
                <p>
                    Brillio est destinée aux jeunes, y compris aux mineurs. Si vous avez moins de 18 ans, nous vous
                    encourageons à informer vos parents ou tuteurs de votre utilisation de l'application.
                    Les mineurs de moins de 13 ans ne peuvent pas créer de compte sans le consentement d'un parent.
                </p>

                <h2>9. Cookies et technologies similaires</h2>
                <p>
                    Notre site web utilise des cookies pour améliorer votre expérience. Vous pouvez gérer vos
                    préférences de cookies via les paramètres de votre navigateur.
                </p>

                <h2>10. Modifications de cette politique</h2>
                <p>
                    Nous pouvons mettre à jour cette politique de confidentialité de temps en temps. Nous vous
                    informerons de tout changement important par email ou via l'application.
                </p>

                <h2>11. Contact</h2>
                <p>
                    Pour toute question concernant cette politique de confidentialité ou vos données personnelles,
                    contactez-nous :
                </p>
                <ul>
                    <li>Email : <a href="mailto:privacy@brillio.africa">privacy@brillio.africa</a></li>
                    <li>Adresse : Dakar, Sénégal</li>
                </ul>
            </div>
        </div>
    </section>
@endsection
