<?php

namespace App\Http\Controllers\Jeune;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Shared\HasMessages;

class MessagesController extends Controller
{
    use HasMessages;

    /**
     * Configuration pour le Trait HasMessages
     */
    protected function getMessageConfig(): array
    {
        return [
            'relation_profile' => 'mentor.mentorProfile',
            'user_column' => 'mentee_id',
            'view_prefix' => 'jeune.messages.',
            'is_api' => false,
        ];
    }
}
