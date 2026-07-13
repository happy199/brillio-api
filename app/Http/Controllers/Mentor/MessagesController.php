<?php

namespace App\Http\Controllers\Mentor;

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
            'relation_profile' => 'mentee.jeuneProfile',
            'user_column' => 'mentor_id',
            'view_prefix' => 'mentor.messages.',
            'is_api' => false,
        ];
    }
}
