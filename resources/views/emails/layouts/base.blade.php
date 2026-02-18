<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? 'Brillio' }}</title>
</head>

<body
    style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background-color: #f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4; padding: 20px 0;">
        <tr>
            <td align="center">
                <!-- Container -->
                <table width="600" cellpadding="0" cellspacing="0"
                    style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); max-width: 100%;">
                    <!-- Header -->
                    <tr>
                        <td
                            style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); padding: 30px; text-align: center; border-radius: 8px 8px 0 0;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 28px; font-weight: 700;">Brillio</h1>
                            <p style="color: #e0e7ff; margin: 5px 0 0; font-size: 14px;">Ton avenir, ton choix</p>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px; color: #374151; font-size: 16px; line-height: 1.6;">
                            @yield('content')
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td
                            style="background-color: #f9fafb; padding: 20px; text-align: center; border-radius: 0 0 8px 8px; border-top: 1px solid #e5e7eb;">
                            <p style="margin: 0; font-size: 12px; color: #6b7280;">
                                © 2026 Brillio - Tous droits réservés
                            </p>
                            <p style="margin: 10px 0 0; font-size: 12px;">
                                <a href="{{ route('about') }}" style="color: #6366f1; text-decoration: none;">À
                                    propos</a> •
                                <a href="{{ route('contact') }}"
                                    style="color: #6366f1; text-decoration: none;">Contact</a> •
                                <a href="{{ route('privacy-policy') }}"
                                    style="color: #6366f1; text-decoration: none;">Confidentialité</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>