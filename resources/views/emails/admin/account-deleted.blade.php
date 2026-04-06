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
            <h2>Mise à jour concernant votre compte</h2>
        </div>

        <div class="content">
            <p>Bonjour {{ $userName }},</p>

            <p>Nous vous informons que votre compte utilisateur sur la plateforme <strong>Brillio</strong> a été supprimé par l'administration.</p>

            <p>Sachez que cette action est réversible si vous souhaitez rejoindre à nouveau notre communauté. En vous connectant ou en vous inscrivant à l'avenir avec cette même adresse, un nouveau compte pourra être créé avec de nouveaux paramètres.</p>

            <p>Vous êtes et serez toujours le bienvenu pour revenir explorer de nouvelles opportunités de mentorat ou d'accompagnement sur Brillio lorsque vous le souhaiterez.</p>

            <p>En vous souhaitant une excellente continuation de la part de toute l'équipe.</p>
        </div>

        <div class="footer">
            <p>© {{ date('Y') }} Brillio Platform. Tous droits réservés.</p>
        </div>
    </div>
</body>

</html>
