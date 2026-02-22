<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Mentor Vérifié</title>
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #1f2937;
            background-color: #f3f4f6;
            margin: 0;
            padding: 0;
        }

        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f3f4f6;
            padding: 40px 0;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        }

        .header {
            background-color: #7c3aed;
            background-image: linear-gradient(135deg, #7c3aed 0%, #a855f7 100%);
            padding: 40px 20px;
            text-align: center;
            color: #ffffff;
        }

        .badge-icon {
            width: 80px;
            height: 80px;
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 40px;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .badge-icon svg {
            width: 48px;
            height: 48px;
            color: #ffffff;
        }

        .content {
            padding: 40px;
        }

        h1 {
            font-size: 28px;
            font-weight: 800;
            margin: 0 0 10px;
            color: #ffffff;
        }

        h2 {
            font-size: 20px;
            font-weight: 700;
            color: #4c1d95;
            margin-top: 30px;
        }

        p {
            margin-bottom: 20px;
            font-size: 16px;
        }

        .benefit-card {
            background-color: #f5f3ff;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 15px;
            border-left: 4px solid #7c3aed;
        }

        .benefit-card h3 {
            margin: 0 0 5px;
            font-size: 16px;
            color: #5b21b6;
        }

        .benefit-card p {
            margin: 0;
            font-size: 14px;
            color: #6d28d9;
        }

        .cta-container {
            text-align: center;
            margin: 40px 0 20px;
        }

        .btn {
            display: inline-block;
            background-color: #7c3aed;
            color: #ffffff !important;
            padding: 16px 32px;
            border-radius: 12px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px -1px rgba(124, 58, 237, 0.3);
        }

        .footer {
            text-align: center;
            padding: 20px;
            font-size: 14px;
            color: #6b7280;
            background-color: #f9fafb;
        }

        .social-invite {
            background-color: #eff6ff;
            padding: 15px;
            border-radius: 12px;
            margin-top: 20px;
            font-size: 14px;
            color: #1e40af;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <div class="badge-icon">
                    <img src="{{ config('app.url') }}/favicon-32x32.png" alt="Brillio"
                        style="width: 48px; height: 48px; border-radius: 50%;">
                </div>
                <h1>FÉLICITATIONS !</h1>
                <p style="opacity: 0.9; margin: 0;">Votre excellence est désormais reconnue</p>
            </div>

            <div class="content">
                <p>Bonjour <strong>{{ $mentor->user->name }}</strong>,</p>

                <p>C'est un grand plaisir pour l'équipe <strong>Brillio</strong> de vous annoncer que votre profil a été
                    officiellement <strong>vérifié</strong> ! 🎉</p>

                <p>Après une analyse attentive de votre compte, notre équipe a été impressionnée par le remplissage
                    méthodique et la qualité des informations que vous partagez. En récompense de votre
                    professionnalisme, nous vous accordons le <strong>badge de vérification</strong>.</p>

                <h2>Qu'est-ce que cela change pour vous ?</h2>

                <div class="benefit-card">
                    <h3>✨ Visibilité Maximale</h3>
                    <p>Votre badge est désormais visible par tous les jeunes. Un profil vérifié inspire immédiatement
                        confiance et facilite la création de relations de mentorat.</p>
                </div>

                <div class="benefit-card">
                    <h3>🚀 Mise en avant de vos ressources</h3>
                    <p>Chaque article, guide, exercice ou document que vous publiez sera désormais mis en avant
                        prioritairement sur la plateforme.</p>
                </div>

                <div class="benefit-card">
                    <h3>💎 Prestige & Crédibilité</h3>
                    <p>Vous faites désormais partie de l'élite des mentors Brillio, un gage de qualité pour votre
                        parcours professionnel.</p>
                </div>

                <p style="margin-top: 30px;">Pour conserver cet avantage, nous vous encourageons à maintenir votre
                    profil à jour régulièrement. Vous pouvez ajouter manuellement vos nouvelles étapes marquantes ou
                    simplement <strong>importer votre profil LinkedIn</strong> pour que notre système le complète
                    automatiquement.</p>

                <div class="cta-container">
                    <a href="{{ url('/mentor/profile') }}" class="btn">Accéder à mon tableau de bord</a>
                </div>

                <div class="social-invite">
                    <strong>💡 Astuce :</strong> Un profil complet à 100% multiplie par 3 vos chances d'être contacté
                    par des jeunes talentueux en quête de conseils !
                </div>
            </div>

            <div class="footer">
                <p>© {{ date('Y') }} Brillio Platform. Développez votre impact.</p>
                <p style="font-size: 12px;">Vous recevez cet email car votre compte mentor a été validé par un
                    administrateur.</p>
            </div>
        </div>
    </div>
</body>

</html>