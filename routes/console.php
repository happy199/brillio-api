<?php

use App\Jobs\SendInactivityReminders;
use App\Jobs\SendMentorReportReminders;
use App\Jobs\SendMissingPhoneReminders;
use App\Jobs\SendNewMentorsDigest;
use App\Jobs\SendProfileCompletionReminders;
use App\Models\CronAutomation;
use Illuminate\Support\Facades\Schedule;

// Session reminders: run every hour to send 24h and 1h reminders
Schedule::command('sessions:send-reminders')
    ->hourly()
    ->when(fn () => CronAutomation::isEnabled('session_reminders'))
    ->timezone('Africa/Abidjan');

// Profil completion reminders: run every Monday at 9 AM
Schedule::job(new SendProfileCompletionReminders)
    ->mondays()
    ->at('09:00')
    ->when(fn () => CronAutomation::isEnabled('profile_completion_reminders'))
    ->timezone('Africa/Abidjan');

// Missing phone reminders: run every Sunday at 9 AM
Schedule::job(new SendMissingPhoneReminders)
    ->sundays()
    ->at('09:00')
    ->when(fn () => CronAutomation::isEnabled('missing_phone_reminders'))
    ->timezone('Africa/Abidjan');

// Weekly new mentors digest: run every Friday at 4 PM
Schedule::job(new SendNewMentorsDigest)
    ->fridays()
    ->at('16:00')
    ->when(fn () => CronAutomation::isEnabled('new_mentors_digest'))
    ->timezone('Africa/Abidjan');

// Mentor report reminders (escrow release): daily at 10 AM
Schedule::job(new SendMentorReportReminders)
    ->dailyAt('10:00')
    ->when(fn () => CronAutomation::isEnabled('mentor_report_reminders'))
    ->timezone('Africa/Abidjan');

// Synchroniser les questions de personnalité tous les trimestres (1er jour de chaque trimestre à 2h du matin)
Schedule::command('personality:sync-questions')
    ->quarterly()
    ->at('02:00')
    ->when(fn () => CronAutomation::isEnabled('personality_sync'))
    ->timezone('Africa/Dakar');

// Downgrade expired organization subscriptions daily at 1 AM
Schedule::command('organizations:downgrade-expired')
    ->dailyAt('01:00')
    ->when(fn () => CronAutomation::isEnabled('organizations_downgrade'))
    ->timezone('Africa/Abidjan');

// Enterprise monthly credits: distribute 50 free credits on the 1st of each month
Schedule::command('organizations:grant-enterprise-credits')
    ->monthlyOn(1, '00:01')
    ->when(fn () => CronAutomation::isEnabled('enterprise_credits'))
    ->timezone('Africa/Abidjan');

// Rappel d'attraction : Relance hebdomadaire pour les inactifs (7 jours+)
// Exécution horaire avec limite de 500 par lot (voir SendInactivityReminders)
Schedule::job(new SendInactivityReminders)
    ->hourly()
    ->when(fn () => CronAutomation::isEnabled('inactivity_reminders'))
    ->timezone('Africa/Abidjan');

// Archive unverified users older than 7 days daily at 2 AM
Schedule::command('app:archive-unverified')
    ->dailyAt('02:00')
    ->when(fn () => CronAutomation::isEnabled('archive_unverified'))
    ->timezone('Africa/Abidjan');

Schedule::command('messages:send-unread-reminders')
    ->everyFourHours()
    ->when(fn () => CronAutomation::isEnabled('unread_messages_reminders'))
    ->timezone('Africa/Abidjan');

// Process recurring email campaigns every hour
Schedule::command('campaigns:process-recurring')
    ->hourly()
    ->when(fn () => CronAutomation::isEnabled('recurring_campaigns'))
    ->timezone('Africa/Abidjan');

// Surveillance & exclusion des rebonds SMTP pour protéger le serveur Namecheap
Schedule::command('emails:process-bounces --scan-domains')
    ->hourly()
    ->when(fn () => CronAutomation::isEnabled('process_bounces'))
    ->timezone('Africa/Abidjan');
