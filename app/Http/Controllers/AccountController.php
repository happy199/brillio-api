<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    /**
     * Generate a random confirmation code for account archiving
     */
    public function generateConfirmationCode()
    {
        // Generate format: WORD-NNNN (ex: DELTA-7392)
        $words = ['ALPHA', 'BETA', 'GAMMA', 'DELTA', 'SIGMA', 'OMEGA', 'THETA', 'KAPPA'];
        $word = $words[array_rand($words)];
        $number = rand(1000, 9999);

        $code = "{$word}-{$number}";

        // Store in session for validation
        session(['archive_confirmation_code' => $code]);

        return response()->json(['code' => $code]);
    }

    /**
     * Archive user account after code validation
     */
    public function archiveAccount(Request $request)
    {
        $request->validate([
            'confirmation_code' => 'required|string',
            'reason' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        $sessionCode = session('archive_confirmation_code');

        // Validate confirmation code
        if ($request->confirmation_code !== $sessionCode) {
            return back()->withErrors(['confirmation_code' => 'Le code de confirmation est incorrect.']);
        }

        // Archive the account
        $user->is_archived = true;
        $user->archived_at = now();
        $user->archived_reason = $request->reason;
        $user->save();

        // Clear session code
        session()->forget('archive_confirmation_code');

        // Log the user out
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // TODO: Send email notification
        // Mail::to($user->email)->send(new AccountArchivedMail($user));

        return redirect()->route('home')->with('success', 'Votre compte a été archivé. Vous pouvez le réactiver à tout moment en vous reconnectant.');
    }

    /**
     * Reactivate an archived account (called during login)
     */
    public static function reactivateAccount($user)
    {
        if ($user->is_archived) {
            $user->is_archived = false;
            $user->archived_at = null;
            $user->archived_reason = null;
            $user->save();

            return true;
        }

        return false;
    }
}
