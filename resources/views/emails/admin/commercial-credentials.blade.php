<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: #6366f1;
            color: white;
            padding: 30px;
            border-radius: 12px 12px 0 0;
            text-align: center;
        }

        .content {
            background: #ffffff;
            padding: 40px;
            border: 1px solid #e5e7eb;
            border-radius: 0 0 12px 12px;
        }

        .credentials {
            background: #f9fafb;
            padding: 25px;
            border-radius: 8px;
            border: 1px dashed #d1d5db;
            margin: 25px 0;
        }

        .button {
            display: inline-block;
            padding: 14px 28px;
            background-color: #6366f1;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            margin-top: 20px;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            color: #6b7280;
            font-size: 14px;
        }

        .label {
            font-weight: bold;
            color: #4b5563;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Bienvenue Commercial ! 👋</h1>
    </div>
    <div class="content">
        <p>Bonjour {{ $user->name }},</p>
        <p>Votre compte Commercial sur la plateforme <strong>Brillio</strong> a été configuré avec succès.</p>

        <p>Voici vos identifiants de connexion pour accéder à votre espace dédié :</p>

        <div class="credentials">
            <p><span class="label">Email :</span> {{ $user->email }}</p>
            <p><span class="label">Mot de passe temporaire :</span> <code
                    style="background: #eee; padding: 2px 6px; border-radius: 4px;">{{ $password }}</code></p>
        </div>

        <p style="color: #ef4444; font-size: 14px;"><em>Note : Pour votre sécurité, nous vous recommandons de changer
                votre mot de passe dès votre première connexion.</em></p>

        <div style="text-align: center;">
            <a href="{{ $loginUrl }}" class="button">Accéder à mon Espace Commercial</a>
        </div>

        <p style="margin-top: 30px;">Si le bouton ne fonctionne pas, copiez et collez l'URL suivante dans votre
            navigateur :<br>
            <small>{{ $loginUrl }}</small>
        </p>
    </div>
    <div class="footer">
        © {{ date('Y') }} Brillio. Vos accès sécurisés.
    </div>
</body>

</html>