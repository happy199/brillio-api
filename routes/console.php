<?php

use Illuminate\Support\Facades\Schedule;

// Session reminders: run every day at 8 AM to send 24h reminders
Schedule::command('sessions:send-reminders')
    ->dailyAt('08:00')
    ->timezone('Africa/Abidjan');

// Profil completion reminders: run every Monday at 9 AM
Schedule::job(new \App\Jobs\SendProfileCompletionReminders)
    ->mondays()
    ->at('09:00')
    ->timezone('Africa/Abidjan');

// Weekly new mentors digest: run every Friday at 4 PM
Schedule::job(new \App\Jobs\SendNewMentorsDigest)
    ->fridays()
    ->at('16:00')
    ->timezone('Africa/Abidjan');

// Mentor report reminders (escrow release): daily at 10 AM
Schedule::job(new \App\Jobs\SendMentorReportReminders)
    ->dailyAt('10:00')
    ->timezone('Africa/Abidjan');

// Synchroniser les questions de personnalité tous les trimestres (1er jour de chaque trimestre à 2h du matin)
Schedule::command('personality:sync-questions')
    ->quarterly()
    ->at('02:00')
    ->timezone('Africa/Dakar');

// Downgrade expired organization subscriptions daily at 1 AM
Schedule::command('organizations:downgrade-expired')
    ->dailyAt('01:00')
    ->timezone('Africa/Abidjan');

// Enterprise monthly credits: distribute 50 free credits on the 1st of each month
Schedule::command('organizations:grant-enterprise-credits')
    ->monthlyOn(1, '00:01')
    ->timezone('Africa/Abidjan');
