@extends('emails.layouts.base')

@section('content')
@if($user->isMentor())
<div style="font-family: 'Inter', system-ui, -apple-system, sans-serif; color: #1f2937; max-width: 600px; margin: 0 auto;">
    <p style="margin: 0 0 16px; font-size: 16px; line-height: 1.6; color: #374151;">Bonjour <strong>{{ $user->name }}</strong>,</p>

    <p style="margin: 0 0 24px; font-size: 16px; line-height: 1.6; color: #4b5563;">
        Merci pour votre engagement auprès de la communauté <strong>Brillio</strong>. Chaque semaine, des jeunes talents africains consultent vos profils pour solliciter votre mentorat et bénéficier de vos retours d'expérience.
    </p>

    <!-- Urgency / Importance Card -->
    <div style="background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%); border-left: 4px solid #ea580c; border-radius: 12px; padding: 24px; margin: 24px 0; box-shadow: 0 4px 12px rgba(234, 88, 12, 0.08);">
        <h3 style="margin: 0 0 10px; font-size: 17px; font-weight: 700; color: #9a3412; display: flex; align-items: center;">
            📞 Il manque votre numéro de téléphone sur votre profil
        </h3>
        <p style="margin: 0; font-size: 14px; line-height: 1.6; color: #7c2d12;">
            Afin de pouvoir vous joindre rapidement lors de la confirmation d'une séance de mentorat et vous transmettre en direct les notifications importantes par appel ou WhatsApp, nous vous invitons à renseigner votre contact direct.
        </p>
    </div>

    <!-- CTA Button -->
    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ route('mentor.dashboard') }}"
           style="display: inline-block; background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%); color: #ffffff !important; padding: 16px 36px; text-decoration: none; border-radius: 10px; font-weight: 700; font-size: 16px; box-shadow: 0 4px 15px rgba(234, 88, 12, 0.3); transition: all 0.3s ease;">
           📲 Renseigner mon numéro sur mon tableau de bord
        </a>
    </div>

    <hr style="border: 0; border-top: 1px solid #f3f4f6; margin: 32px 0;">

    <!-- Exclusive WhatsApp Mentor Community -->
    <div style="margin-top: 24px;">
        <h3 style="color: #111827; font-size: 17px; font-weight: 700; margin: 0 0 12px 0;">
            🌍 Rejoignez le Groupe WhatsApp Exclusif des Mentors Brillio
        </h3>
        <p style="margin: 0 0 16px 0; font-size: 14px; color: #4b5563; line-height: 1.6;">
            Chaque mois, nous organisons ou sponsorisons des événements professionnels, salons de l'emploi, sommets tech et conférences d'envergure partout en <strong>Afrique et en Europe</strong>.
        </p>
        <p style="margin: 0 0 20px 0; font-size: 14px; color: #4b5563; line-height: 1.6;">
            En tant que mentor Brillio, nous mettons régulièrement à votre disposition :
        </p>

        <div style="font-size: 14px; color: #4b5563; line-height: 1.5; margin-bottom: 24px;">
            <div style="display: table; width: 100%; margin-bottom: 10px;">
                <div style="display: table-cell; vertical-align: top; width: 32px; font-size: 18px;">🎟️</div>
                <div style="display: table-cell; vertical-align: top;">
                    <strong style="color: #1f2937;">Places gratuites & pass VIP</strong> pour des conférences et sommets exclusifs.
                </div>
            </div>
            <div style="display: table; width: 100%; margin-bottom: 10px;">
                <div style="display: table-cell; vertical-align: top; width: 32px; font-size: 18px;">🏷️</div>
                <div style="display: table-cell; vertical-align: top;">
                    <strong style="color: #1f2937;">Billets d'entrée à tarifs préférentiels</strong> réservés exclusivement à nos experts.
                </div>
            </div>
            <div style="display: table; width: 100%;">
                <div style="display: table-cell; vertical-align: top; width: 32px; font-size: 18px;">🤝</div>
                <div style="display: table-cell; vertical-align: top;">
                    <strong style="color: #1f2937;">Réseautage de haut niveau</strong> entre mentors, dirigeants et partenaires stratégiques.
                </div>
            </div>
        </div>

        <p style="margin: 0 0 20px 0; font-size: 14px; color: #374151; line-height: 1.6; background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; padding: 14px;">
            En rejoignant le groupe WhatsApp des mentors, nous pourrons facilement vous transmettre en temps réel toutes ces opportunités exclusives dès leur ouverture !
        </p>

        <!-- Mentor Group Card -->
        <div style="background-color: #ffffff; border: 2px solid #22c55e; border-radius: 12px; padding: 20px; margin-bottom: 24px; text-align: center;">
            <div style="font-size: 28px; margin-bottom: 8px;">💬</div>
            <h4 style="margin: 0 0 6px 0; color: #166534; font-size: 16px; font-weight: 700;">Communauté WhatsApp des Mentors Brillio</h4>
            <p style="margin: 0 0 16px 0; color: #4b5563; font-size: 13px; line-height: 1.4;">Accès direct aux invitations, billets gratuits et opportunités de networking.</p>
            <a href="{{ $mentorWhatsappGroupUrl }}" target="_blank"
               style="display: inline-block; background-color: #25D366; color: #ffffff !important; padding: 12px 28px; text-decoration: none; border-radius: 8px; font-weight: 700; font-size: 15px; box-shadow: 0 2px 8px rgba(37, 211, 102, 0.3);">
               👉 Rejoindre le groupe WhatsApp des mentors
            </a>
        </div>
    </div>

    <!-- Sign-off -->
    <p style="margin: 24px 0 0 0; font-size: 15px; line-height: 1.6; color: #374151;">
        Avec toute notre considération,<br>
        <strong>L'équipe Brillio</strong>
    </p>
