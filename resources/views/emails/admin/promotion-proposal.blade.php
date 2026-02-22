<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #374151;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9fafb;
        }

        .container {
            background-color: #ffffff;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo {
            color: #10b981;
            font-size: 32px;
            font-weight: 700;
            margin: 0;
            text-decoration: none;
        }

        .hero {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            text-align: center;
        }

        .hero h2 {
            margin: 0;
            font-size: 24px;
        }

        .content p {
            margin: 20px 0;
        }

        .cta-container {
            text-align: center;
            margin: 35px 0;
        }

        .cta-button {
            display: inline-block;
            padding: 16px 32px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white !important;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 700;
            font-size: 16px;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
            transition: transform 0.2s ease;
        }

        .info-box {
            background-color: #f0fdf4;
            border-left: 4px solid #10b981;
            padding: 20px;
            margin: 25px 0;
            border-radius: 4px;
            font-size: 14px;
            color: #065f46;
        }

        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 12px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1 class="logo">Brillio</h1>
        </div>

        <div class="hero">
            <h2>Félicitations, {{ $user->name }} !</h2>
        </div>

        <div class="content">
            <p>Bonjour,</p>

            <p>Votre parcours et votre expertise professionnelle ont retenu toute notre attention. Chez
                <strong>Brillio</strong>, nous recherchons des profils capables d'inspirer et de guider les talents de
                demain.</p>

            <p>Au vu de la solidité de votre expérience, nous avons le plaisir de vous proposer de passer du statut de
                Jeune à celui de <strong>Mentor</strong> sur notre plateforme.</p>

            <p>Devenir Mentor sur Brillio, c'est rejoindre une communauté d'élite, partager votre savoir-faire et
                contribuer activement au développement de l'écosystème professionnel africain.</p>

            <div class="cta-container">
                <a href="{{ $acceptUrl }}" class="cta-button">
                    🚀 Accéder à mon nouveau statut Mentor
                </a>
            </div>

            <div class="info-box">
                <strong>📝 Note importante :</strong> En acceptant cette promotion, votre compte Jeune actuel sera
                archivé. Vous serez ensuite invité à vous reconnecter via LinkedIn pour activer votre profil Mentor et
                certifier vos compétences.
            </div>
        </div>

        <div class="footer">
            <p>© {{ date('Y') }} Brillio Platform. Tous droits réservés.</p>
            <p>Vous recevez cet email suite à une proposition administrative de la part de l'équipe Brillio.</p>
        </div>
    </div>
</body>

</html>