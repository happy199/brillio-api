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
            background: #f3f4f6;
            color: #374151;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            text-align: center;
            border: 1px solid #e5e7eb;
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
        }

        .info-box {
            background-color: #fef2f2;
            border-left: 4px solid #ef4444;
            padding: 20px;
            margin: 25px 0;
            border-radius: 4px;
            font-size: 14px;
            color: #991b1b;
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
            <h2>Mise à jour de votre compte</h2>
        </div>

        <div class="content">
            <p>Bonjour {{ $user->name }},</p>

            <p>Nous vous informons d'un changement concernant votre statut sur la plateforme <strong>Brillio</strong>.
                Votre compte a été repositionné au statut <strong>Jeune</strong> par l'administration.</p>

            <p>Ce changement peut être lié à une mise à jour de nos critères d'accompagnement ou à une réorganisation
                stratégique. Vous conservez bien entendu l'accès à l'ensemble des ressources et parcours disponibles
                pour les Jeunes.</p>

            <p>Pour continuer votre aventure sur Brillio, vous pouvez vous connecter directement via le portail Jeune :
            </p>

            <div class="cta-container">
                <a href="{{ route('auth.jeune.login') }}" class="cta-button">
                    Se connecter à Brillio
                </a>
            </div>

            <div class="info-box">
                <strong>📝 Note :</strong> Votre profil Mentor a été archivé. Si vous étiez connecté, il est possible
                que vous deviez vous identifier à nouveau pour actualiser vos droits d'accès.
            </div>
        </div>

        <div class="footer">
            <p>© {{ date('Y') }} Brillio Platform. Tous droits réservés.</p>
        </div>
    </div>
</body>

</html>