<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class ContactController extends Controller
{
    public function submit(Request $request)
    {
        // Rate limiting
        $key = 'contact-submit:' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 3)) {
            return back()->with('error', 'Trop de messages envoyés. Réessaye dans 1 heure.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        ContactMessage::create([
            'name' => $request->name,
            'email' => $request->email,
            'subject' => $request->subject,
            'message' => strip_tags($request->message), // Sanitize
            'status' => 'new',
            'ip_address' => $request->ip(),
        ]);

        RateLimiter::hit($key, 3600); // 1 heure

        return back()->with('success', 'Merci ! Ton message a été envoyé. Nous te répondrons bientôt.');
    }
}
