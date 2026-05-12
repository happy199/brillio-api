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
                    Bienvenue sur Brillio. Nous accordons une grande importance à la protection de vos données personnelles.
                    Cette politique de confidentialité est établie conformément à la <strong>Loi n° 2017-20 du 20 avril 2018
                        portant Code du numérique en République du Bénin</strong> (Livre Cinquième) et aux standards de la
                    <strong>CEDEAO</strong>.
                </p>
                <p>
                    Elle explique comment nous collectons, utilisons, stockons et protégeons vos informations lorsque vous
                    utilisez notre application mobile et notre site web.
                </p>

                <h2>2. Données collectées</h2>
                <p>Nous collectons les types de données suivants :</p>

                <h3>2.1 Données que vous nous fournissez</h3>
                <ul>
                    <li><strong>Informations de compte :</strong> nom, prénom, adresse email, mot de passe, date de
                        naissance, pays, ville.</li>
                    <li><strong>Données de profil :</strong> photo de profil, informations académiques, centres d'intérêt.
                    </li>
                    <li><strong>Données Sensibles (Psychotechniques) :</strong> vos réponses au questionnaire MBTI et les
                        profils psychologiques qui en découlent. <em>Conformément à l'Article 402 du Code du Numérique, ces
                            données font l'objet d'une protection renforcée.</em></li>
                    <li><strong>Conversations avec le chatbot :</strong> messages échangés avec notre assistant IA
                        d'orientation.</li>
                    <li><strong>Documents académiques :</strong> bulletins, relevés de notes, diplômes que vous choisissez
                        de télécharger pour votre dossier d'orientation.</li>
                </ul>

                <h3>2.2 Données collectées automatiquement</h3>
                <ul>
                    <li>Identifiants de l'appareil, type d'appareil et système d'exploitation.</li>
                    <li>Données d'utilisation de l'application et adresse IP.</li>
                </ul>

                <h2>3. Utilisation des données (Finalités)</h2>
                <p>Nous utilisons vos données pour des finalités spécifiques et légitimes :</p>
                <ul>
                    <li>Fournir et améliorer nos services d'orientation et personnaliser votre expérience.</li>
                    <li>Vous permettre de passer le test de personnalité et consulter vos résultats.</li>
                    <li>Stocker vos conversations avec le chatbot pour assurer la continuité du conseil.</li>
                    <li>Vous mettre en relation avec des profils de mentors pertinents basés sur votre profil.</li>
                    <li>Analyser l'utilisation de l'application de manière agrégée pour optimiser nos services.</li>
                    <li>Sécurisation de votre espace personnel et communication relative au service.</li>
                </ul>

                <h2>4. Partage et Transfert des données</h2>
                <p>
                    <strong>Nous ne commercialisons jamais vos données personnelles.</strong>
                </p>
                <p>Nous pouvons partager vos données avec :</p>
                <ul>
                    <li><strong>Prestataires de services :</strong> hébergement (serveurs sécurisés), services d'IA.</li>
                    <li><strong>Organisations partenaires :</strong> Si vous êtes lié à une organisation (école,
                        association) via un code d'invitation spécifique ou si cette organisation est cliente de nos
                        services.</li>
                    <li><strong>Autorités légales :</strong> uniquement si requis par la loi.</li>
                </ul>
                <p>
                    <strong>Transfert international :</strong> Nos serveurs peuvent être situés hors du territoire béninois.
                    En utilisant Brillio, vous consentez au transfert de vos données vers ces serveurs, pour lesquels nous
                    garantissons un niveau de sécurité conforme aux standards internationaux.
                </p>

                <h2>5. Stockage et sécurité</h2>
                <p>
                    Vos données sont stockées sur des serveurs sécurisés. Nous utilisons des mesures de sécurité
                    techniques et organisationnelles appropriées (chiffrement TLS, hachage bcrypt, accès restreints) pour
                    protéger vos données contre l'accès non autorisé,
                    la perte ou la destruction.
                </p>

                <h2>6. Conservation des données</h2>
                <p>
                    Nous conservons vos données aussi longtemps que votre compte est actif ou que nécessaire pour
                    vous fournir nos services. Vous pouvez demander la suppression de votre compte et de vos données
                    à tout moment.
                </p>

                <h2>7. Vos droits et Recours</h2>
                <p>Conformément au Code du Numérique, vous disposez des droits suivants :</p>
                <ul>
                    <li><strong>Droit d'accès et de rectification :</strong> demander une copie de vos données et corriger
                        les erreurs.</li>
                    <li><strong>Droit à l'effacement (Oubli) :</strong> demander la suppression de vos données (disponible
                        en 2 clics depuis votre profil).</li>
                    <li><strong>Droit à la portabilité :</strong> recevoir vos données dans un format structuré (disponible
                        en 2 clics depuis votre profil).</li>
                    <li><strong>Droit d'opposition :</strong> vous opposer à certains traitements de vos données.</li>
                </ul>
                <p>
                    Pour toute réclamation, vous avez le droit de saisir l'<strong>Autorité de Protection des Données à
                        Caractère Personnel (APDP)</strong> du Bénin (<a href="https://www.apdp.bj"
                        target="_blank">www.apdp.bj</a>).
                </p>

                <h2>8. Utilisation par les mineurs</h2>
                <p>
                    Brillio est destinée aux jeunes, y compris aux mineurs. Si vous avez moins de 18 ans, nous vous
                    encourageons à informer vos parents ou tuteurs. Les mineurs de moins de 10 ans ne peuvent pas créer de
                    compte sans le consentement et l'accompagnement d'un parent.
                </p>

                <h2>9. Cookies et technologies similaires</h2>
                <p>
                    Notre site web utilise des cookies pour améliorer votre expérience. Vous pouvez gérer vos
                    préférences via les paramètres de votre navigateur.
                </p>

                <h2>10. Modifications de cette politique</h2>
                <p>
                    Nous pouvons être amenés à modifier cette politique de temps en temps. Nous vous
                    informerons de tout changement important par email ou via l'application.
                </p>

                <h2>11. Contact</h2>
                <p>
                    Pour toute question ou pour exercer vos droits, contactez notre responsable de la protection des données
                    :
                </p>
                <ul>
                    <li>Email : <a href="mailto:contact@brillio.africa">contact@brillio.africa</a></li>
                    <li>Adresse : Cotonou, République du Bénin</li>
                </ul>
            </div>
        </div>
    </section>
@endsection