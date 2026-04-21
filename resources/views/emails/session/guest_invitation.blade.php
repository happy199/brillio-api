<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invitation à une séance - Brillio</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f7f9; }
        .container { max-width: 600px; margin: 20px auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .header { background: linear-gradient(135deg, #f97316 0%, #ef4444 50%, #ec4899 100%); padding: 40px 20px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 24px; font-weight: 800; }
        .content { padding: 40px 30px; }
        .content h2 { color: #111827; margin-top: 0; font-size: 20px; }
        .info-box { background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; margin: 20px 0; }
        .info-item { margin-bottom: 10px; font-size: 14px; }
        .info-label { font-weight: 700; color: #64748b; text-transform: uppercase; font-size: 11px; margin-bottom: 2px; }
        .info-value { font-weight: 600; color: #1e293b; }
        .btn { display: inline-block; padding: 16px 32px; background-color: #111827; color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: 700; font-size: 16px; margin-top: 20px; text-align: center; width: 100%; box-sizing: border-box; }
        .footer { padding: 20px; text-align: center; font-size: 12px; color: #94a3b8; }
        .footer a { color: #475569; text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Brillio</h1>
        </div>
        <div class="content">
            <h2>Bonjour {{ $recipient->name }},</h2>
            <p>L'organisation <strong>{{ $session->organization->name }}</strong> vous a invité à animer une séance d'expertise sur la plateforme Brillio.</p>
            
            <p>Votre expertise est précieuse pour accompagner les jeunes talents de notre plateforme. Voici les détails de votre intervention :</p>

            <div class="info-box">
                <div class="info-item">
                    <div class="info-label">Sujet de la séance</div>
                    <div class="info-value">{{ $session->title }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Date et Heure</div>
                    <div class="info-value">{{ $session->scheduled_at->translatedFormat('d F Y \à H:i') }} (GMT)</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Durée prévue</div>
                    <div class="info-value">{{ $session->duration_minutes }} minutes</div>
                </div>
            </div>

            <p>Pour rejoindre la visioconférence le moment venu, il vous suffit de cliquer sur le bouton ci-dessous. <strong>Aucune création de compte n'est requise.</strong></p>

            <a href="{{ $magicLink }}" class="btn">Rejoindre la séance</a>

            <p style="margin-top: 30px; font-size: 14px; color: #64748b; font-style: italic;">
                Note : Pour des raisons de sécurité, le lien ci-dessus est strictement personnel et ne doit pas être partagé.
            </p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} Brillio. Propulsez les talents de demain.</p>
            <p><a href="https://brillio.africa">Visiter notre site web</a></p>
        </div>
    </div>
</body>
</html>
