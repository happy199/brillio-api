<?php

use Illuminate\Support\Facades\Schedule;

// Synchroniser les questions de personnalité tous les trimestres (1er jour de chaque trimestre à 2h du matin)
Schedule::command('personality:sync-questions')
    ->quarterly()
    ->at('02:00')
    ->timezone('Africa/Dakar');
