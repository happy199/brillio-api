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
                En accédant et en utilisant l'application Brillio et le site web brillio.africa
                (ci-après "le Service"), vous acceptez d'être lié par les présentes conditions
                d'utilisation. Si vous n'acceptez pas ces conditions, veuillez ne pas utiliser le Service.
            </p>

            <h2>2. Description du Service</h2>
            <p>
                Brillio est une plateforme d'orientation professionnelle destinée aux jeunes africains.
                Le Service comprend :
            </p>
            <ul>
                <li>Un test de personnalité basé sur le modèle MBTI</li>
                <li>Un chatbot d'orientation alimenté par l'intelligence artificielle</li>
                <li>Des profils de mentors africains avec leurs parcours professionnels</li>
                <li>Un espace de stockage de documents académiques</li>
                <li>Des ressources d'orientation adaptées au contexte africain</li>
            </ul>

            <h2>3. Inscription et compte</h2>
            <h3>3.1 Création de compte</h3>
            <p>
                Pour utiliser certaines fonctionnalités du Service, vous devez créer un compte.
                Vous vous engagez à :
            </p>
            <ul>
                <li>Fournir des informations exactes et à jour</li>
                <li>Maintenir la confidentialité de vos identifiants</li>
                <li>Nous informer immédiatement de toute utilisation non autorisée</li>
            </ul>

            <h3>3.2 Responsabilité du compte</h3>
            <p>
                Vous êtes responsable de toutes les activités effectuées sous votre compte.
                Brillio ne sera pas responsable des pertes résultant d'une utilisation non autorisée
                de votre compte.
            </p>

            <h2>4. Utilisation acceptable</h2>
            <p>En utilisant le Service, vous vous engagez à ne pas :</p>
            <ul>
                <li>Violer les lois applicables</li>
                <li>Usurper l'identité d'une autre personne</li>
                <li>Télécharger des contenus illégaux, offensants ou nuisibles</li>
                <li>Tenter de contourner les mesures de sécurité</li>
                <li>Utiliser le Service à des fins commerciales non autorisées</li>
                <li>Harceler d'autres utilisateurs ou membres de l'équipe</li>
                <li>Diffuser des virus ou codes malveillants</li>
                <li>Collecter des données d'autres utilisateurs sans autorisation</li>
            </ul>

            <h2>5. Contenu utilisateur</h2>
            <h3>5.1 Propriété</h3>
            <p>
                Vous conservez tous les droits sur le contenu que vous publiez sur le Service
                (documents, messages, etc.). En publiant du contenu, vous nous accordez une licence
                non exclusive pour stocker et afficher ce contenu dans le cadre du Service.
            </p>

            <h3>5.2 Responsabilité</h3>
            <p>
                Vous êtes seul responsable du contenu que vous publiez. Ne téléchargez pas de
                documents contenant des informations confidentielles de tiers ou des contenus
                protégés par des droits d'auteur sans autorisation.
            </p>

            <h2>6. Propriété intellectuelle</h2>
            <p>
                Le Service, y compris son design, ses fonctionnalités, ses textes, images et code,
                est la propriété de Brillio et est protégé par les lois sur la propriété intellectuelle.
                Vous n'êtes pas autorisé à copier, modifier, distribuer ou créer des œuvres dérivées
                sans notre autorisation écrite.
            </p>

            <h2>7. Limitations du Service</h2>
            <h3>7.1 Conseils d'orientation</h3>
            <p>
                Les conseils fournis par le chatbot IA et les informations sur les métiers sont
                donnés à titre indicatif. Ils ne remplacent pas les conseils d'un professionnel
                de l'orientation qualifié. Les décisions d'orientation restent de votre responsabilité.
            </p>

            <h3>7.2 Test de personnalité</h3>
            <p>
                Le test MBTI est un outil d'auto-connaissance. Les résultats sont indicatifs et
                ne constituent pas un diagnostic psychologique. Ils ne doivent pas être utilisés
                comme seul critère pour des décisions importantes.
            </p>

            <h3>7.3 Profils de mentors</h3>
            <p>
                Les parcours des mentors sont partagés à titre d'inspiration. Brillio ne garantit
                pas l'exactitude des informations fournies par les mentors et n'est pas responsable
                des interactions entre utilisateurs et mentors.
            </p>

            <h2>8. Disponibilité du Service</h2>
            <p>
                Nous nous efforçons de maintenir le Service disponible 24h/24, mais nous ne
                garantissons pas une disponibilité ininterrompue. Le Service peut être
                temporairement indisponible pour maintenance ou en cas de force majeure.
            </p>

            <h2>9. Modification et résiliation</h2>
            <h3>9.1 Modifications</h3>
            <p>
                Nous pouvons modifier ces conditions à tout moment. Les modifications entreront
                en vigueur dès leur publication. Votre utilisation continue du Service après
                modification constitue une acceptation des nouvelles conditions.
            </p>

            <h3>9.2 Résiliation</h3>
            <p>
                Vous pouvez supprimer votre compte à tout moment. Nous nous réservons le droit
                de suspendre ou supprimer votre compte en cas de violation des présentes conditions.
            </p>

            <h2>10. Limitation de responsabilité</h2>
            <p>
                Dans les limites autorisées par la loi, Brillio ne sera pas responsable des
                dommages indirects, accessoires, spéciaux ou consécutifs résultant de votre
                utilisation ou de votre incapacité à utiliser le Service.
            </p>
            <p>
                Le Service est fourni "tel quel" sans garantie d'aucune sorte, expresse ou implicite.
            </p>

            <h2>11. Indemnisation</h2>
            <p>
                Vous acceptez d'indemniser et de dégager Brillio de toute responsabilité en cas
                de réclamation, dommage ou dépense (y compris les frais juridiques) résultant de
                votre violation des présentes conditions ou de votre utilisation du Service.
            </p>

            <h2>12. Droit applicable</h2>
            <p>
                Les présentes conditions sont régies par le droit béninois. Tout litige sera
                soumis à la compétence exclusive des tribunaux de Cotonou, Bénin.
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
                <li>Email : <a href="mailto:legal@brillio.africa">legal@brillio.africa</a></li>
                <li>Adresse : Cotonou, Bénin</li>
            </ul>
        </div>
    </div>
</section>
@endsection