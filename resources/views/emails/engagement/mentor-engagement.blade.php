@extends('emails.layouts.base')

@section('content')
<div style="font-family: 'Plus Jakarta Sans', Arial, sans-serif; color: #1e293b; max-width: 600px; margin: 0 auto;">
    <!-- En-tête -->
    <div style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); border-radius: 20px; padding: 40px 32px; text-align: center; margin-bottom: 28px; color: #ffffff;">
        <div style="width: 64px; height: 64px; margin: 0 auto 16px; background: rgba(255, 255, 255, 0.2); border-radius: 16px; display: inline-flex; align-items: center; justify-content: center; font-size: 32px; line-height: 64px;">
            🌍
        </div>
        <h1 style="color: #ffffff; margin: 0 0 10px; font-size: 26px; font-weight: 800; letter-spacing: -0.02em;">
            Transmettez, guidez et monétisez votre expertise
        </h1>
        <p style="color: #e0e7ff; margin: 0; font-size: 16px; font-weight: 500;">
            Bonjour {{ $user->name }}, découvrez tout ce que Brillio vous permet d'accomplir.
        </p>
    </div>

    <!-- Intro -->
    <p style="font-size: 16px; line-height: 1.6; color: #334155; margin-bottom: 24px;">
        Votre présence sur <strong>Brillio</strong> va bien au-delà d'un simple annuaire. Brillio est une plateforme complète conçue pour permettre aux mentors et professionnels de structurer leur accompagnement, d'élargir leur audience panafricaine et de valoriser leur savoir-faire.
    </p>

    <!-- Piliers de valeur -->
    <div style="margin-bottom: 32px;">

        <!-- 1. Séances & Mentorat payant -->
        <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; padding: 22px; margin-bottom: 16px;">
            <table role="presentation" width="100%">
                <tr>
                    <td style="width: 44px; vertical-align: top;">
                        <span style="font-size: 28px;">🎯</span>
                    </td>
                    <td>
                        <h3 style="margin: 0 0 6px; font-size: 16px; font-weight: 700; color: #1e293b;">
                            Monétisez votre savoir à l'unité
                        </h3>
                        <p style="margin: 0; font-size: 14px; line-height: 1.5; color: #475569;">
                            Proposez des séances d'accompagnement individuel ou de mentorat payant à l'heure. Vous fixez vos disponibilités et vous êtes directement rémunéré(e) pour votre temps et vos conseils stratégiques.
                        </p>
                    </td>
                </tr>
            </table>
        </div>

        <!-- 2. Vente de contenus documentaires -->
        <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; padding: 22px; margin-bottom: 16px;">
            <table role="presentation" width="100%">
                <tr>
                    <td style="width: 44px; vertical-align: top;">
                        <span style="font-size: 28px;">📚</span>
                    </td>
                    <td>
                        <h3 style="margin: 0 0 6px; font-size: 16px; font-weight: 700; color: #1e293b;">
                            Partagez et vendez vos contenus documentaires
                        </h3>
                        <p style="margin: 0; font-size: 14px; line-height: 1.5; color: #475569;">
                            E-books, modèles de CV, guides métiers, fiches pratiques ou brochures sectorielles : publiez vos ressources sur Brillio et générez des revenus complémentaires tout en éduquant votre communauté.
                        </p>
                    </td>
                </tr>
            </table>
        </div>

        <!-- 3. Communauté privée et suivi structuré -->
        <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; padding: 22px; margin-bottom: 16px;">
            <table role="presentation" width="100%">
                <tr>
                    <td style="width: 44px; vertical-align: top;">
                        <span style="font-size: 28px;">👥</span>
                    </td>
                    <td>
                        <h3 style="margin: 0 0 6px; font-size: 16px; font-weight: 700; color: #1e293b;">
                            Bâtissez votre communauté privée
                        </h3>
                        <p style="margin: 0; font-size: 14px; line-height: 1.5; color: #475569;">
                            Rassemblez les talents qui vous suivent dans un espace dédié. Échangez, suivez leur progression et fédérez votre propre cohorte de mentorés directement depuis la plateforme.
                        </p>
                    </td>
                </tr>
            </table>
        </div>

        <!-- 4. Impact panafricain -->
        <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; padding: 22px;">
            <table role="presentation" width="100%">
                <tr>
                    <td style="width: 44px; vertical-align: top;">
                        <span style="font-size: 28px;">🚀</span>
                    </td>
                    <td>
                        <h3 style="margin: 0 0 6px; font-size: 16px; font-weight: 700; color: #1e293b;">
                            Un impact direct sur les talents africains
                        </h3>
                        <p style="margin: 0; font-size: 14px; line-height: 1.5; color: #475569;">
                            Accompagnez des jeunes étudiants et diplômés partout en Afrique. Aidez-les à éviter les erreurs de début de carrière, à trouver les bonnes filières et à construire un parcours professionnel solide.
                        </p>
                    </td>
                </tr>
            </table>
        </div>

    </div>

    <!-- CTA -->
    <div style="text-align: center; margin: 36px 0;">
        <a href="{{ route('mentor.dashboard') }}"
           style="display: inline-block; background: #4f46e5; color: #ffffff !important; padding: 15px 36px; text-decoration: none; border-radius: 12px; font-weight: 700; font-size: 16px; box-shadow: 0 4px 14px rgba(79, 70, 229, 0.3);">
           🚀 Accéder à mon espace mentor
        </a>
    </div>

    <!-- Signature -->
    <p style="margin: 28px 0 0; font-size: 14px; line-height: 1.6; color: #64748b;">
        Merci d'inspirer la nouvelle génération,<br>
        <strong>L'équipe Brillio</strong>
    </p>
</div>
@endsection
