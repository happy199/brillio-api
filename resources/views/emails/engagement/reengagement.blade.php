<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; line-height: 1.6; color: #374151; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 0 auto; padding: 40px 20px; }
        .header { text-align: center; margin-bottom: 40px; }
        .logo { font-size: 24px; font-weight: bold; color: #7c3aed; text-decoration: none; }
        .hero { background: linear-gradient(135deg, #7c3aed 0%, #db2777 100%); border-radius: 24px; padding: 40px; color: white; text-align: center; margin-bottom: 40px; }
        .hero h1 { margin: 0 0 16px; font-size: 28px; }
        .hero p { margin: 0; opacity: 0.9; font-size: 18px; }
        .features { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 40px; }
        .feature-card { background: #f9fafb; border-radius: 16px; padding: 24px; text-align: center; border: 1px solid #f3f4f6; }
        .feature-icon { font-size: 24px; margin-bottom: 12px; }
        .feature-title { font-weight: bold; margin-bottom: 8px; color: #111827; }
        .feature-desc { font-size: 14px; color: #6b7280; }
        .cta-container { text-align: center; }
        .btn { display: inline-block; background: #7c3aed; color: white; text-decoration: none; padding: 16px 32px; border-radius: 12px; font-weight: bold; font-size: 16px; transition: background 0.2s; }
        .footer { text-align: center; margin-top: 40px; color: #9ca3af; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="{{ config('app.url') }}" class="logo">Brillio</a>
        </div>

        <div class="hero">
            <h1>Ça fait un petit moment, {{ $user->name }} !</h1>
            <p>Ta réussite n'attend que toi. Reviens explorer toutes les opportunités sur Brillio.</p>
        </div>

        <div class="features">
            <div class="feature-card">
                <div class="feature-icon">🤖</div>
                <div class="feature-title">Discuter avec l'IA</div>
                <div class="feature-desc">Pose tes questions sur ton orientation ou ta carrière.</div>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🧠</div>
                <div class="feature-title">Test de Personnalité</div>
                <div class="feature-desc">Repasse ton test pour affiner tes recommandations.</div>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📁</div>
                <div class="feature-title">Tes Documents</div>
                <div class="feature-desc">Dépose tes diplômes et CV pour les avoir partout avec toi.</div>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🤝</div>
                <div class="feature-title">Mentors</div>
                <div class="feature-desc">Entre en contact avec des experts pour booster ton parcours.</div>
            </div>
        </div>

        <div class="cta-container">
            <a href="{{ config('app.url') }}" class="btn">Je reviens sur Brillio</a>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} Brillio. Tous droits réservés.</p>
            <p>Tu reçois cet email car tu es inscrit sur Brillio.</p>
        </div>
    </div>
</body>
</html>
