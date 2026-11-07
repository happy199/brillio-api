@extends('emails.layouts.base')

@section('content')
<div style="font-family: 'Plus Jakarta Sans', Arial, sans-serif; color: #1e293b; max-width: 600px; margin: 0 auto;">
    <!-- Hero / En-tête -->
    <div style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); border-radius: 20px; padding: 40px 32px; text-align: center; margin-bottom: 28px; color: #ffffff;">
        <div style="width: 64px; height: 64px; margin: 0 auto 16px; background: rgba(255, 255, 255, 0.2); border-radius: 16px; display: inline-flex; align-items: center; justify-content: center; font-size: 32px; line-height: 64px;">
            🚀
        </div>
        <h1 style="color: #ffffff; margin: 0 0 10px; font-size: 26px; font-weight: 800; letter-spacing: -0.02em;">
            Félicitations, {{ $user->name }} !
        </h1>
        <p style="color: #e0e7ff; margin: 0; font-size: 16px; font-weight: 500;">
            Votre profil de mentor est désormais officiellement publié et visible sur Brillio.
        </p>
    </div>

    <!-- Intro -->
    <p style="font-size: 16px; line-height: 1.6; color: #334155; margin-bottom: 20px;">
        Merci d'avoir pris le temps de compléter votre profil. Vous rejoignez ainsi une communauté d'experts et de leaders engagés pour éclairer et propulser la jeunesse africaine.
    </p>

    <!-- Carte explicative -->
    <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; padding: 24px; margin-bottom: 28px;">
        <h3 style="margin: 0 0 12px; font-size: 17px; font-weight: 700; color: #1e293b;">
            💡 Que se passe-t-il maintenant ?
        </h3>
        <p style="margin: 0 0 16px; font-size: 14px; line-height: 1.6; color: #475569;">
            Votre fiche apparaît désormais dans l'annuaire public des mentors. Les jeunes talents dont le profil MBTI et les aspirations correspondent à votre expertise peuvent dès à présent consulter votre parcours et solliciter vos précieux conseils.
        </p>
        <p style="margin: 0; font-size: 14px; line-height: 1.6; color: #475569;">
            Dès qu'un jeune réserve une séance avec vous, vous recevrez une notification par email et par WhatsApp pour valider le créneau.
        </p>
    </div>

    <!-- Actions immédiates -->
    <div style="text-align: center; margin: 32px 0;">
        <a href="{{ route('mentor.dashboard') }}"
           style="display: inline-block; background: #4f46e5; color: #ffffff !important; padding: 14px 32px; text-decoration: none; border-radius: 12px; font-weight: 700; font-size: 15px; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);">
           Accéder à mon tableau de bord
        </a>
    </div>

    <!-- Signature -->
    <p style="margin: 28px 0 0; font-size: 14px; line-height: 1.6; color: #64748b;">
        Bienvenue dans l'aventure Brillio,<br>
        <strong>L'équipe Brillio</strong>
    </p>
</div>
@endsection

