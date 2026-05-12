@extends('layouts.public')

{{-- SEO Meta Tags --}}
<x-seo-meta page="terms" />

@section('content')
    <!-- Hero Section -->
    <section class="gradient-hero pt-32 pb-16 relative">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="max-w-3xl mx-auto text-center text-white">
                <h1 class="text-4xl font-bold mb-4">Conditions d'utilisation</h1>
                <p class="text-white/80">Dernière mise à jour : {{ date('d/m/Y') }}</p>
            </div>
        </div>
    </section>

    <!-- Content -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto prose prose-lg">
                <!-- TODO: Personnaliser les CGU avec un avocat -->

                <h2>1. Acceptation des conditions</h2>
                <p>
                    En accédant et en utilisant la plateforme Brillio (site web brillio.africa et applications associées), vous acceptez sans réserve les présentes Conditions Générales d'Utilisation (CGU). Ces conditions sont régies par la <strong>Loi n° 2017-20 du 20 avril 2018 portant Code du numérique en République du Bénin</strong>.
                </p>
                <p>Si vous n'acceptez pas ces conditions, veuillez cesser toute utilisation du Service.</p>

                <h2>2. Description du Service</h2>
                <p>
                    Brillio est une plateforme numérique d'orientation professionnelle. Elle propose des services automatisés de test de personnalité (MBTI), d'assistance par intelligence artificielle, de mise en relation avec des mentors et de stockage sécurisé de documents académiques.
                </p>

                <h2>3. Inscription et Consentement Électronique</h2>
                <p>
                    La création d'un compte nécessite la fourniture d'informations exactes. Conformément aux dispositions sur les transactions électroniques du Code du Numérique, la validation du formulaire d'inscription avec la case à cocher de consentement constitue une <strong>signature électronique</strong> ayant valeur d'engagement contractuel.
                </p>

                <h2>4. Responsabilité du Prestataire de Services Numériques</h2>
                <p>
                    Conformément aux Articles 560 et suivants du Code du Numérique sur la responsabilité des prestataires :
                </p>
                <ul>
                    <li><strong>Contenu IA :</strong> Les conseils du chatbot sont générés de manière automatisée. Brillio ne saurait être tenu responsable des décisions d'orientation prises sur cette seule base.</li>
                    <li><strong>Contenu Utilisateur :</strong> Brillio agit en tant qu'hébergeur pour vos documents académiques. Nous n'exerçons pas de contrôle a priori sur la véracité des documents téléchargés par les utilisateurs.</li>
                    <li><strong>Mentorat :</strong> Brillio facilite la mise en relation mais n'intervient pas dans les échanges privés entre mentors et jeunes.</li>
                </ul>

                <h2>5. Propriété Intellectuelle</h2>
                <p>
                    L'architecture, le code source, le design et les contenus originaux de Brillio sont protégés par le droit d'auteur et les dispositions du Code du Numérique sur la protection des programmes informatiques. Toute reproduction sans autorisation est passible de sanctions pénales.
                </p>

                <h2>6. Droit Applicable et Litiges</h2>
                <p>
                    Les présentes conditions sont exclusivement régies par le <strong>droit béninois</strong>. En cas de litige relatif à l'interprétation ou à l'exécution des présentes, et à défaut de résolution amiable, les tribunaux de <strong>Cotonou</strong> seront seuls compétents.
                </p>

                <h2>13. Dispositions générales</h2>
                <ul>
                    <li>Si une disposition est jugée invalide, les autres restent en vigueur</li>
                    <li>Notre non-exercice d'un droit ne constitue pas une renonciation à ce droit</li>
                    <li>Ces conditions constituent l'intégralité de l'accord entre vous et Brillio</li>
                </ul>

                <h2>14. Contact</h2>
                <p>
                    Pour toute question concernant ces conditions d'utilisation, contactez-nous :
                </p>
                <ul>
                    <li>Email : <a href="mailto:contact@brillio.africa">contact@brillio.africa</a></li>
                    <li>Adresse : Cotonou, Bénin</li>
                </ul>
            </div>
        </div>
    </section>
@endsection