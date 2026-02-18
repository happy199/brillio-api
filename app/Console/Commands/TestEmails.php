<?php

namespace App\Console\Commands;

use App\Mail\Mentorship\MentorshipAccepted;
use App\Mail\Mentorship\MentorshipRequested;
use App\Mail\Mentorship\MentorshipRefused;
use App\Mail\Session\SessionProposed;
use App\Mail\Session\SessionConfirmed;
use App\Mail\Session\SessionReminder;
use App\Mail\Session\SessionCompleted;
use App\Mail\Session\SessionRefused;
use App\Mail\Session\SessionCancelled;
use App\Mail\Engagement\ProfileCompletionReminder;
use App\Mail\Engagement\NewMentorsWeekly;
use App\Models\Mentorship;
use App\Models\MentoringSession;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:emails {email?} {--to=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test email templates (mentorship + session emails)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $emailType = $this->argument('email');
        $recipient = $this->option('to') ?? 'test@example.com';

        if (!$emailType) {
            $emailType = $this->choice(
                'Quel email voulez-vous tester ?',
            [
                'mentorship-request',
                'mentorship-accepted',
                'mentorship-refused',
                'session-proposed',
                'session-confirmed',
                'session-reminder',
                'session-completed',
                'session-refused',
                'session-cancelled',
                'profile-reminder',
                'new-mentors-digest',
                'all'
            ],
                11
            );
        }

        $this->info("📧 Test des emails vers : {$recipient}");

        switch ($emailType) {
            case 'mentorship-request':
                $this->testMentorshipRequest($recipient);
                break;
            case 'mentorship-refused':
                $this->testMentorshipRefused($recipient);
                break;
            case 'session-proposed':
                $this->testSessionProposed($recipient);
                break;
            case 'session-confirmed':
                $this->testSessionConfirmed($recipient);
                break;
            case 'session-reminder':
                $this->testSessionReminder($recipient);
                break;
            case 'session-completed':
                $this->testSessionCompleted($recipient);
                break;
            case 'session-refused':
                $this->testSessionRefused($recipient);
                break;
            case 'session-cancelled':
                $this->testSessionCancelled($recipient);
                break;
            case 'profile-reminder':
                $this->testProfileReminder($recipient);
                break;
            case 'new-mentors-digest':
                $this->testNewMentorsDigest($recipient);
                break;
            case 'all':
                $this->testMentorshipRequest($recipient);
                $this->testMentorshipAccepted($recipient);
                $this->testMentorshipRefused($recipient);
                $this->testSessionProposed($recipient);
                $this->testSessionConfirmed($recipient);
                $this->testSessionReminder($recipient);
                $this->testSessionCompleted($recipient);
                $this->testSessionRefused($recipient);
                $this->testSessionCancelled($recipient);
                $this->testProfileReminder($recipient);
                $this->testNewMentorsDigest($recipient);
                break;
            default:
                $this->error("Email type inconnu : {$emailType}");
                return 1;
        }

        $this->newLine();
        $this->info('✅ Emails envoyés ! Vérifiez votre boîte (Mailtrap/Brevo)');
        return 0;
    }

    private function testMentorshipRequest($recipient)
    {
        $this->line('💼 Envoi Mentorship Request...');

        // Create test data
        $mentor = User::where('user_type', 'mentor')->first() ?? new User([
            'name' => 'Marie Dupont',
            'email' => 'mentor@brillio.africa',
            'user_type' => 'mentor',
        ]);

        $mentee = User::where('user_type', 'jeune')->first() ?? new User([
            'name' => 'Jean Kouassi',
            'email' => 'jeune@brillio.africa',
            'user_type' => 'jeune',
        ]);

        $mentorship = new Mentorship([
            'mentor_id' => $mentor->id ?? 1,
            'mentee_id' => $mentee->id ?? 2,
            'request_message' => "Bonjour, je suis très intéressé par votre parcours dans la tech. J'aimerais apprendre de votre expérience et recevoir vos conseils pour ma carrière.",
            'status' => 'pending',
        ]);

        $requestsUrl = route('mentor.mentorship.requests');

        Mail::to($recipient)->send(new MentorshipRequested($mentorship, $mentor, $mentee, $requestsUrl, $requestsUrl));
    }

    private function testMentorshipAccepted($recipient)
    {
        $this->line('🎉 Envoi Mentorship Accepted...');

        $mentor = User::where('user_type', 'mentor')->with('mentorProfile')->first() ?? new User([
            'name' => 'Marie Dupont',
            'email' => 'mentor@brillio.africa',
            'user_type' => 'mentor',
        ]);

        // Load or mock mentor profile
        if (!$mentor->mentorProfile) {
            $mentor->setRelation('mentorProfile', (object)[
                'current_position' => 'Senior Software Engineer',
                'current_company' => 'Google',
                'years_of_experience' => 8,
                'specialization' => 'tech',
                'specializationModel' => (object)['name' => 'Technologie & IT'],
            ]);
        }

        $mentee = User::where('user_type', 'jeune')->first() ?? new User([
            'name' => 'Jean Kouassi',
            'email' => 'jeune@brillio.africa',
            'user_type' => 'jeune',
        ]);

        $mentorship = new Mentorship([
            'mentor_id' => $mentor->id ?? 1,
            'mentee_id' => $mentee->id ?? 2,
            'status' => 'accepted',
        ]);

        $bookingUrl = route('jeune.sessions.create', ['mentor' => $mentor->id ?? 1]);

        Mail::to($recipient)->send(new MentorshipAccepted($mentorship, $mentor, $mentee, $bookingUrl));
    }

    private function testSessionProposed($recipient)
    {
        $this->line('📅 Envoi Session Proposed...');

        $mentor = User::where('user_type', 'mentor')->first() ?? new User([
            'name' => 'Marie Dupont',
            'email' => 'mentor@brillio.africa',
            'user_type' => 'mentor',
        ]);

        $mentee = User::where('user_type', 'jeune')->first() ?? new User([
            'name' => 'Jean Martin',
            'email' => 'jeune@brillio.africa',
            'user_type' => 'jeune',
        ]);

        $session = new MentoringSession([
            'mentor_id' => $mentor->id ?? 1,
            'scheduled_at' => now()->addDays(3)->setTime(14, 0),
            'duration_minutes' => 60,
            'price' => 5000,
            'status' => 'proposed',
            'notes' => 'Session de découverte pour définir vos objectifs de carrière',
        ]);

        // Get mentee credits using SystemSetting
        $creditPrice = SystemSetting::getValue('credit_price_jeune', 50);
        $menteeCredits = (int)floor(7500 / $creditPrice); // 7500 FCFA = 150 crédits si 1 crédit = 50 FCFA

        $acceptUrl = route('jeune.sessions.show', ['session' => $session->id ?? 1]);
        $refuseUrl = route('jeune.sessions.show', ['session' => $session->id ?? 1]);

        Mail::to($recipient)->send(new SessionProposed(
            $session,
            $mentor,
            $mentee,
            $menteeCredits,
            $acceptUrl,
            $refuseUrl
            ));
    }

    private function testSessionConfirmed($recipient)
    {
        $this->line('✅ Envoi Session Confirmed...');

        $mentor = User::where('user_type', 'mentor')->first() ?? new User([
            'name' => 'Marie Dupont',
            'email' => 'mentor@brillio.africa',
            'user_type' => 'mentor',
        ]);

        $mentees = User::where('user_type', 'jeune')->take(2)->get();
        if ($mentees->isEmpty()) {
            $mentees = collect([
                new User(['name' => 'Jean Martin', 'email' => 'jeune1@brillio.africa']),
                new User(['name' => 'Sophie Diallo', 'email' => 'jeune2@brillio.africa']),
            ]);
        }

        $session = new MentoringSession([
            'mentor_id' => $mentor->id ?? 1,
            'scheduled_at' => now()->addDays(5)->setTime(15, 0),
            'duration_minutes' => 60,
            'status' => 'confirmed',
        ]);
        $session->mentor = $mentor;

        $calendarUrl = route('jeune.sessions.calendar');

        Mail::to($recipient)->send(new SessionConfirmed(
            $session,
            $mentees->first(),
            $mentees,
            $calendarUrl
            ));
    }

    private function testSessionReminder($recipient)
    {
        $this->line('⏰ Envoi Session Reminder...');

        $mentor = User::where('user_type', 'mentor')->first() ?? new User([
            'name' => 'Marie Dupont',
            'email' => 'mentor@brillio.africa',
            'user_type' => 'mentor',
        ]);

        $mentees = User::where('user_type', 'jeune')->take(1)->get();
        if ($mentees->isEmpty()) {
            $mentees = collect([
                new User(['name' => 'Jean Martin', 'email' => 'jeune@brillio.africa']),
            ]);
        }

        $session = new MentoringSession([
            'mentor_id' => $mentor->id ?? 1,
            'scheduled_at' => now()->addDay()->setTime(14, 0),
            'duration_minutes' => 60,
            'status' => 'confirmed',
            'meeting_link' => route('meeting.show', ['meetingId' => 'Brillio_Test_Meeting_' . time()]),
        ]);
        $session->mentor = $mentor;

        Mail::to($recipient)->send(new SessionReminder(
            $session,
            $mentees->first(),
            $mentees
            ));
    }

    private function testSessionCompleted($recipient)
    {
        $this->line('💭 Envoi Session Completed...');

        $mentor = User::where('user_type', 'mentor')->first() ?? new User([
            'name' => 'Marie Dupont',
            'email' => 'mentor@brillio.africa',
            'user_type' => 'mentor',
        ]);

        $mentees = User::where('user_type', 'jeune')->take(1)->get();
        if ($mentees->isEmpty()) {
            $mentees = collect([
                new User(['name' => 'Jean Martin', 'email' => 'jeune@brillio.africa', 'user_type' => 'jeune']),
            ]);
        }

        $session = new MentoringSession([
            'mentor_id' => $mentor->id ?? 1,
            'scheduled_at' => now()->subHours(2),
            'duration_minutes' => 60,
            'status' => 'completed',
        ]);
        $session->mentor = $mentor;

        $sessionUrl = route('jeune.sessions.show', ['session' => $session->id ?? 1]);
        $bookingUrl = route('jeune.sessions.create', ['mentor' => $mentor->id ?? 1]);

        Mail::to($recipient)->send(new SessionCompleted(
            $session,
            $mentees->first(),
            $mentees,
            $sessionUrl,
            $bookingUrl
            ));
    }

    private function testMentorshipRefused($recipient)
    {
        $this->line('❌ Envoi Mentorship Refused...');

        $mentor = User::where('user_type', 'mentor')->first() ?? new User(['name' => 'Marie Dupont']);
        $mentee = User::where('user_type', 'jeune')->first() ?? new User(['name' => 'Jean Kouassi']);
        $mentorship = new Mentorship([
            'mentor_id' => $mentor->id ?? 1,
            'mentee_id' => $mentee->id ?? 2,
        ]);

        Mail::to($recipient)->send(new MentorshipRefused($mentorship, $mentor, $mentee, "Je n'ai malheureusement pas de disponibilité pour un nouveau mentoré en ce moment."));
    }

    private function testSessionRefused($recipient)
    {
        $this->line('❌ Envoi Session Refused...');

        $mentor = User::where('user_type', 'mentor')->first() ?? new User(['name' => 'Marie Dupont']);
        $mentee = User::where('user_type', 'jeune')->first() ?? new User(['name' => 'Jean Kouassi']);
        $session = new MentoringSession([
            'mentor_id' => $mentor->id ?? 1,
            'scheduled_at' => now()->addDays(2),
        ]);

        Mail::to($recipient)->send(new SessionRefused($session, $mentor, $mentee, "Un imprévu professionnel m'empêche d'être présent à ce créneau."));
    }

    private function testSessionCancelled($recipient)
    {
        $this->line('🚫 Envoi Session Cancelled...');

        $mentor = User::where('user_type', 'mentor')->first() ?? new User(['name' => 'Marie Dupont', 'user_type' => 'mentor']);
        $mentee = User::where('user_type', 'jeune')->first() ?? new User(['name' => 'Jean Kouassi', 'user_type' => 'jeune']);
        $session = new MentoringSession([
            'mentor_id' => $mentor->id ?? 1,
            'scheduled_at' => now()->addDays(2),
        ]);

        Mail::to($recipient)->send(new SessionCancelled($session, $mentee, $mentor, collect([$mentee])));
    }

    private function testProfileReminder($recipient)
    {
        $this->line('🚀 Envoi Profile Reminder...');

        $user = User::where('user_type', 'jeune')->first() ?? new User(['name' => 'Jean Kouassi', 'user_type' => 'jeune']);
        $missing = ["Passer le test MBTI", "Ajouter une biographie", "Ajouter une photo de profil"];

        Mail::to($recipient)->send(new ProfileCompletionReminder($user, $missing));
    }

    private function testNewMentorsDigest($recipient)
    {
        $this->line('👥 Envoi New Mentors Digest...');

        $jeune = User::where('user_type', 'jeune')->first() ?? new User(['name' => 'Jean Kouassi']);

        $mentors = User::where('user_type', 'mentor')->with('mentorProfile.specializationModel')->take(3)->get();
        if ($mentors->isEmpty()) {
            $mentors = collect([
                new User(['name' => 'Adeline Koné', 'user_type' => 'mentor']),
                new User(['name' => 'Moussa Traoré', 'user_type' => 'mentor']),
            ]);
            foreach ($mentors as $m) {
                $m->setRelation('mentorProfile', (object)[
                    'current_position' => 'Expert',
                    'current_company' => 'Brillio Corp',
                    'years_of_experience' => 10,
                    'specialization' => 'tech',
                    'specializationModel' => (object)['name' => 'Technologie & IT'],
                    'public_slug' => 'dr-ousmane-sow-f2abccfe',
                    'getRouteKeyName' => 'public_slug' // Mocking property for consistency if needed, though route helper usually checks method
                ]);
            }
        }

        Mail::to($recipient)->send(new NewMentorsWeekly($jeune, $mentors));
    }
}