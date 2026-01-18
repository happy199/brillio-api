<!DOCTYPE html>
<html>

<body style="font-family: Arial; max-width: 600px; margin: 0 auto; padding: 20px;">
    <h1 style="color: #6366f1;">Bienvenue {{ $user->name }} ! 👋</h1>
    <p style="font-size: 16px; line-height: 1.6;">
        Félicitations, votre compte Brillio a été créé avec succès ! 🚀
    </p>
    <p style="font-size: 16px; line-height: 1.6;">
        Nous sommes ravis de vous compter parmi nous. Que vous soyez là pour explorer votre avenir ou partager votre
        expérience, toute l'équipe est là pour vous accompagner.
    </p>
    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ route('login') }}" style="background: #6366f1; color: white; padding: 12px 24px; 
                text-decoration: none; border-radius: 6px; display: inline-block; font-weight: bold;">
            Accéder à mon espace
        </a>
    </div>
    <p style="color: #666; font-size: 12px; margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
        Vous recevez cet email suite à votre inscription sur Brillio.africa.
    </p>
</body>

</html>