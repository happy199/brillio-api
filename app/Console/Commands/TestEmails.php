<?php

namespace App\Console\Commands;

use App\Mail\Mentorship\MentorshipAccepted;
use App\Mail\Mentorship\MentorshipRequested;
use App\Models\Mentorship;
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
    protected $description = 'Test email templates (mentorship-request, mentorship-accepted)';

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
            ['mentorship-request', 'mentorship-accepted', 'all'],
                2
            );
        }

        $this->info("📧 Test des emails vers : {$recipient}");

        switch ($emailType) {
            case 'mentorship-request':
                $this->testMentorshipRequest($recipient);
                break;
            case 'mentorship-accepted':
                $this->testMentorshipAccepted($recipient);
                break;
            case 'all':
                $this->testMentorshipRequest($recipient);
                $this->testMentorshipAccepted($recipient);
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

        $acceptUrl = route('mentor.mentorship.accept', ['mentorship' => 1]);
        $refuseUrl = route('mentor.mentorship.refuse', ['mentorship' => 1]);

        Mail::to($recipient)->send(new MentorshipRequested($mentorship, $mentor, $mentee, $acceptUrl, $refuseUrl));
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
}