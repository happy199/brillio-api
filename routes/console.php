<?php

use Illuminate\Support\Facades\Schedule;

// Session reminders: run every day at 8 AM to send 24h reminders
Schedule::command('sessions:send-reminders')
    ->dailyAt('08:00')
    ->timezone('Africa/Abidjan');

// Synchroniser les questions de personnalité tous les trimestres (1er jour de chaque trimestre à 2h du matin)
Schedule::command('personality:sync-questions')
    ->quarterly()
    ->at('02:00')
    ->timezone('Africa/Dakar');