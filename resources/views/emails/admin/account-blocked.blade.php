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
            background: #fef2f2;
            color: #991b1b;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            text-align: center;
            border: 1px solid #fecaca;
        }

        .hero h2 {
            margin: 0;
            font-size: 24px;
        }

        .content p {
            margin: 20px 0;
        }

        .info-box {
            background-color: #f9fafb;
            border-left: 4px solid #6b7280;
            padding: 20px;
            margin: 25px 0;
            border-radius: 4px;
            font-size: 14px;
            color: #4b5563;
            font-style: italic;
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
            <h2>Suspension de votre compte</h2>
        </div>

        <div class="content">
            <p>Bonjour {{ $userName }},</p>

            <p>Nous vous informons que votre compte utilisateur sur <strong>Brillio</strong> a été temporairement suspendu par l'administration.</p>

            <p>Le motif associé à cette suspension est le suivant :</p>
            <div class="info-box">
                "{{ $reason }}"
            </div>

            <p>Cette suspension vise à préserver la sécurité et la qualité de la plateforme pour l'ensemble de notre communauté.</p>

            <p>Si vous pensez qu'il s'agit d'une erreur ou si vous souhaitez obtenir plus de détails, nous vous invitons à contacter notre support en répondant simplement à ce message ou via notre formulaire de contact sur le site.</p>
        </div>

        <div class="footer">
            <p>© {{ date('Y') }} Brillio Platform. Tous droits réservés.</p>
        </div>
    </div>
</body>

</html>
