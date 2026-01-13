<?php

namespace App\Http\Controllers\Jeune;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    public function showChangePasswordForm()
    {
        $user = auth()->user();
        $hasPassword = !empty($user->password);
        $isOAuthUser = !empty($user->auth_provider);

        return view('jeune.change-password', compact('hasPassword', 'isOAuthUser'));
    }

    public function updatePassword(Request $request)
    {
        $user = auth()->user();
        $hasPassword = !empty($user->password);

        $rules = [
            'new_password' => ['required', 'confirmed', Password::min(8)],
        ];

        // Si l'utilisateur a déjà un mot de passe, demander l'ancien
        if ($hasPassword) {
            $rules['current_password'] = ['required', 'current_password'];
        }

        $validated = $request->validate($rules);

        $user->update([
            'password' => Hash::make($validated['new_password']),
        ]);

        return back()->with('success', 'Mot de passe modifié avec succès !');
    }
}