</div>
@else
<div style="font-family: 'Inter', system-ui, -apple-system, sans-serif; color: #1f2937; max-width: 600px; margin: 0 auto;">
    <p style="margin: 0 0 16px; font-size: 16px; line-height: 1.6; color: #374151;">Bonjour <strong>{{ $user->name }}</strong>,</p>

    <p style="margin: 0 0 24px; font-size: 16px; line-height: 1.6; color: #4b5563;">
        Chaque jour, de nouvelles opportunités exclusives sont ajoutées sur <strong>Brillio</strong> : bourses d'études, offres d'emploi, stages de fin d'études, formations certifiantes et événements de réseautage avec nos organisations partenaires.
    </p>

    <!-- Urgency Message Card (FOMO) -->
    <div style="background: linear-gradient(135deg, #f5f3ff 0%, #e0e7ff 100%); border-left: 4px solid #6366f1; border-radius: 12px; padding: 24px; margin: 24px 0; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.05);">
        <h3 style="margin: 0 0 10px; font-size: 17px; font-weight: 700; color: #4338ca; display: flex; align-items: center;">
            ⚠️ Le problème des e-mails ? Ils arrivent souvent trop tard.
        </h3>
        <p style="margin: 0; font-size: 14px; line-height: 1.6; color: #312e81;">
            Dans le monde professionnel, <strong>les premières heures font toute la différence</strong>. Pour t'éviter de rater l'opportunité qui pourrait propulser ta carrière, Brillio envoie désormais ses alertes en temps réel directement sur <strong>WhatsApp</strong>.
        </p>
        <p style="margin: 8px 0 0; font-size: 13px; font-weight: 600; color: #4f46e5;">
            🔥 Déjà plus de 3 200 jeunes ont activé leurs alertes pour se positionner en priorité !
        </p>
    </div>

    <!-- CTA Button -->
    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ route('jeune.dashboard') }}"
           style="display: inline-block; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); color: #ffffff !important; padding: 16px 36px; text-decoration: none; border-radius: 10px; font-weight: 700; font-size: 16px; box-shadow: 0 4px 15px rgba(99, 102, 241, 0.35); transition: all 0.3s ease;">
           📲 Activer mes alertes WhatsApp
        </a>
    </div>

    <!-- Grid of Opportunities -->
    <div style="margin: 32px 0;">
        <h4 style="color: #111827; font-size: 16px; font-weight: 700; margin: 0 0 16px 0; border-bottom: 2px solid #f3f4f6; padding-bottom: 8px;">
            💼 Ce que tu reçois instantanément sur WhatsApp :
        </h4>

        <div style="font-size: 14px; color: #4b5563; line-height: 1.5;">
            <div style="display: table; width: 100%; margin-bottom: 12px;">
                <div style="display: table-cell; vertical-align: top; width: 40px; font-size: 20px; padding: 10px 0;">🎓</div>
                <div style="display: table-cell; vertical-align: top; padding: 10px 0;">
                    <strong style="color: #1f2937;">Bourses d'études exclusives</strong><br>
                    Sois alerté dès la publication des financements de nos partenaires nationaux et internationaux.
                </div>
            </div>
            <div style="display: table; width: 100%; margin-bottom: 12px;">
                <div style="display: table-cell; vertical-align: top; width: 40px; font-size: 20px; padding: 10px 0;">💼</div>
                <div style="display: table-cell; vertical-align: top; padding: 10px 0;">
                    <strong style="color: #1f2937;">Offres d'emploi & Stages</strong><br>
                    Reçois des opportunités de recrutement ciblées selon ton type de personnalité MBTI.
                </div>
            </div>
            <div style="display: table; width: 100%; margin-bottom: 12px;">
                <div style="display: table-cell; vertical-align: top; width: 40px; font-size: 20px; padding: 10px 0;">🚀</div>
                <div style="display: table-cell; vertical-align: top; padding: 10px 0;">
                    <strong style="color: #1f2937;">Formations & Ateliers</strong><br>
                    Accède en priorité à des webinaires et à des sessions de formation avec des mentors certifiés.
                </div>
            </div>
            <div style="display: table; width: 100%;">
                <div style="display: table-cell; vertical-align: top; width: 40px; font-size: 20px; padding: 10px 0;">🤝</div>
                <div style="display: table-cell; vertical-align: top; padding: 10px 0;">
                    <strong style="color: #1f2937;">Partenariats & Événements</strong><br>
                    Réseaute directement avec les organisations membres de la communauté Brillio.
                </div>
            </div>
        </div>
    </div>

    <hr style="border: 0; border-top: 1px solid #f3f4f6; margin: 32px 0;">

    <!-- Motivated Section -->
    <div style="margin-top: 24px;">
        <h3 style="color: #1f2937; font-size: 16px; font-weight: 700; margin: 0 0 12px 0;">🚀 Envie d'aller encore plus vite ?</h3>
        <p style="margin: 0 0 20px 0; font-size: 14px; color: #4b5563; line-height: 1.5;">
            Si tu es motivé et prêt à passer à l'action, rejoins directement nos canaux officiels :
        </p>

        <!-- Channel Link Card -->
        <div style="background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 10px; padding: 18px; margin-bottom: 16px;">
            <div style="display: table; width: 100%;">
                <div style="display: table-cell; vertical-align: top; width: 36px; font-size: 24px; line-height: 1;">📢</div>
                <div style="display: table-cell; vertical-align: top;">
                    <h4 style="margin: 0 0 4px 0; color: #111827; font-size: 14px; font-weight: 600;">Canal WhatsApp officiel</h4>
                    <p style="margin: 0 0 10px 0; color: #6b7280; font-size: 13px; line-height: 1.4;">Suis toutes les actualités majeures et les lancements de Brillio en temps réel.</p>
                    <a href="{{ $whatsappChannelUrl }}" target="_blank" style="color: #4f46e5; text-decoration: none; font-weight: 600; font-size: 13px;">Rejoindre la chaîne &rarr;</a>
                </div>
            </div>
        </div>

        <!-- Group Link Card -->
        <div style="background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 10px; padding: 18px; margin-bottom: 24px;">
            <div style="display: table; width: 100%;">
                <div style="display: table-cell; vertical-align: top; width: 36px; font-size: 24px; line-height: 1;">🔒</div>
                <div style="display: table-cell; vertical-align: top;">
                    <h4 style="margin: 0 0 4px 0; color: #111827; font-size: 14px; font-weight: 600;">Groupe Privé VIP</h4>
                    <p style="margin: 0 0 10px 0; color: #6b7280; font-size: 13px; line-height: 1.4;">Sois le premier informé des opportunités de stages et des invitations exclusives.</p>
                    <a href="{{ $whatsappGroupUrl }}" target="_blank" style="color: #4f46e5; text-decoration: none; font-weight: 600; font-size: 13px;">Rejoindre le groupe privé &rarr;</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Sign-off -->
    <p style="margin: 24px 0 0 0; font-size: 15px; line-height: 1.6; color: #374151;">
        À très vite,<br>
        <strong>L'équipe Brillio</strong>
    </p>
</div>
@endif
@endsection


