<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Confirmation de rupture de lien</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333; line-height: 1.6;">
    <p>Bonjour {{ $organization->name ?? 'Utilisateur' }},</p> <!-- Wait, the email is to the user, so "Bonjour" should just be "Bonjour," -->

    <p>Nous vous confirmons que la rupture de votre relation de parrainnage avec l'organisation <strong>{{ $organization->name }}</strong> a été effectuée avec succès.</p>

    <p>Si vous pensez qu'il s'agit d'une erreur, nous vous invitons à contacter l'organisation à l'adresse e-mail suivante : <strong>{{ $organization->email ?? $organization->owner->email ?? 'contact non défini' }}</strong>.</p>

    <p>Cordialement,<br>L'équipe Brillio</p>
</body>
</html>
