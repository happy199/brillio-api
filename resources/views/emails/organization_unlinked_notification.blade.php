<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Notification de rupture de lien</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333; line-height: 1.6;">
    <p>Bonjour,</p>

    <p>Nous vous informons qu'un(e) <strong>{{ $role }}</strong> ({{ $user->name }}) a décidé de rompre la relation de mentorat avec votre organisation.</p>

    <p>Par conséquent, plus aucune information sur cette personne ne sera visible dans votre espace Brillio.</p>

    <p>Si vous pensez qu'il s'agit d'une erreur, veuillez recontacter la personne à l'adresse e-mail suivante : <strong>{{ $user->email }}</strong>.</p>

    <p>Cordialement,<br>L'équipe Brillio</p>
</body>
</html>
