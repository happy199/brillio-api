<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invitation Brillio</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }

        .container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 40px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo h1 {
            color: #10b981;
            font-size: 32px;
            margin: 0;
        }

        .logo .tagline {
            color: #6b7280;
            font-size: 14px;
            margin-top: 5px;
        }

        .organization-banner {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            text-align: center;
        }

        .organization-banner h2 {
            margin: 0;
            font-size: 24px;
        }

        .organization-banner p {
            margin: 10px 0 0;
            font-size: 14px;
            opacity: 0.9;
        }

        .content {
            margin-bottom: 30px;
        }

        .content p {
            margin: 15px 0;
        }

        .cta-button {
            display: inline-block;
            padding: 16px 32px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            text-align: center;
            margin: 20px 0;
        }

        .cta-button:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
        }

        .button-container {
            text-align: center;
        }

        .info-box {
            background-color: #f0fdf4;
            border-left: 4px solid #10b981;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }

        .info-box strong {
            color: #059669;
        }

        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 12px;
        }

        .footer a {
            color: #10b981;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Logo -->
        <div class="logo">
            <h1>Brillio</h1>
            <p class="tagline">Votre parcours vers l'excellence</p>
        </div>

        <!-- Organization Banner -->
        <div class="organization-banner">
            <h2>{{ $organization->name }}</h2>
            <p>vous invite à rejoindre Brillio</p>
        </div>

        <!-- Content -->
        <div class="content">
            <p>Bonjour,</p>

            <p>Vous avez été invité(e) par <strong>{{ $organization->name }}</strong> à rejoindre la plateforme
                <strong>Brillio</strong>, une communauté africaine de mentorat et d'accompagnement professionnel.</p>

            <div class="info-box">
                <p><strong>🎯 Avec Brillio, vous pourrez :</strong></p>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>Être accompagné(e) par des mentors expérimentés</li>
                    <li>Découvrir votre personnalité professionnelle (test MBTI)</li>
                    <li>Accéder à des ressources pédagogiques exclusives</li>
                    <li>Construire votre parcours professionnel</li>
                </ul>
            </div>

            <p>En tant que membre parrainé par <strong>{{ $organization->name }}</strong>, vous bénéficiez d'un suivi
                personnalisé et votre progression sera visible par votre organisation partenaire.</p>
        </div>

        <!-- Call to Action -->
        <div class="button-container">
            <a href="{{ $registrationUrl }}" class="cta-button">
                🚀 Créer mon compte gratuitement
            </a>
        </div>

        <p style="text-align: center; color: #6b7280; font-size: 14px; margin-top: 20px;">
            Ou copiez ce lien dans votre navigateur :<br>
            <span style="color: #10b981; word-break: break-all;">{{ $registrationUrl }}</span>
        </p>

        <div class="info-box" style="margin-top: 30px;">
            <p style="margin: 0;"><strong>⏰ Attention :</strong> Cette invitation expire le <strong>{{
                    $invitation->expires_at->format('d/m/Y') }}</strong>.</p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Cet email vous a été envoyé par <strong>{{ $organization->name }}</strong> via Brillio.</p>
            <p>
                <a href="https://brillio.africa">Visit Brillio</a> |
                <a href="https://brillio.africa/politique-de-confidentialite">Politique de confidentialité</a>
            </p>
            <p style="margin-top: 15px; font-size: 11px; color: #9ca3af;">
                © {{ date('Y') }} Brillio. Tous droits réservés.
            </p>
        </div>
    </div>
</body>

</html>