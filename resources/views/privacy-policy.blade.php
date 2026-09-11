@extends('layouts.public')

{{-- SEO Meta Tags --}}
<x-seo-meta page="privacy" />

@section('title', 'Politique de Confidentialité')

@section('content')
    <!-- Hero Section (Aligné avec Conditions d'utilisation) -->
    <section class="gradient-hero pt-32 pb-16 relative">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="max-w-3xl mx-auto text-center text-white">
                <h1 class="text-4xl font-bold mb-4">Politique de Confidentialité</h1>
                <p class="text-white/80">Dernière mise à jour : {{ date('d/m/Y') }}</p>
            </div>
        </div>
    </section>

    <!-- Content -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto">
                <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 sm:p-5 mb-8">
                    <p class="text-sm text-amber-900 leading-relaxed m-0">
                        <strong class="font-bold text-amber-950">Important :</strong> L'utilisation de notre plateforme implique l'acceptation de cette politique.
                        La non-acceptation explicite est considérée comme une acceptation implicite des termes décrits ci-dessous.
                    </p>
                </div>

                <div class="prose prose-lg max-w-none text-gray-700">
                    <h2>1. Introduction</h2>
                    <p>
                        Brillio s'engage à protéger la vie privée de ses utilisateurs. Cette politique explique comment nous collectons,
                        utilisons et protégeons vos données personnelles conformément à la <strong>Loi n° 2017-20 du 20 avril 2018 portant Code du numérique en République du Bénin</strong> (Livre Cinquième), aux standards de la <strong>CEDEAO</strong> et au Règlement Général sur la Protection des Données (RGPD).
                    </p>

                    <h2>2. Données Collectées</h2>
                    <p>Nous collectons les types de données suivants :</p>
                    
                    <h3>2.1 Données d'identification</h3>
                    <ul>
                        <li>Nom et prénom</li>
                        <li>Adresse email</li>
                        <li>Date de naissance</li>
                        <li>Pays et ville de résidence</li>
                    </ul>

                    <h3>2.2 Données de profil & professionnelles</h3>
                    <ul>
                        <li>Résultats des tests de personnalité (MBTI)</li>
                        <li>Documents académiques et Curriculum Vitae (CV) déposés</li>
                        <li>Données extraites des CV (coordonnées de contact, compétences techniques, parcours professionnel, projets, diplômes)</li>
                        <li>Historique des échanges avec notre assistant IA d'orientation</li>
                        <li>Préférences d'insertion professionnelle</li>
                    </ul>

                    <h3>2.3 Données techniques</h3>
                    <ul>
                        <li>Adresse IP et données d'utilisation</li>
                        <li>Type de navigateur et système d'exploitation</li>
                        <li>Cookies de session et logs de connexion</li>
                    </ul>

                    <h2>3. Utilisation des Données (Finalités)</h2>
                    <p>Nous utilisons vos données pour :</p>
                    <ul>
                        <li>Fournir nos services d'orientation et d'insertion professionnelle</li>
                        <li>Évaluer, noter et optimiser les CV selon les critères ATS des recruteurs et générer des versions restructurées prêtes à l'emploi</li>
                        <li>Permettre à l'équipe commerciale et de placement de Brillio de vous contacter directement afin de vous proposer des opportunités concrètes d'emploi, de stage ou d'ateliers adaptés à vos compétences</li>
                        <li>Personnaliser votre expérience utilisateur et améliorer nos algorithmes d'orientation</li>
                        <li>Communiquer avec vous concernant votre compte et vos démarches</li>
                        <li>Assurer la sécurité de la plateforme et respecter nos obligations légales</li>
                    </ul>

                    <h2>4. Cookies</h2>
                    <p>Nous utilisons des cookies essentiels, de performance et de préférences pour garantir la sécurité et l'ergonomie de nos services. Vous pouvez gérer vos préférences de cookies dans les paramètres de votre navigateur.</p>

                    <h2>5. Partage des Données</h2>
                    <p><strong>Nous ne vendons jamais vos données personnelles.</strong> Nous pouvons partager vos données uniquement avec nos prestataires techniques d'hébergement sécurisé, nos modèles d'IA, les mentors habilités (avec votre accord pour le suivi) et les autorités légales si la loi l'exige.</p>

                    <h2>6. Vos Droits et Recours</h2>
                    <p>Conformément au Code du Numérique et au RGPD, vous disposez des droits d'accès, de rectification, d'effacement (« droit à l'oubli »), de portabilité, d'opposition et de limitation du traitement de vos données.</p>
                    <p>
                        Pour exercer ces droits, vous pouvez nous écrire à : <a href="mailto:contact@brillio.africa" class="text-primary-600 hover:underline">contact@brillio.africa</a>.
                        Vous avez également le droit de saisir l'<strong>Autorité de Protection des Données à Caractère Personnel (APDP)</strong> du Bénin (<a href="https://www.apdp.bj" target="_blank" rel="noopener">www.apdp.bj</a>).
                    </p>

                    <h2>7. Sécurité des Données</h2>
                    <p>
                        Nous mettons en œuvre des mesures de sécurité techniques et organisationnelles appropriées (chiffrement TLS, protocoles d'accès stricts, sauvegardes sécurisées) pour protéger vos données contre tout accès non autorisé, perte, altération ou destruction.
                    </p>

                    <h2>8. Conservation des Données</h2>
                    <p>
                        Nous conservons vos données aussi longtemps que votre compte est actif ou que nécessaire pour vous fournir nos services et respecter nos obligations légales.
                    </p>

                    <h2>9. Transferts Internationaux</h2>
                    <p>
                        Vos données peuvent être hébergées sur des serveurs sécurisés situés hors du territoire béninois, pour lesquels nous garantissons un niveau de protection rigoureusement conforme aux standards internationaux.
                    </p>

                    <h2>10. Modifications de la Politique</h2>
                    <p>
                        Nous pouvons modifier cette politique de confidentialité à tout moment. Les modifications importantes vous seront notifiées par email ou via la plateforme. La date de dernière mise à jour est toujours indiquée en haut de cette page.
                    </p>

                    <h2>11. Contact</h2>
                    <p>
                        Pour toute question concernant cette politique ou l'exercice de vos droits :
                    </p>
                    <ul>
                        <li>Email : <a href="mailto:contact@brillio.africa" class="text-primary-600 hover:underline">contact@brillio.africa</a></li>
                        <li>Adresse : Brillio, Cotonou, République du Bénin</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
@endsection