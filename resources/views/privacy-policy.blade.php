@extends('layouts.jeune')

@section('title', 'Politique de Confidentialité')

@section('content')
    <div class="max-w-4xl mx-auto space-y-8">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Politique de Confidentialité</h1>
            <p class="text-gray-600">Dernière mise à jour : {{ date('d/m/Y') }}</p>
        </div>

        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-6">
            <p class="text-sm text-yellow-800">
                <strong>Important :</strong> L'utilisation de notre plateforme implique l'acceptation de cette politique.
                La non-acceptation explicite est considérée comme une acceptation implicite des termes décrits ci-dessous.
            </p>
        </div>

        <div class="bg-white rounded-2xl p-8 shadow-sm space-y-8">
            <section>
                <h2 class="text-2xl font-bold text-gray-900 mb-4">1. Introduction</h2>
                <p class="text-gray-700 leading-relaxed">
                    Brillio s'engage à protéger la vie privée de ses utilisateurs. Cette politique explique comment nous
                    collectons,
                    utilisons et protégeons vos données personnelles conformément au Règlement Général sur la Protection des
                    Données (RGPD).
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-bold text-gray-900 mb-4">2. Données Collectées</h2>
                <div class="space-y-3 text-gray-700">
                    <p><strong>Données d'identification :</strong></p>
                    <ul class="list-disc list-inside ml-4 space-y-1">
                        <li>Nom et prénom</li>
                        <li>Adresse email</li>
                        <li>Date de naissance</li>
                        <li>Pays et ville de résidence</li>
                    </ul>

                    <p class="mt-4"><strong>Données de profil :</strong></p>
                    <ul class="list-disc list-inside ml-4 space-y-1">
                        <li>Résultats des tests de personnalité (MBTI)</li>
                        <li>Documents académiques uploadés</li>
                        <li>Historique des conversations avec l'IA</li>
                        <li>Préférences d'orientation professionnelle</li>
                    </ul>

                    <p class="mt-4"><strong>Données techniques :</strong></p>
                    <ul class="list-disc list-inside ml-4 space-y-1">
                        <li>Adresse IP</li>
                        <li>Type de navigateur</li>
                        <li>Cookies de session</li>
                        <li>Logs de connexion</li>
                    </ul>
                </div>
            </section>

            <section>
                <h2 class="text-2xl font-bold text-gray-900 mb-4">3. Utilisation des Données</h2>
                <p class="text-gray-700 mb-3">Nous utilisons vos données pour :</p>
                <ul class="list-disc list-inside ml-4 space-y-2 text-gray-700">
                    <li>Fournir nos services d'orientation professionnelle</li>
                    <li>Personnaliser votre expérience utilisateur</li>
                    <li>Améliorer nos algorithmes de recommandation</li>
                    <li>Communiquer avec vous concernant votre compte</li>
                    <li>Assurer la sécurité de la plateforme</li>
                    <li>Respecter nos obligations légales</li>
                </ul>
            </section>

            <section>
                <h2 class="text-2xl font-bold text-gray-900 mb-4">4. Cookies</h2>
                <p class="text-gray-700 mb-3">Nous utilisons des cookies pour :</p>
                <ul class="list-disc list-inside ml-4 space-y-2 text-gray-700">
                    <li><strong>Cookies essentiels :</strong> Nécessaires au fonctionnement du site (authentification,
                        session)</li>
                    <li><strong>Cookies de performance :</strong> Analyse de l'utilisation pour améliorer nos services</li>
                    <li><strong>Cookies de préférences :</strong> Mémorisation de vos choix et paramètres</li>
                </ul>
                <p class="text-gray-700 mt-3">
                    Vous pouvez gérer vos préférences de cookies dans les paramètres de votre navigateur.
                    Notez que la désactivation de certains cookies peut affecter le fonctionnement du site.
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-bold text-gray-900 mb-4">5. Partage des Données</h2>
                <p class="text-gray-700 mb-3">Nous ne vendons jamais vos données personnelles. Nous pouvons partager vos
                    données avec :</p>
                <ul class="list-disc list-inside ml-4 space-y-2 text-gray-700">
                    <li><strong>Fournisseurs de services :</strong> OpenAI/DeepSeek pour l'IA, services d'hébergement</li>
                    <li><strong>Mentors :</strong> Uniquement les informations nécessaires pour le mentorat (avec votre
                        consentement)</li>
                    <li><strong>Autorités légales :</strong> Si requis par la loi</li>
                </ul>
            </section>

            <section>
                <h2 class="text-2xl font-bold text-gray-900 mb-4">6. Vos Droits (RGPD)</h2>
                <p class="text-gray-700 mb-3">Conformément au RGPD, vous disposez des droits suivants :</p>
                <ul class="list-disc list-inside ml-4 space-y-2 text-gray-700">
                    <li><strong>Droit d'accès :</strong> Obtenir une copie de vos données</li>
                    <li><strong>Droit de rectification :</strong> Corriger vos données inexactes</li>
                    <li><strong>Droit à l'effacement :</strong> Supprimer vos données ("droit à l'oubli")</li>
                    <li><strong>Droit à la portabilité :</strong> Recevoir vos données dans un format structuré</li>
                    <li><strong>Droit d'opposition :</strong> Vous opposer au traitement de vos données</li>
                    <li><strong>Droit de limitation :</strong> Limiter le traitement de vos données</li>
                </ul>
                <p class="text-gray-700 mt-3">
                    Pour exercer ces droits, contactez-nous à : <a href="mailto:contact@brillio.africa"
                        class="text-primary-600 hover:underline">contact@brillio.africa</a>
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-bold text-gray-900 mb-4">7. Sécurité des Données</h2>
                <p class="text-gray-700">
                    Nous mettons en œuvre des mesures de sécurité techniques et organisationnelles appropriées pour protéger
                    vos données contre
                    tout accès non autorisé, perte, destruction ou altération. Cela inclut le chiffrement des données
                    sensibles,
                    l'authentification sécurisée, et des audits de sécurité réguliers.
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-bold text-gray-900 mb-4">8. Conservation des Données</h2>
                <p class="text-gray-700">
                    Nous conservons vos données personnelles aussi longtemps que nécessaire pour fournir nos services et
                    respecter nos obligations légales.
                    Les données des comptes inactifs depuis plus de 3 ans peuvent être supprimées après notification.
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-bold text-gray-900 mb-4">9. Transferts Internationaux</h2>
                <p class="text-gray-700">
                    Vos données peuvent être transférées et traitées dans des pays hors de l'Union Européenne.
                    Dans ce cas, nous nous assurons que des garanties appropriées sont en place conformément au RGPD.
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-bold text-gray-900 mb-4">10. Modifications de la Politique</h2>
                <p class="text-gray-700">
                    Nous pouvons modifier cette politique de confidentialité à tout moment. Les modifications importantes
                    seront notifiées
                    par email ou via une notification sur la plateforme. La date de dernière mise à jour est indiquée en
                    haut de cette page.
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-bold text-gray-900 mb-4">11. Contact</h2>
                <p class="text-gray-700">
                    Pour toute question concernant cette politique ou vos données personnelles, contactez notre Délégué à la
                    Protection des Données :
                </p>
                <div class="mt-3 p-4 bg-gray-50 rounded-xl">
                    <p class="text-gray-700"><strong>Email :</strong> <a href="mailto:contact@brillio.africa"
                            class="text-primary-600 hover:underline">contact@brillio.africa</a></p>
                    <p class="text-gray-700 mt-1"><strong>Adresse :</strong> Brillio, Cotonou, Bénin</p>
                </div>
            </section>
        </div>

        <div class="text-center text-sm text-gray-500 pb-8">
            <p>© {{ date('Y') }} Brillio. Tous droits réservés.</p>
        </div>
    </div>
@endsection