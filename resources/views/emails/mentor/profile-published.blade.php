<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Félicitations pour votre profil publié</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f1f5f9;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: #f1f5f9; padding: 40px 10px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; background-color: #ffffff; border-radius: 20px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.06);">
                    
                    <!-- Header avec icône centrée via table -->
                    <tr>
                        <td align="center" style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); padding: 44px 32px; text-align: center;">
                            <table role="presentation" cellpadding="0" cellspacing="0" align="center" style="margin: 0 auto 20px auto;">
                                <tr>
                                    <td align="center" valign="middle" style="width: 72px; height: 72px; background-color: rgba(255, 255, 255, 0.2); border-radius: 18px; text-align: center; vertical-align: middle; line-height: 1;">
                                        <span style="font-size: 36px; display: inline-block; line-height: 1;">🚀</span>
                                    </td>
                                </tr>
                            </table>
                            <h1 style="color: #ffffff; margin: 0 0 10px; font-size: 26px; font-weight: 800; letter-spacing: -0.02em; text-align: center;">
                                Félicitations, {{ $user->name }} !
                            </h1>
                            <p style="color: #e0e7ff; margin: 0; font-size: 16px; font-weight: 500; text-align: center;">
                                Votre profil de mentor est désormais officiellement publié sur Brillio.
                            </p>
                        </td>
                    </tr>

                    <!-- Corps -->
                    <tr>
                        <td style="padding: 36px 32px; color: #1e293b;">
                            <p style="font-size: 16px; line-height: 1.6; color: #334155; margin: 0 0 20px 0;">
                                Merci d'avoir pris le temps de compléter votre profil. Vous rejoignez ainsi une communauté d'experts et de leaders engagés pour éclairer et propulser la jeunesse africaine.
                            </p>

                            <!-- Carte d'explication -->
                            <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; padding: 22px; margin-bottom: 28px;">
                                <h3 style="margin: 0 0 10px 0; font-size: 16px; font-weight: 700; color: #1e293b;">
                                    💡 Que se passe-t-il maintenant ?
                                </h3>
                                <p style="margin: 0 0 12px 0; font-size: 14px; line-height: 1.6; color: #475569;">
                                    Votre fiche apparaît désormais dans l'annuaire public des mentors. Les jeunes talents dont le profil MBTI et les aspirations correspondent à votre expertise peuvent dès à présent consulter votre parcours et solliciter vos précieux conseils.
                                </p>
                                <p style="margin: 0; font-size: 14px; line-height: 1.6; color: #475569;">
                                    Dès qu'un jeune réserve une séance avec vous, vous recevrez une notification par email et par WhatsApp pour valider le créneau.
                                </p>
                            </div>

                            <!-- Bouton CTA -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" align="center" style="margin: 30px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ route('mentor.dashboard') }}"
                                           style="display: inline-block; background: #4f46e5; color: #ffffff !important; padding: 15px 34px; text-decoration: none; border-radius: 12px; font-weight: 700; font-size: 15px; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);">
                                           Accéder à mon tableau de bord
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <!-- Signature -->
                            <p style="margin: 28px 0 0 0; font-size: 14px; line-height: 1.6; color: #64748b;">
                                Bienvenue dans l'aventure Brillio,<br>
                                <strong style="color: #334155;">L'équipe Brillio</strong>
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center" style="padding: 24px 32px; background-color: #f8fafc; border-top: 1px solid #e2e8f0; text-align: center;">
                            <p style="margin: 0; font-size: 12px; color: #94a3b8;">
                                © {{ date('Y') }} Brillio - Tous droits réservés.
                            </p>
                            <p style="margin: 8px 0 0 0; font-size: 12px; color: #94a3b8;">
                                Vous recevez cet email car vous êtes inscrit comme mentor sur Brillio.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
