<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenue chez les mentors Brillio</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', Arial, sans-serif; background-color: #f8fafc;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f8fafc; padding: 40px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 24px; overflow: hidden; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); padding: 48px 40px; text-align: center;">
                            <img src="{{ asset('android-chrome-512x512.png') }}" alt="Brillio" style="width: 80px; height: 80px; margin-bottom: 24px; border-radius: 20px; background: white; padding: 10px;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 28px; font-weight: 800; letter-spacing: -0.025em;">Félicitations, {{ $user->name }} ! 🚀</h1>
                            <p style="color: #e0e7ff; margin: 12px 0 0; font-size: 18px; font-weight: 500;">Votre profil est désormais publié sur Brillio.</p>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding: 40px; color: #1e293b;">
                            <p style="font-size: 17px; line-height: 1.6; margin-bottom: 24px;">
                                Merci d'avoir pris le temps de compléter votre profil. Vous faites désormais partie de notre communauté de mentors engagés pour la réussite des jeunes talents.
                            </p>

                            <p style="font-size: 17px; line-height: 1.6; margin-bottom: 32px; font-weight: 600; color: #4338ca;">
                                En attendant de recevoir vos premières demandes de mentorat, voici ce que vous pouvez faire :
                            </p>

                            <!-- Actions -->
                            <div style="margin-bottom: 32px;">
                                <!-- Action 1 -->
                                <div style="background-color: #f1f5f9; border-radius: 16px; padding: 20px; margin-bottom: 16px;">
                                    <table width="100%">
                                        <tr>
                                            <td style="width: 40px; vertical-align: top;">
                                                <span style="font-size: 24px;">📚</span>
                                            </td>
                                            <td>
                                                <h3 style="margin: 0 0 8px; font-size: 16px; font-weight: 700;">Explorez les ressources</h3>
                                                <p style="margin: 0; font-size: 14px; color: #64748b;">Découvrez les partages et conseils de vos confrères mentors pour vous inspirer.</p>
                                            </td>
                                        </tr>
                                    </table>
                                </div>

                                <!-- Action 2 -->
                                <div style="background-color: #f1f5f9; border-radius: 16px; padding: 20px; margin-bottom: 16px;">
                                    <table width="100%">
                                        <tr>
                                            <td style="width: 40px; vertical-align: top;">
                                                <span style="font-size: 24px;">✍️</span>
                                            </td>
                                            <td>
                                                <h3 style="margin: 0 0 8px; font-size: 16px; font-weight: 700;">Partagez votre expertise</h3>
                                                <p style="margin: 0; font-size: 14px; color: #64748b;">Créez et publiez vos propres ressources (articles, vidéos, guides) pour aider la communauté.</p>
                                            </td>
                                        </tr>
                                    </table>
                                </div>

                                <!-- Action 3 -->
                                <div style="background-color: #f1f5f9; border-radius: 16px; padding: 20px;">
                                    <table width="100%">
                                        <tr>
                                            <td style="width: 40px; vertical-align: top;">
                                                <span style="font-size: 24px;">🔍</span>
                                            </td>
                                            <td>
                                                <h3 style="margin: 0 0 8px; font-size: 16px; font-weight: 700;">Découvrez les talents</h3>
                                                <p style="margin: 0; font-size: 14px; color: #64748b;">Explorez la liste des jeunes talents en cas de besoins futurs en recrutement.</p>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <!-- Buttons -->
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding-top: 20px;">
                                        <div style="display: inline-block;">
                                            <a href="{{ config('app.url') }}/ressources" style="background: #4f46e5; color: #ffffff; padding: 14px 28px; text-decoration: none; border-radius: 12px; font-weight: 700; margin-right: 12px; display: inline-block;">📚 Voir les ressources</a>
                                            <a href="{{ config('app.url') }}/explorer" style="background: #ffffff; color: #4f46e5; border: 2px solid #4f46e5; padding: 12px 28px; text-decoration: none; border-radius: 12px; font-weight: 700; display: inline-block;">🔍 Explorer les talents</a>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding: 32px 40px; background-color: #f8fafc; text-align: center; border-top: 1px solid #e2e8f0;">
                            <p style="margin: 0; font-size: 14px; color: #94a3b8;">
                                Vous recevez cet email car vous êtes un mentor certifié sur Brillio.
                            </p>
                            <p style="margin: 12px 0 0; font-size: 14px; color: #94a3b8;">
                                &copy; {{ date('Y') }} Brillio. Tous droits réservés.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
